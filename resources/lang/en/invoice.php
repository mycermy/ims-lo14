<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invoice Language Lines
    |--------------------------------------------------------------------------
    */
    'document' => 'Invoice',
    'serial_number' => 'Invoice number',
    'reference' => 'Reference',
    'due_at' => 'Date due',
    'created_at' => 'Date of issue',
    'paid_at' => 'Date of payment',
    'description' => 'Description',
    'note' => 'Note',
    'total_amount' => 'Total',
    'tax' => 'Tax',
    'tax_label' => 'Tax',
    'subtotal_amount' => 'Subtotal',
    'amount' => 'Amount',
    'unit_price' => 'Unit price',
    'quantity' => 'Qty',
    'discount_name' => 'Discount',

    'from' => 'Bill From',
    'to' => 'Bill To',

    'states' => [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
    ],

    'types' => [
        'invoice' => 'Invoice',
        'quote' => 'Quote',
        'credit' => 'Credit note',
        'proforma' => 'Proforma invoice',
    ],
];
