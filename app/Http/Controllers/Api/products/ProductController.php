<?php

namespace App\Http\Controllers\Api\products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Support\MediaPath;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])
            ->withStockQuantity(); // Efficiently load stock quantity with single query

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name, SKU, barcode, brand, manufacturer or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = [
            'id', 'name', 'sku', 'barcode', 'brand',
            'purchase_price', 'sale_price', 'tax_rate',
            'minimum_stock_level', 'is_active', 'created_at', 'updated_at'
        ];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->paginate($request->get('per_page', 10));

        $products->getCollection()->transform(function ($product) {
            return $this->transformProductImage($product);
        });
        
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basic Info
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products',
            'barcode' => 'nullable|string|max:255|unique:products',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'minimum_stock_level' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Normalize image path
        if (!empty($validated['image'])) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        if (!isset($validated['minimum_stock_level'])) {
            $validated['minimum_stock_level'] = 0;
        }

        if (!isset($validated['tax_rate'])) {
            $validated['tax_rate'] = 0;
        }

        $product = Product::create($validated);
        $product->load(['category', 'unit']);
        
        return response()->json($this->transformProductImage($product), 201);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit']);
        return response()->json($this->transformProductImage($product));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            // Basic Info
            'name' => 'sometimes|required|string|max:255',
            'sku' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('products')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('products')->ignore($product->id)],
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'purchase_price' => 'sometimes|required|numeric|min:0',
            'sale_price' => 'sometimes|required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'minimum_stock_level' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Normalize image path
        if (array_key_exists('image', $validated)) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        $product->update($validated);
        $product->load(['category', 'unit']);
        
        return response()->json($this->transformProductImage($product));
    }

    public function destroy(Product $product)
    {
        // Check if product has stock or transactions
        $hasStock = $product->stocks()->where('quantity', '>', 0)->exists();
        $hasTransactions = $product->stockLedgers()->exists();

        if ($hasStock || $hasTransactions) {
            return response()->json([
                'message' => 'Cannot delete product with stock or transaction history'
            ], 422);
        }

        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name,
                ];
            });

        return response()->json([
            'categories' => $categories
        ]);
    }

    public function units()
    {
        $units = Unit::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($unit) {
                return [
                    'value' => $unit->id,
                    'label' => $unit->name . ' (' . $unit->code . ')',
                ];
            });

        return response()->json([
            'units' => $units
        ]);
    }

    private function transformProductImage(Product $product): Product
    {
        $product->image = MediaPath::url($product->image);
        return $product;
    }
}

