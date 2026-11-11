<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::query()->where('key', $key)->value('value') ?? $default;
        } catch (Throwable) {
            return $default;
        }
    }
}

if (! function_exists('format_money')) {
    function format_money(float|int|string $amount, bool $withSymbol = true): string
    {
        $formatted = number_format((float) $amount, 2);
        $symbol = setting('currency_symbol', '৳');

        return $withSymbol ? "{$symbol} {$formatted}" : $formatted;
    }
}
