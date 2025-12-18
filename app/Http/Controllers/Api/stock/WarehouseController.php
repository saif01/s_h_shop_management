<?php

namespace App\Http\Controllers\Api\stock;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request)
    {
        $query = Warehouse::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $warehouses = $query->orderBy('name')->get();

        return response()->json([
            'warehouses' => $warehouses,
            'data' => $warehouses,
        ]);
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'message' => 'Warehouse created successfully',
            'warehouse' => $warehouse,
        ], 201);
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse)
    {
        return response()->json([
            'warehouse' => $warehouse,
        ]);
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('warehouses')->ignore($warehouse->id)],
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'message' => 'Warehouse updated successfully',
            'warehouse' => $warehouse,
        ]);
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse)
    {
        // Check if warehouse is being used
        if ($warehouse->stocks()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete warehouse as it has stock records',
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'message' => 'Warehouse deleted successfully',
        ]);
    }
}

