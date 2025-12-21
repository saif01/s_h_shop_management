<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>Stock Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1.5px solid #1976d2; }
        .header h1 { color: #1976d2; font-size: 16px; margin-bottom: 2px; font-weight: bold; }
        .summary-section { margin-bottom: 8px; display: table; width: 100%; }
        .summary-box { display: table-cell; width: 25%; padding: 4px 6px; text-align: center; border: 1px solid #ddd; background-color: #f5f5f5; }
        .summary-box .label { font-size: 7px; color: #666; margin-bottom: 2px; }
        .summary-box .value { font-size: 10px; font-weight: bold; color: #1976d2; }
        .filters-section { margin-bottom: 6px; font-size: 7px; color: #666; }
        .filters-section span { margin-right: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8px; }
        thead { background-color: #1976d2; color: white; }
        th { padding: 4px 5px; text-align: left; font-weight: bold; font-size: 8px; }
        th.text-right { text-align: right; }
        td { padding: 3px 5px; border-bottom: 1px solid #ddd; font-size: 8px; }
        td.text-right { text-align: right; }
        .status-badge { display: inline-block; padding: 1px 5px; border-radius: 2px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .status-out { background-color: #f44336; color: white; }
        .status-low { background-color: #ff9800; color: white; }
        .status-ok { background-color: #4caf50; color: white; }
        .footer { margin-top: 10px; padding-top: 6px; border-top: 1px solid #ddd; text-align: center; font-size: 7px; color: #666; }
        .no-data { text-align: center; padding: 20px; color: #999; font-style: italic; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock Report</h1>
    </div>

    @if($summary)
    <div class="summary-section">
        <div class="summary-box">
            <div class="label">Total Products</div>
            <div class="value">{{ number_format($summary['total_products']) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Stock Value</div>
            <div class="value">Tk {{ number_format($summary['total_stock_value'], 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Low Stock Items</div>
            <div class="value">{{ number_format($summary['low_stock_count']) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Out of Stock</div>
            <div class="value">{{ number_format($summary['out_of_stock_count']) }}</div>
        </div>
    </div>
    @endif

    @if($filters['warehouse_id'] || $filters['category_id'] || $filters['low_stock_only'])
    <div class="filters-section">
        <strong>Filters:</strong>
        @if($filters['warehouse_id']) <span>Warehouse: {{ $warehouseName ?? 'Selected' }}</span> @endif
        @if($filters['category_id']) <span>Category: {{ $categoryName ?? 'Selected' }}</span> @endif
        @if($filters['low_stock_only']) <span>Low Stock Only: Yes</span> @endif
    </div>
    @endif

    @if(count($stock) > 0)
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Warehouse</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Min Level</th>
                <th class="text-right">Stock Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stock as $item)
            @php
                $quantity = $item->quantity ?? 0;
                $purchasePrice = $item->purchase_price ?? 0;
                $minLevel = $item->minimum_stock_level ?? 0;
                $status = 'ok';
                if ($quantity == 0) {
                    $status = 'out';
                } elseif ($minLevel > 0 && $quantity <= $minLevel) {
                    $status = 'low';
                }
            @endphp
            <tr>
                <td>{{ $item->product_name ?? 'N/A' }}</td>
                <td>{{ $item->sku ?? 'N/A' }}</td>
                <td>{{ $item->category_name ?? 'N/A' }}</td>
                <td>{{ $item->warehouse_name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($quantity) }}</td>
                <td class="text-right">{{ number_format($minLevel) }}</td>
                <td class="text-right">Tk {{ number_format($quantity * $purchasePrice, 2) }}</td>
                <td>
                    <span class="status-badge status-{{ $status }}">
                        @if($status == 'out') Out of Stock
                        @elseif($status == 'low') Low Stock
                        @else OK
                        @endif
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No stock data found for the selected criteria.</div>
    @endif

    <div class="footer">Generated on {{ date('d M Y, h:i A') }}</div>
</body>
</html>
