<?php

namespace App\Http\Controllers\Api\reports;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Sales Report
     */
    public function salesReport(Request $request)
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

        $sales = $query->orderBy('sales.invoice_date', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_sales' => $sales->sum('total_amount'),
            'total_paid' => $sales->sum('paid_amount'),
            'total_due' => $sales->sum('balance_amount'),
            'total_count' => $sales->count(),
        ];

        // Top selling products
        $topProducts = SalesItem::select(
                'product_id',
                'products.name as product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id');
        
        if ($request->date_from) {
            $topProducts->whereDate('sales.invoice_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $topProducts->whereDate('sales.invoice_date', '<=', $request->date_to);
        }
        
        $topProducts = $topProducts->groupBy('product_id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return response()->json([
            'sales' => $sales,
            'summary' => $summary,
            'top_products' => $topProducts,
        ]);
    }

    /**
     * Purchase Report
     */
    public function purchaseReport(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.product'])
            ->select('purchases.*')
            ->selectRaw('suppliers.name as supplier_name');
        
        $query->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id');

        // Apply filters
        if ($request->date_from) {
            $query->whereDate('purchases.invoice_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('purchases.invoice_date', '<=', $request->date_to);
        }
        if ($request->supplier_id) {
            $query->where('purchases.supplier_id', $request->supplier_id);
        }
        if ($request->status) {
            $query->where('purchases.status', $request->status);
        }

        $purchases = $query->orderBy('purchases.invoice_date', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_purchases' => $purchases->sum('total_amount'),
            'total_paid' => $purchases->sum('paid_amount'),
            'total_due' => $purchases->sum('balance_amount'),
            'total_count' => $purchases->count(),
        ];

        return response()->json([
            'purchases' => $purchases,
            'summary' => $summary,
        ]);
    }

    /**
     * Stock Report
     */
    public function stockReport(Request $request)
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

        $stock = $query->get();

        // Calculate summary
        $totalStockValue = $stock->sum(function ($item) {
            return $item->quantity * $item->purchase_price;
        });

        $lowStockCount = $stock->filter(function ($item) {
            return $item->quantity <= $item->minimum_stock_level;
        })->count();

        $outOfStockCount = $stock->filter(function ($item) {
            return $item->quantity == 0;
        })->count();

        $summary = [
            'total_products' => $stock->count(),
            'total_stock_value' => $totalStockValue,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];

        return response()->json([
            'stock' => $stock,
            'summary' => $summary,
        ]);
    }

    /**
     * Due Report
     */
    public function dueReport(Request $request)
    {
        $partyType = $request->party_type ?? 'customer';
        
        if ($partyType === 'customer') {
            $query = Sale::with('customer')
                ->select('sales.*')
                ->selectRaw('customers.name as party_name')
                ->selectRaw('customers.id as party_id')
                ->selectRaw('sales.balance_amount as due_amount')
                ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                ->where('sales.balance_amount', '>', 0);
            
            if ($request->party_id) {
                $query->where('sales.customer_id', $request->party_id);
            }
        } else {
            $query = Purchase::with('supplier')
                ->select('purchases.*')
                ->selectRaw('suppliers.name as party_name')
                ->selectRaw('suppliers.id as party_id')
                ->selectRaw('purchases.balance_amount as due_amount')
                ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->where('purchases.balance_amount', '>', 0);
            
            if ($request->party_id) {
                $query->where('purchases.supplier_id', $request->party_id);
            }
        }

        if ($request->overdue_only) {
            $query->whereNotNull('due_date')
                ->whereDate('due_date', '<', now());
        }

        $due = $query->orderBy('due_date', 'asc')->get();

        // Calculate summary
        $summary = [
            'total_due' => $due->sum('due_amount'),
            'overdue_amount' => $due->filter(function ($item) {
                return $item->due_date && $item->due_date < now();
            })->sum('due_amount'),
            'total_parties' => $due->unique('party_id')->count(),
        ];

        return response()->json([
            'due' => $due,
            'summary' => $summary,
        ]);
    }

    /**
     * Profit Report
     */
    public function profitReport(Request $request)
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
                $date = $item->invoice_date;
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

        // Calculate summary
        $totalRevenue = collect($profitData)->sum('revenue');
        $totalCost = collect($profitData)->sum('cost');
        $grossProfit = $totalRevenue - $totalCost;
        
        $summary = [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'profit_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
        ];

        return response()->json([
            'profit' => $profitData,
            'chart' => $profitData,
            'summary' => $summary,
        ]);
    }

    /**
     * Export Sales Report to Excel
     */
    public function exportSalesExcel(Request $request)
    {
        // Implementation would use Maatwebsite\Excel package
        // For now, return a placeholder response
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }

    /**
     * Export Sales Report to PDF
     */
    public function exportSalesPDF(Request $request)
    {
        // Implementation would use DomPDF package
        // For now, return a placeholder response
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }
}

