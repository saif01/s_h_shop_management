<?php

namespace App\Http\Controllers\Api\products;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(Request $request)
    {
        $query = Unit::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $units = $query->orderBy('name')->get();

        return response()->json([
            'units' => $units,
            'data' => $units,
        ]);
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'code' => 'required|string|max:50|unique:units,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $unit = Unit::create($validated);

        return response()->json([
            'message' => 'Unit created successfully',
            'unit' => $unit,
        ], 201);
    }

    /**
     * Display the specified unit.
     */
    public function show(Unit $unit)
    {
        return response()->json([
            'unit' => $unit,
        ]);
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units')->ignore($unit->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('units')->ignore($unit->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $unit->update($validated);

        return response()->json([
            'message' => 'Unit updated successfully',
            'unit' => $unit,
        ]);
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit)
    {
        // Check if unit is being used by products
        if ($unit->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete unit as it is being used by products',
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully',
        ]);
    }
}

