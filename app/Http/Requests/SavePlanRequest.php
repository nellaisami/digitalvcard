<?php

namespace App\Http\Requests;

use App\Models\Plan;
use App\Support\PlanFeatureCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SavePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    public function rules(): array
    {
        /** @var Plan|null $plan */
        $plan = $this->route('plan');
        $existingTemplateIds = $plan instanceof Plan ? $plan->vcardTemplates()->pluck('vcard_templates.id')->all() : [];
        $featureKeys = implode(',', array_keys(PlanFeatureCatalog::features()));
        $limitKeys = implode(',', array_keys(PlanFeatureCatalog::configurableLimits()));

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('plans', 'name')->ignore($plan)],
            'price' => ['required', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'frequency' => ['required', Rule::in(['monthly', 'yearly', 'unlimited'])],
            'templates' => ['nullable', 'array'],
            'templates.*' => [
                'integer',
                'distinct',
                Rule::exists('vcard_templates', 'id')->where(function ($query) use ($existingTemplateIds): void {
                    $query->where(function ($query) use ($existingTemplateIds): void {
                        $query->where('status', 'active');
                        if ($existingTemplateIds !== []) {
                            $query->orWhereIn('id', $existingTemplateIds);
                        }
                    });
                }),
            ],
            'trial_days' => ['required', 'integer', 'min:0', 'max:3650'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_default' => ['required', 'boolean'],
            'features' => ['nullable', 'array:'.$featureKeys],
            'features.*' => ['boolean'],
            'feature_limits' => ['nullable', 'array:'.$limitKeys],
            'feature_limits.no_of_visitors' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'feature_limits.no_of_organization_users' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'unlimited_limits' => ['nullable', 'array:'.$limitKeys],
            'unlimited_limits.*' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('is_default') && $this->input('status') !== 'active') {
                $validator->errors()->add('is_default', 'The default plan must be active.');
            }

            $features = $this->input('features', []);
            if (! is_array($features)) {
                return;
            }

            foreach (PlanFeatureCatalog::configurableLimits() as $key => $label) {
                if ($this->isFeatureEnabled($features, $key)
                    && ! $this->boolean('unlimited_limits.'.$key)
                    && ! $this->filled('feature_limits.'.$key)) {
                    $validator->errors()->add('feature_limits.'.$key, 'Set a numeric limit or choose unlimited access.');
                }
            }
        });
    }

    private function isFeatureEnabled(array $features, string $key): bool
    {
        return in_array($features[$key] ?? null, [true, 1, '1', 'true'], true);
    }
}
