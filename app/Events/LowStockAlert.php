<?php

namespace App\Events;

use App\Models\Part;
use Illuminate\Foundation\Events\Dispatchable;

class LowStockAlert
{
    use Dispatchable;

    public function __construct(public Part $part)
    {
    }
}
