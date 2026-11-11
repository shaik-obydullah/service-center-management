<?php

namespace App\Models;

use Database\Factories\DeviceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'status'])]
class DeviceType extends Model
{
    /** @use HasFactory<DeviceTypeFactory> */
    use HasFactory;

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function repairServices(): HasMany
    {
        return $this->hasMany(RepairService::class);
    }
}
