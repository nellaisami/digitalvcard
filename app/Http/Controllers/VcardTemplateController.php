<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveVcardTemplateRequest;
use App\Models\VcardTemplate;
use App\Services\VcardTemplatePhotoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VcardTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $templates = VcardTemplate::query()
            ->withCount('plans')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('vcard-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('vcard-templates.form', ['template' => new VcardTemplate(['status' => 'active'])]);
    }

    public function store(SaveVcardTemplateRequest $request, VcardTemplatePhotoManager $photos): RedirectResponse
    {
        $data = $request->validated();
        $data['image_path'] = $photos->store($request->file('image'));
        $data['image_url'] = 'storage/'.$data['image_path'];
        unset($data['image']);
        VcardTemplate::create($data);

        return redirect()->route('vcard-templates.index')->with('status', 'vCard template created.');
    }

    public function edit(VcardTemplate $vcard_template): View
    {
        return view('vcard-templates.form', ['template' => $vcard_template]);
    }

    public function update(
        SaveVcardTemplateRequest $request,
        VcardTemplate $vcard_template,
        VcardTemplatePhotoManager $photos,
    ): RedirectResponse {
        $data = $request->validated();
        unset($data['image']);
        $oldPath = $vcard_template->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $photos->store($request->file('image'));
            $data['image_url'] = 'storage/'.$data['image_path'];
        }

        DB::transaction(fn () => $vcard_template->update($data));
        if (isset($data['image_path']) && $oldPath !== $data['image_path']) {
            $photos->delete($oldPath);
        }

        return redirect()->route('vcard-templates.index')->with('status', 'vCard template updated.');
    }

    public function toggleStatus(VcardTemplate $vcard_template): JsonResponse
    {
        $status = $vcard_template->status === 'active' ? 'inactive' : 'active';
        $vcard_template->update(['status' => $status]);

        return response()->json([
            'status' => $status,
            'label' => ucfirst($status),
            'message' => "vCard template {$status}.",
        ]);
    }

    public function destroy(
        VcardTemplate $vcard_template,
        VcardTemplatePhotoManager $photos,
    ): RedirectResponse {
        $imagePath = $vcard_template->image_path;
        $assigned = DB::transaction(function () use ($vcard_template): bool {
            $template = VcardTemplate::query()->lockForUpdate()->findOrFail($vcard_template->id);
            if ($template->plans()->exists()) {
                return true;
            }

            $template->delete();

            return false;
        });

        if ($assigned) {
            return back()->with('error', 'Remove this template from assigned plans before deleting it.');
        }

        $photos->delete($imagePath);

        return redirect()->route('vcard-templates.index')->with('status', 'vCard template deleted.');
    }
}
