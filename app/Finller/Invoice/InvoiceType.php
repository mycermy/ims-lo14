<?php

namespace App\Finller\Invoice;

enum InvoiceType: string
{
    case Invoice = 'invoice';
    case Quote = 'quote';
    case Credit = 'credit';
    case Proforma = 'proforma';

    public function trans(): string
    {
        return match ($this) {
            self::Invoice => __('invoice.types.invoice'),
            self::Quote => __('invoice.types.quote'),
            self::Credit => __('invoice.types.credit'),
            self::Proforma => __('invoice.types.proforma'),
        };
    }
}
