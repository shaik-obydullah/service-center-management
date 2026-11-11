<?php

namespace App\Models;

use Database\Factories\PartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['part_category_id', 'supplier_id', 'name', 'code', 'category', 'brand', 'model', 'cost_price', 'selling_price', 'quantity', 'minimum_stock', 'status'])]
class Part extends Model
{
    /** @use HasFactory<PartFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'cost_price' => 'float',
            'selling_price' => 'float',
            'quantity' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    public function partCategory(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(PartUsage::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'minimum_stock')->where('status', 'active');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->minimum_stock;
    }

    public function getStockValueAttribute(): float
    {
        return $this->quantity * $this->cost_price;
    }
}
