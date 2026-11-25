<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTaxRequest;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(Request $request): View
    {
        $taxes = Tax::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where('tax_name', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('tax_name')
            ->paginate(10)
            ->withQueryString();

        return view('taxes.index', compact('taxes'));
    }

    public function create(): View
    {
        return view('taxes.form', ['tax' => new Tax(['status' => 'active'])]);
    }

    public function store(SaveTaxRequest $request): RedirectResponse
    {
        Tax::create($request->validated());

        return redirect()->route('taxes.index')->with('status', 'Tax added successfully.');
    }

    public function edit(Tax $tax): View
    {
        return view('taxes.form', compact('tax'));
    }

    public function update(SaveTaxRequest $request, Tax $tax): RedirectResponse
    {
        $tax->update($request->validated());

        return redirect()->route('taxes.index')->with('status', 'Tax updated successfully.');
    }

    public function toggleStatus(Tax $tax): JsonResponse
    {
        $status = $tax->status === 'active' ? 'inactive' : 'active';
        $tax->update(['status' => $status]);

        return response()->json([
            'status' => $status,
            'label' => ucfirst($status),
            'message' => "{$tax->tax_name} is now {$status}.",
        ]);
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        $tax->delete();

        return redirect()->route('taxes.index')->with('status', 'Tax deleted successfully.');
    }
}
