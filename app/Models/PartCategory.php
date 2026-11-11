<?php

namespace App\Models;

use Database\Factories\PartCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'status'])]
class PartCategory extends Model
{
    /** @use HasFactory<PartCategoryFactory> */
    use HasFactory;

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
