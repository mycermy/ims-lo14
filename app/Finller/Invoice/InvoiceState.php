<?php

namespace App\Finller\Invoice;

enum InvoiceState: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function trans(): string
    {
        return match ($this) {
            self::Draft => __('invoice.states.draft'),
            self::Pending => __('invoice.states.pending'),
            self::Paid => __('invoice.states.paid'),
            self::Refunded => __('invoice.states.refunded'),
        };
    }
}
