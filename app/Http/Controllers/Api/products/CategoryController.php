<?php

namespace App\Http\Controllers\Api\products;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\MediaPath;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = ['id', 'name', 'slug', 'order', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'order';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $categories = $query->paginate($request->get('per_page', 10));

        $categories->getCollection()->transform(function ($category) {
            return $this->transformCategoryImage($category);
        });
        
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories',
            'order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        if (!empty($validated['image'])) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        if (!isset($validated['order'])) {
            $validated['order'] = 0;
        }

        // Set created_by to current user
        $validated['created_by'] = auth()->id();

        $category = Category::create($validated);
        $category->load(['createdBy']);
        
        return response()->json($this->transformCategoryImage($category), 201);
    }

    public function show(Category $category)
    {
        $category->load(['createdBy', 'updatedBy']);
        return response()->json($this->transformCategoryImage($category));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('categories')->ignore($category->id)],
            'order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate slug if not provided but name changed
        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        if (array_key_exists('image', $validated)) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        // Set updated_by to current user
        $validated['updated_by'] = auth()->id();

        $category->update($validated);
        $category->load(['createdBy', 'updatedBy']);
        
        return response()->json($this->transformCategoryImage($category));
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated products'
            ], 422);
        }

        // Check if category has subcategories
        if ($category->subCategories()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated subcategories'
            ], 422);
        }

        $category->delete();
        
        return response()->json(['message' => 'Category deleted successfully']);
    }

    private function transformCategoryImage(Category $category): Category
    {
        $category->image = MediaPath::url($category->image);
        return $category;
    }
}

