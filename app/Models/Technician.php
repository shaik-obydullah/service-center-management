<?php

namespace App\Models;

use Database\Factories\TechnicianFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'employee_id', 'name', 'phone', 'email', 'skills_json', 'hourly_rate', 'status'])]
class Technician extends Model
{
    /** @use HasFactory<TechnicianFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'skills_json' => 'array',
            'hourly_rate' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TechnicianAssignment::class);
    }

    public function getActiveWorkOrdersCountAttribute(): int
    {
        return $this->workOrders()
            ->whereIn('status', ['new', 'diagnosed', 'approved', 'ready', 'in_repair'])
            ->count();
    }

    public function getCompletedWorkOrdersCountAttribute(): int
    {
        return $this->workOrders()->where('status', 'completed')->count();
    }
}
