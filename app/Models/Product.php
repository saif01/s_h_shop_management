<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $appends = ['stock_quantity'];

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
     * Get total stock quantity across all warehouses
     */
    public function getStockQuantityAttribute()
    {
        return $this->stocks()->sum('quantity');
    }

    /**
     * Get stock quantity for a specific warehouse
     */
    public function getStockForWarehouse($warehouseId)
    {
        return $this->stocks()->where('warehouse_id', $warehouseId)->first()?->quantity ?? 0;
    }
}
