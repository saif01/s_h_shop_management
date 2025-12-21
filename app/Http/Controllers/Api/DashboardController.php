<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Owner dashboard summary for the shop management system.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
        $last7Days = Carbon::today()->subDays(7);
        $last30Days = Carbon::today()->subDays(30);

        // Sales metrics
        $todaySales = Sale::whereDate('invoice_date', $today)->sum('total_amount');
        $yesterdaySales = Sale::whereDate('invoice_date', $yesterday)->sum('total_amount');
        $monthSales = Sale::whereDate('invoice_date', '>=', $startOfMonth)->sum('total_amount');
        $lastMonthSales = Sale::whereBetween('invoice_date', [$startOfLastMonth, $endOfLastMonth])->sum('total_amount');
        $customerDue = Sale::sum('balance_amount');

        // Purchase metrics
        $todayPurchases = Purchase::whereDate('invoice_date', $today)->sum('total_amount');
        $monthPurchases = Purchase::whereDate('invoice_date', '>=', $startOfMonth)->sum('total_amount');
        $supplierDue = Purchase::sum('balance_amount');

        // Product & Stock metrics
        $productCount = Product::count();
        $lowStockCount = Stock::whereHas('product', function ($q) {
            $q->where('minimum_stock_level', '>', 0);
        })
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->whereColumn('stocks.quantity', '<', 'products.minimum_stock_level')
            ->count();

        // Customer & Supplier counts
        $customerCount = Customer::where('is_active', true)->count();
        $supplierCount = Supplier::where('is_active', true)->count();

        // Profit calculation (this month)
        $thisMonthProfit = SalesItem::select(
            DB::raw('SUM(sales_items.total) as revenue'),
            DB::raw('SUM(sales_items.quantity * products.purchase_price) as cost'),
            DB::raw('SUM(sales_items.discount) as discount')
        )
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->whereDate('sales.invoice_date', '>=', $startOfMonth)
            ->where('sales.status', '!=', 'cancelled')
            ->first();

        $profit = 0;
        if ($thisMonthProfit) {
            $revenue = $thisMonthProfit->revenue ?? 0;
            $cost = $thisMonthProfit->cost ?? 0;
            $discount = $thisMonthProfit->discount ?? 0;
            $profit = $revenue - $cost - $discount;
        }

        // Sales trend (last 7 days)
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $salesTrend[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
                'sales' => (float) Sale::whereDate('invoice_date', $date)->sum('total_amount'),
            ];
        }

        // Sales trend (last 30 days) - grouped by week
        $salesTrend30 = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::today()->startOfWeek()->subWeeks($i);
            $weekEnd = Carbon::today()->startOfWeek()->subWeeks($i)->endOfWeek();
            $salesTrend30[] = [
                'label' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'week' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'sales' => (float) Sale::whereBetween('invoice_date', [$weekStart, $weekEnd])->sum('total_amount'),
            ];
        }

        // Top products by sales (this month)
        $topProducts = SalesItem::select(
            'products.id',
            'products.name',
            'products.image',
            DB::raw('SUM(sales_items.quantity) as total_quantity'),
            DB::raw('SUM(sales_items.total) as total_sales')
        )
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->whereDate('sales.invoice_date', '>=', $startOfMonth)
            ->where('sales.status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name', 'products.image')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $item->image,
                    'quantity' => $item->total_quantity,
                    'sales' => (float) $item->total_sales,
                ];
            });

        // Top customers by sales (this month)
        $topCustomers = Sale::select(
            'customers.id',
            'customers.name',
            DB::raw('COUNT(sales.id) as total_orders'),
            DB::raw('SUM(sales.total_amount) as total_sales')
        )
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereDate('sales.invoice_date', '>=', $startOfMonth)
            ->where('sales.status', '!=', 'cancelled')
            ->whereNotNull('customers.id')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name ?? 'Unknown',
                    'orders' => $item->total_orders,
                    'sales' => (float) $item->total_sales,
                ];
            });

        // Low stock items
        $lowStockItems = Stock::select(
            'products.id',
            'products.name',
            'products.sku',
            'products.image',
            'stocks.quantity',
            'products.minimum_stock_level'
        )
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->where('products.minimum_stock_level', '>', 0)
            ->whereColumn('stocks.quantity', '<', 'products.minimum_stock_level')
            ->orderBy('stocks.quantity', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'image' => $item->image,
                    'quantity' => $item->quantity,
                    'minimum' => $item->minimum_stock_level,
                    'needed' => max(0, $item->minimum_stock_level - $item->quantity),
                ];
            });

        // Recent sales
        $recentSales = Sale::with('customer')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($sale) {
                // Handle date formatting - invoice_date might be string or Carbon instance
                $date = null;
                if ($sale->invoice_date) {
                    try {
                        $date = Carbon::parse($sale->invoice_date)->toDateString();
                    } catch (\Exception $e) {
                        // If parsing fails, use the original value as string
                        $date = is_string($sale->invoice_date) ? $sale->invoice_date : null;
                    }
                }

                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer' => $sale->customer?->name ?? 'Walk-in',
                    'total' => $sale->total_amount,
                    'paid' => $sale->paid_amount,
                    'balance' => $sale->balance_amount,
                    'date' => $date,
                    'status' => $sale->status,
                ];
            });

        // Calculate percentage changes
        $salesGrowth = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;
        $monthGrowth = $lastMonthSales > 0 ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        return response()->json([
            'metrics' => [
                'today_sales' => (float) $todaySales,
                'yesterday_sales' => (float) $yesterdaySales,
                'sales_growth' => round($salesGrowth, 2),
                'month_sales' => (float) $monthSales,
                'last_month_sales' => (float) $lastMonthSales,
                'month_growth' => round($monthGrowth, 2),
                'today_purchases' => (float) $todayPurchases,
                'month_purchases' => (float) $monthPurchases,
                'profit' => (float) $profit,
                'customer_due' => (float) $customerDue,
                'supplier_due' => (float) $supplierDue,
                'low_stock_items' => $lowStockCount,
                'product_count' => $productCount,
                'customer_count' => $customerCount,
                'supplier_count' => $supplierCount,
            ],
            'charts' => [
                'sales_trend_7' => $salesTrend,
                'sales_trend_30' => $salesTrend30,
            ],
            'top_products' => $topProducts,
            'top_customers' => $topCustomers,
            'low_stock_items' => $lowStockItems,
            'recent_sales' => $recentSales,
        ]);
    }
}
