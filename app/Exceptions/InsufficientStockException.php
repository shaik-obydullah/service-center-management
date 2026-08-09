<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(string $partName = '')
    {
        $message = $partName
            ? "Insufficient stock for part: {$partName}"
            : 'Insufficient stock for the requested part.';

        parent::__construct($message, 422);
    }
}
