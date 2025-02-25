<?php

namespace App\Finller\Invoice;

use Brick\Money\Money;
use NumberFormatter;

trait FormatForPdf
{
    public function formatMoney(?Money $money = null, ?string $locale = null): ?string
    {
        $locale = $locale ?? config('invoices.default_locale') ?? app()->getLocale();
        
        return $money ? str_replace("\xe2\x80\xaf", ' ', $money->formatTo($locale)) : null;
    }

    public function formatPercentage(null|float|int $percentage, ?string $locale = null): string|false|null
    {
        if (! $percentage) {
            return null;
        }

        $locale = $locale ?? config('invoices.default_locale') ?? app()->getLocale();
        
        $formatter = new NumberFormatter($locale, NumberFormatter::PERCENT);

        return $formatter->format(($percentage > 1) ? ($percentage / 100) : $percentage);
    }
}
