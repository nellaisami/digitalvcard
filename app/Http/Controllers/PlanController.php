<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePlanRequest;
use App\Models\Plan;
use App\Models\VcardTemplate;
use App\Services\PlanTaxCalculator;
use App\Support\PlanFeatureCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $plans = Plan::query()
            ->withCount('users')
            ->withCount('vcardTemplates')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('plans.form', [
            'plan' => new Plan(['frequency' => 'monthly', 'trial_days' => 0, 'status' => 'active']),
            'features' => $this->editableFeatures(),
            'limitFeatures' => PlanFeatureCatalog::configurableLimits(),
            'templates' => VcardTemplate::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(SavePlanRequest $request): RedirectResponse
    {
        $data = $this->planData($request->validated());
        $templateIds = $data['template_ids'];
        unset($data['template_ids']);
        $plan = DB::transaction(function () use ($data, $templateIds): Plan {
            Plan::query()->lockForUpdate()->get();

            if ($data['is_default']) {
                Plan::query()->update(['is_default' => false]);
            }

            $plan = Plan::create($data);
            $plan->vcardTemplates()->sync($templateIds);

            return $plan;
        });

        return redirect()->route('plans.show', $plan)->with('status', 'Plan created successfully.');
    }

    public function show(Plan $plan, PlanTaxCalculator $calculator): View
    {
        $plan->loadCount('users')->load('vcardTemplates');

        return view('plans.show', [
            'plan' => $plan,
            'features' => PlanFeatureCatalog::features(),
            'taxCalculation' => $calculator->calculate($plan),
            'currency' => strtoupper(config('app.currency', 'USD')),
        ]);
    }

    public function edit(Plan $plan): View
    {
        $plan->load('vcardTemplates');
        $selectedTemplateIds = $plan->vcardTemplates->modelKeys();

        return view('plans.form', [
            'plan' => $plan,
            'features' => $this->editableFeatures(),
            'limitFeatures' => PlanFeatureCatalog::configurableLimits(),
            'templates' => VcardTemplate::query()
                ->where('status', 'active')
                ->orWhereIn('id', $selectedTemplateIds)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(SavePlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $this->planData($request->validated());
        $templateIds = $data['template_ids'];
        unset($data['template_ids']);
        DB::transaction(function () use ($plan, $data, $templateIds): void {
            Plan::query()->lockForUpdate()->get();

            if ($data['is_default']) {
                Plan::query()->where('id', '<>', $plan->id)->update(['is_default' => false]);
            }

            $plan->update($data);
            $plan->vcardTemplates()->sync($templateIds);
        });

        return redirect()->route('plans.show', $plan)->with('status', 'Plan updated successfully.');
    }

    public function setDefault(Plan $plan): RedirectResponse
    {
        DB::transaction(function () use ($plan): void {
            Plan::query()->lockForUpdate()->get();
            $lockedPlan = Plan::query()->findOrFail($plan->id);
            abort_unless($lockedPlan->status === 'active', 422, 'Only active plans can be the default plan.');
            Plan::query()->update(['is_default' => false]);
            $lockedPlan->update(['is_default' => true]);
        });

        return redirect()->route('plans.index')->with('status', 'Default plan updated.');
    }

    public function toggleStatus(Plan $plan): JsonResponse
    {
        $result = DB::transaction(function () use ($plan): array {
            $lockedPlan = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            if ($lockedPlan->is_default && $lockedPlan->status === 'active') {
                return ['error' => 'Choose another default plan before deactivating this one.'];
            }

            $status = $lockedPlan->status === 'active' ? 'inactive' : 'active';
            $lockedPlan->update(['status' => $status]);

            return ['status' => $status];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json([
            'status' => $result['status'],
            'label' => ucfirst($result['status']),
            'message' => "Plan {$result['status']}.",
        ]);
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $assigned = DB::transaction(function () use ($plan): bool {
            $lockedPlan = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            if ($lockedPlan->users()->exists()) {
                return true;
            }

            $lockedPlan->delete();

            return false;
        });

        if ($assigned) {
            return back()->with('error', 'This plan is assigned to users. Reassign them before deleting it.');
        }

        return redirect()->route('plans.index')->with('status', 'Plan deleted successfully.');
    }

    private function planData(array $data): array
    {
        $features = array_keys(array_filter(
            $data['features'] ?? [],
            fn ($enabled): bool => in_array($enabled, [true, 1, '1', 'true'], true),
        ));
        $templateIds = array_values(array_unique(array_map('intval', $data['templates'] ?? [])));
        if ($templateIds !== []) {
            $features[] = 'no_of_vcards';
        } else {
            $features = array_values(array_diff($features, ['no_of_vcards']));
        }
        $limits = [];

        foreach (array_keys(PlanFeatureCatalog::configurableLimits()) as $key) {
            if (in_array($key, $features, true)) {
                $limits[$key] = ! empty($data['unlimited_limits'][$key])
                    ? null
                    : (int) $data['feature_limits'][$key];
            }
        }

        $data['features'] = $features;
        $data['feature_limits'] = $limits;
        $data['template_ids'] = $templateIds;
        $data['is_default'] = (bool) $data['is_default'];
        unset($data['templates'], $data['no_of_vcards'], $data['vcard_unlimited'], $data['unlimited_limits']);

        return $data;
    }

    private function editableFeatures(): array
    {
        $features = PlanFeatureCatalog::features();
        unset($features['no_of_vcards']);

        return $features;
    }
}
