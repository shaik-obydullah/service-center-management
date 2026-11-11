<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'device_type_id', 'status'])]
class Brand extends Model
{
    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class);
    }
}
