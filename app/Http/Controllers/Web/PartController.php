<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Supplier;
use App\Services\PartService;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $parts = Part::query()
            ->with('partCategory', 'supplier')
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%")
                ->orWhere('brand', 'like', "%{$s}%"))
            ->when($request->category, fn ($q, $c) => $q->where('part_category_id', $c))
            ->when($request->filter === 'low', fn ($q) => $q->lowStock())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = PartCategory::orderBy('name')->get();
        $lowStockCount = Part::lowStock()->count();

        return view('parts.index', compact('parts', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $categories = PartCategory::where('status', true)->pluck('name', 'id');
        $suppliers = Supplier::where('status', true)->pluck('name', 'id');

        return view('parts.create', compact('categories', 'suppliers'));
    }

    public function store(StorePartRequest $request)
    {
        Part::create($request->validated());

        return redirect()
            ->route('parts.index')
            ->with('success', 'Part created successfully.');
    }

    public function show(Part $part)
    {
        $part->load('partCategory', 'supplier', 'movements.user', 'usage.part');

        return view('parts.show', compact('part'));
    }

    public function edit(Part $part)
    {
        $categories = PartCategory::where('status', true)->pluck('name', 'id');
        $suppliers = Supplier::where('status', true)->pluck('name', 'id');

        return view('parts.edit', compact('part', 'categories', 'suppliers'));
    }

    public function update(UpdatePartRequest $request, Part $part)
    {
        $part->update($request->validated());

        return redirect()
            ->route('parts.show', $part)
            ->with('success', 'Part updated successfully.');
    }

    public function restock(Request $request, Part $part, PartService $service)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $service->restock($part, $request->quantity, 'manual-restock', $request->notes, auth()->id());

        return redirect()
            ->route('parts.show', $part)
            ->with('success', "Stock increased by {$request->quantity}.");
    }

    public function adjust(Request $request, Part $part, PartService $service)
    {
        $request->validate([
            'delta' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string'],
        ]);

        $part = $service->adjust($part, (int) $request->delta, $request->reason, auth()->id());

        return redirect()
            ->route('parts.show', $part)
            ->with('success', 'Stock adjusted successfully.');
    }

    public function destroy(Part $part)
    {
        if ($part->usage()->exists()) {
            return redirect()
                ->route('parts.index')
                ->with('error', 'Cannot delete a part that has been used.');
        }

        $part->delete();

        return redirect()
            ->route('parts.index')
            ->with('success', 'Part deleted successfully.');
    }
}
