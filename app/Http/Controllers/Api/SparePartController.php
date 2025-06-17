<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SparePartResource;
use App\Models\SparePart;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function index(Request $request)
    {
        $parts = SparePart::when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                return $query->where('category', $category);
            })
            ->when($request->low_stock, function ($query) {
                return $query->lowStock();
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return SparePartResource::collection($parts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:spare_parts',
            'name' => 'required',
            'description' => 'nullable',
            'unit' => 'required',
            'unit_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'category' => 'required|in:filtration,lubrification,pneumatique,batterie,autre',
        ]);

        $sparePart = SparePart::create($validated);

        return new SparePartResource($sparePart);
    }

    public function update(Request $request, SparePart $sparePart)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required',
            'description' => 'nullable',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'minimum_stock' => 'sometimes|required|integer|min:0',
            'category' => 'sometimes|required|in:filtration,lubrification,pneumatique,batterie,autre',
        ]);

        $sparePart->update($validated);

        return new SparePartResource($sparePart);
    }

    public function updateStock(Request $request, SparePart $sparePart)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'operation' => 'required|in:add,remove',
        ]);

        if ($validated['operation'] === 'add') {
            $sparePart->incrementStock($validated['quantity']);
        } else {
            if ($sparePart->quantity_in_stock < $validated['quantity']) {
                return response()->json(['message' => 'Stock insuffisant'], 422);
            }
            $sparePart->decrementStock($validated['quantity']);
        }

        return new SparePartResource($sparePart->fresh());
    }

    public function lowStockAlert()
    {
        $lowStockParts = SparePart::lowStock()->get();

        return SparePartResource::collection($lowStockParts);
    }
}
