<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'status'])]
class PartCategory extends Model
{
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
