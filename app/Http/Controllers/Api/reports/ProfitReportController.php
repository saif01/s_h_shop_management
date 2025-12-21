<?php

namespace App\Http\Controllers\Api\reports;

use App\Http\Controllers\Controller;
use App\Models\SalesItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfitReportController extends Controller
{
    /**
     * Profit Report
     */
    public function index(Request $request)
    {
        $groupBy = $request->group_by ?? 'daily';
        
        $query = SalesItem::select(
                'sales_items.*',
                'sales.invoice_date',
                'products.name as product_name',
                'products.purchase_price',
                'categories.name as category_name'
            )
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.status', '!=', 'cancelled');

        // Apply filters
        if ($request->date_from) {
            $query->whereDate('sales.invoice_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('sales.invoice_date', '<=', $request->date_to);
        }
        if ($request->category_id) {
            $query->where('products.category_id', $request->category_id);
        }

        $items = $query->get();

        // Group and calculate profit
        $profitData = [];
        
        if ($groupBy === 'product') {
            $grouped = $items->groupBy('product_id');
            foreach ($grouped as $productId => $group) {
                $revenue = $group->sum('total');
                $cost = $group->sum(function ($item) {
                    return $item->quantity * $item->purchase_price;
                });
                $discount = $group->sum('discount');
                $profit = $revenue - $cost - $discount;
                
                $profitData[] = [
                    'id' => $productId,
                    'name' => $group->first()->product_name,
                    'period' => 'All',
                    'quantity_sold' => $group->sum('quantity'),
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'discount' => $discount,
                    'profit' => $profit,
                    'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
                ];
            }
        } elseif ($groupBy === 'category') {
            $grouped = $items->groupBy('category_name');
            foreach ($grouped as $category => $group) {
                $revenue = $group->sum('total');
                $cost = $group->sum(function ($item) {
                    return $item->quantity * $item->purchase_price;
                });
                $discount = $group->sum('discount');
                $profit = $revenue - $cost - $discount;
                
                $profitData[] = [
                    'id' => $category ?? 'uncategorized',
                    'name' => $category ?? 'Uncategorized',
                    'period' => 'All',
                    'quantity_sold' => $group->sum('quantity'),
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'discount' => $discount,
                    'profit' => $profit,
                    'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
                ];
            }
        } else {
            // Group by date period
            $grouped = $items->groupBy(function ($item) use ($groupBy) {
                $date = Carbon::parse($item->invoice_date);
                if ($groupBy === 'daily') {
                    return $date->format('Y-m-d');
                } elseif ($groupBy === 'weekly') {
                    return $date->format('Y-W');
                } elseif ($groupBy === 'monthly') {
                    return $date->format('Y-m');
                }
                return $date->format('Y-m-d');
            });
            
            foreach ($grouped as $period => $group) {
                $revenue = $group->sum('total');
                $cost = $group->sum(function ($item) {
                    return $item->quantity * $item->purchase_price;
                });
                $discount = $group->sum('discount');
                $profit = $revenue - $cost - $discount;
                
                $profitData[] = [
                    'id' => $period,
                    'period' => $period,
                    'name' => '-',
                    'quantity_sold' => $group->sum('quantity'),
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'discount' => $discount,
                    'profit' => $profit,
                    'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
                ];
            }
        }

        // Convert to collection for sorting and pagination
        $profitCollection = collect($profitData);

        // Sorting
        $sortBy = $request->get('sort_by', 'period');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = [
            'period', 'name', 'quantity_sold', 'revenue', 'cost', 'discount', 'profit', 'profit_margin'
        ];
        
        if (in_array($sortBy, $allowedSortFields) && in_array($sortDirection, ['asc', 'desc'])) {
            $profitCollection = $profitCollection->sortBy($sortBy, SORT_REGULAR, $sortDirection === 'desc');
        }

        // Calculate summary from all grouped data (before pagination)
        $totalRevenue = $profitCollection->sum('revenue');
        $totalCost = $profitCollection->sum('cost');
        $grossProfit = $totalRevenue - $totalCost;
        
        $summary = [
            'total_revenue' => (float) $totalRevenue,
            'total_cost' => (float) $totalCost,
            'gross_profit' => (float) $grossProfit,
            'profit_margin' => (float) ($totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0),
        ];

        // Paginate the profit data
        $perPage = $request->get('per_page', 10);
        $currentPage = $request->get('page', 1);
        
        // Manual pagination for collection
        $total = $profitCollection->count();
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = $profitCollection->slice($offset, $perPage)->values();
        
        // Build pagination response
        $lastPage = (int) ceil($total / $perPage);
        
        $response = [
            'data' => $paginatedData,
            'current_page' => (int) $currentPage,
            'last_page' => $lastPage,
            'per_page' => (int) $perPage,
            'total' => $total,
            'from' => $total > 0 ? $offset + 1 : null,
            'to' => $total > 0 ? min($offset + $perPage, $total) : null,
            'summary' => $summary,
            'profit' => $paginatedData, // Keep 'profit' key for backward compatibility
            'chart' => $profitCollection->take(30)->values(), // Chart data (first 30 records for visualization)
        ];
        
        return response()->json($response);
    }

    /**
     * Export Profit Report to Excel
     */
    public function exportExcel(Request $request)
    {
        // Implementation would use Maatwebsite\Excel package
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }

    /**
     * Export Profit Report to PDF
     */
    public function exportPDF(Request $request)
    {
        // Implementation would use DomPDF package
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }
}

