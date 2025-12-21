<?php

namespace App\Http\Controllers\Api\reports;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReportController extends Controller
{
    /**
     * Stock Report
     */
    public function index(Request $request)
    {
        $query = Stock::with(['product.category', 'warehouse'])
            ->select('stocks.*')
            ->selectRaw('products.name as product_name')
            ->selectRaw('products.sku as sku')
            ->selectRaw('products.purchase_price as purchase_price')
            ->selectRaw('products.minimum_stock_level as minimum_stock_level')
            ->selectRaw('categories.name as category_name')
            ->selectRaw('warehouses.name as warehouse_name');
        
        $query->leftJoin('products', 'stocks.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id');

        // Apply filters
        if ($request->warehouse_id) {
            $query->where('stocks.warehouse_id', $request->warehouse_id);
        }
        if ($request->category_id) {
            $query->where('products.category_id', $request->category_id);
        }
        if ($request->low_stock_only) {
            $query->whereColumn('stocks.quantity', '<=', 'products.minimum_stock_level');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'product_name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        // Map frontend sort fields to database fields
        $sortFieldMap = [
            'product_name' => 'products.name',
            'sku' => 'products.sku',
            'category_name' => 'categories.name',
            'warehouse_name' => 'warehouses.name',
            'quantity' => 'stocks.quantity',
            'minimum_stock_level' => 'products.minimum_stock_level',
            'stock_value' => DB::raw('(stocks.quantity * products.purchase_price)'),
            'status' => DB::raw('CASE 
                WHEN stocks.quantity = 0 THEN 1
                WHEN stocks.quantity <= products.minimum_stock_level THEN 2
                ELSE 3
            END'),
        ];
        
        $dbSortField = $sortFieldMap[$sortBy] ?? 'products.name';
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($dbSortField, $sortDirection);

        // Get all stock for summary calculation (before pagination)
        $allStock = clone $query;
        $allStockForSummary = $allStock->get();

        // Calculate summary from all filtered stock
        $totalStockValue = $allStockForSummary->sum(function ($item) {
            return $item->quantity * $item->purchase_price;
        });

        $lowStockCount = $allStockForSummary->filter(function ($item) {
            return $item->quantity > 0 && $item->quantity <= $item->minimum_stock_level;
        })->count();

        $outOfStockCount = $allStockForSummary->filter(function ($item) {
            return $item->quantity == 0;
        })->count();

        $summary = [
            'total_products' => $allStockForSummary->count(),
            'total_stock_value' => (float) $totalStockValue,
            'low_stock_count' => (int) $lowStockCount,
            'out_of_stock_count' => (int) $outOfStockCount,
        ];

        // Paginate the stock
        $perPage = $request->get('per_page', 10);
        $stock = $query->paginate($perPage);

        // Return paginated response with additional data
        $response = $stock->toArray();
        $response['summary'] = $summary;
        $response['stock'] = $response['data']; // Keep 'stock' key for backward compatibility
        
        return response()->json($response);
    }

    /**
     * Export Stock Report to Excel
     */
    public function exportExcel(Request $request)
    {
        // Implementation would use Maatwebsite\Excel package
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }

    /**
     * Export Stock Report to PDF
     */
    public function exportPDF(Request $request)
    {
        // Implementation would use DomPDF package
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }
}

