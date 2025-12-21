<?php

namespace App\Http\Controllers\Api\reports;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
        if ($request->filled('warehouse_id')) {
            $query->where('stocks.warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }
        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('stocks.quantity', '<=', 'products.minimum_stock_level');
        }

        // Get all stock (no pagination for PDF)
        $allStock = $query->orderBy('products.name', 'asc')->get();

        // Calculate summary
        $totalStockValue = $allStock->sum(function ($item) {
            $quantity = $item->quantity ?? 0;
            $purchasePrice = $item->purchase_price ?? 0;
            return $quantity * $purchasePrice;
        });

        $lowStockCount = $allStock->filter(function ($item) {
            $quantity = $item->quantity ?? 0;
            $minLevel = $item->minimum_stock_level ?? 0;
            return $quantity > 0 && $minLevel > 0 && $quantity <= $minLevel;
        })->count();

        $outOfStockCount = $allStock->filter(function ($item) {
            $quantity = $item->quantity ?? 0;
            return $quantity == 0;
        })->count();

        $summary = [
            'total_products' => $allStock->count(),
            'total_stock_value' => (float) $totalStockValue,
            'low_stock_count' => (int) $lowStockCount,
            'out_of_stock_count' => (int) $outOfStockCount,
        ];

        // Get warehouse/category names if filters are applied
        $warehouseName = null;
        if ($request->warehouse_id) {
            $warehouse = Warehouse::find($request->warehouse_id);
            $warehouseName = $warehouse ? $warehouse->name : null;
        }

        $categoryName = null;
        if ($request->category_id) {
            $category = Category::find($request->category_id);
            $categoryName = $category ? $category->name : null;
        }

        // Prepare data for PDF
        $data = [
            'stock' => $allStock,
            'summary' => $summary,
            'filters' => [
                'warehouse_id' => $request->warehouse_id,
                'category_id' => $request->category_id,
                'low_stock_only' => $request->low_stock_only,
            ],
            'warehouseName' => $warehouseName,
            'categoryName' => $categoryName,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.stock-report', $data);
        $pdf->setPaper('a4', 'landscape');
        
        // Generate filename
        $filename = 'stock_report_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
}

