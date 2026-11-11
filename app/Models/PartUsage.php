<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['work_order_id', 'part_id', 'quantity', 'unit_price', 'total'])]
class PartUsage extends Model
{
    protected $table = 'part_usage';

    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
            'total' => 'float',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
