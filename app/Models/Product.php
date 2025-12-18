<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'unit_id',
        'description',
        'brand',
        'image',
        'purchase_price',
        'sale_price',
        'tax_rate',
        'minimum_stock_level',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    /**
     * Scope to add total stock quantity using efficient subquery
     * Prevents N+1 query problem
     */
    public function scopeWithStockQuantity($query)
    {
        return $query->addSelect([
            'stock_quantity' => Stock::selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('product_id', 'products.id')
        ]);
    }

    /**
     * Scope to add stock quantity for a specific warehouse
     */
    public function scopeWithStockForWarehouse($query, $warehouseId)
    {
        return $query->addSelect([
            'stock_quantity' => Stock::selectRaw('COALESCE(quantity, 0)')
                ->whereColumn('product_id', 'products.id')
                ->where('warehouse_id', $warehouseId)
                ->limit(1)
        ]);
    }

    /**
     * Get stock quantity for a specific warehouse (direct method)
     */
    public function getStockForWarehouse($warehouseId)
    {
        return $this->stocks()->where('warehouse_id', $warehouseId)->first()?->quantity ?? 0;
    }
}
