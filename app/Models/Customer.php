<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'phone', 'address', 'city', 'nid_number', 'contact_preference', 'loyalty_member'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'loyalty_member' => 'boolean',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->workOrders()
            ->whereHas('invoice', fn ($q) => $q->where('status', 'paid'))
            ->with('invoice')
            ->get()
            ->sum(fn ($wo) => $wo->invoice?->total ?? 0);
    }
}
