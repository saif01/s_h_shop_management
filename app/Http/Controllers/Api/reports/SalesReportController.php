<?php

namespace App\Http\Controllers\Api\reports;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    /**
     * Sales Report
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'items.product'])
            ->select('sales.*')
            ->selectRaw('customers.name as customer_name');
        
        $query->leftJoin('customers', 'sales.customer_id', '=', 'customers.id');

        // Apply filters
        if ($request->date_from) {
            $query->whereDate('sales.invoice_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('sales.invoice_date', '<=', $request->date_to);
        }
        if ($request->customer_id) {
            $query->where('sales.customer_id', $request->customer_id);
        }
        if ($request->status) {
            $query->where('sales.status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'invoice_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Map frontend sort fields to database fields
        $sortFieldMap = [
            'invoice_number' => 'sales.invoice_number',
            'invoice_date' => 'sales.invoice_date',
            'customer_name' => 'customers.name',
            'total_amount' => 'sales.total_amount',
            'paid_amount' => 'sales.paid_amount',
            'balance_amount' => 'sales.balance_amount',
            'status' => 'sales.status',
        ];
        
        $dbSortField = $sortFieldMap[$sortBy] ?? 'sales.invoice_date';
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($dbSortField, $sortDirection);

        // Get all sales for summary calculation (before pagination)
        $allSales = clone $query;
        $allSalesForSummary = $allSales->get();

        // For financial calculations: exclude cancelled sales unless explicitly filtered by cancelled status
        $salesForFinancials = $allSalesForSummary;
        if (!$request->status || $request->status !== 'cancelled') {
            $salesForFinancials = $allSalesForSummary->where('status', '!=', 'cancelled');
        }
        
        // Calculate summary from all filtered sales
        $summary = [
            'total_sales' => (float) ($salesForFinancials->sum('total_amount') ?? 0),
            'total_paid' => (float) ($salesForFinancials->sum('paid_amount') ?? 0),
            'total_due' => (float) ($salesForFinancials->sum('balance_amount') ?? 0),
            'total_count' => (int) $allSalesForSummary->count(),
        ];

        // Paginate the sales
        $perPage = $request->get('per_page', 10);
        $sales = $query->paginate($perPage);

        // Top selling products
        $topProducts = SalesItem::select(
                'product_id',
                'products.name as product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id');
        
        // Apply same filters as main query
        if ($request->date_from) {
            $topProducts->whereDate('sales.invoice_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $topProducts->whereDate('sales.invoice_date', '<=', $request->date_to);
        }
        if ($request->customer_id) {
            $topProducts->where('sales.customer_id', $request->customer_id);
        }
        if ($request->status) {
            $topProducts->where('sales.status', $request->status);
        } else {
            $topProducts->where('sales.status', '!=', 'cancelled');
        }
        
        $topProducts = $topProducts->groupBy('product_id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Return paginated response with additional data
        $response = $sales->toArray();
        $response['summary'] = $summary;
        $response['top_products'] = $topProducts;
        $response['sales'] = $response['data']; // Keep 'sales' key for backward compatibility
        
        return response()->json($response);
    }

    /**
     * Export Sales Report to Excel
     */
    public function exportExcel(Request $request)
    {
        // Implementation would use Maatwebsite\Excel package
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }

    /**
     * Export Sales Report to PDF
     */
    public function exportPDF(Request $request)
    {
        // Implementation would use DomPDF package
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }
}

