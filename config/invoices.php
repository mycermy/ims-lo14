<?php

use App\Finller\Invoice\InvoiceType;
use App\Finller\Invoice\InvoiceDiscount;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;

return [

    'model_document' => SalesOrder::class,
    'model_document_item' => SalesOrderItem::class,

    'discount_class' => InvoiceDiscount::class,

    'cascade_invoice_delete_to_invoice_items' => true,

    'serial_number' => [
        /**
         * If true, will generate a serial number on creation
         * If false, you will have to set the serial_number yourself
         */
        'auto_generate' => true,

        /**
         * Define the serial number format used for each invoice type
         *
         * P: Prefix
         * S: Serie
         * M: Month
         * Y: Year
         * C: Count
         * Example: IN0012-220234
         * Repeat letter to set the length of each information
         * Examples of formats:
         * - PPYYCCCC : IN220123 (default)
         * - PPPYYCCCC : INV220123
         * - PPSSSS-YYCCCC : INV0001-220123
         * - SSSS-CCCC: 0001-0123
         * - YYCCCC: 220123
         */
        'format' => [
            InvoiceType::Invoice->value => 'PPYYCCCC',
            InvoiceType::Quote->value => 'PPYYCCCC',
            InvoiceType::Credit->value => 'PPYYCCCC',
            InvoiceType::Proforma->value => 'PPYYCCCC',
        ],

        /**
         * Define the default prefix used for each invoice type
         */
        'prefix' => [
            InvoiceType::Invoice->value => 'IN',
            InvoiceType::Quote->value => 'QO',
            InvoiceType::Credit->value => 'CR',
            InvoiceType::Proforma->value => 'PF',
        ],

    ],

    'date_format' => 'd M Y',

    'default_seller' => [
        'name' => null,
        'address' => [
            'street' => null,
            'city' => null,
            'postal_code' => null,
            'state' => null,
            'country' => null,
        ],
        'email' => null,
        'phone_number' => null,
        'tax_number' => null,
        'company_number' => null,
    ],

    /**
     * ISO 4217 currency code
     */
    'default_currency' => 'MYR',
    'default_locale' => 'ms_MY',

    'pdf' => [
        /**
         * Default DOM PDF options
         *
         * @see Available options https://github.com/barryvdh/laravel-dompdf#configuration
         */
        'options' => [
            'isPhpEnabled' => true,
            'fontHeightRatio' => 0.8,
            /**
             * Supported values are: 'DejaVu Sans', 'Helvetica', 'Courier', 'Times', 'Symbol', 'ZapfDingbats'
             */
            'defaultFont' => 'Helvetica',
        ],

        'paper' => [
            'paper' => 'a5',
            'orientation' => 'landscape', // 'portrait',
        ],

        /**
         * The logo displayed in the PDF
         */
        'logo' => 'images/logo.png',

        /**
         * The color displayed at the top of the PDF
         */
        'color' => '#050038',

        /**
         * The template used to render the PDF
         */
        'template' => 'pdfs.finller.default.layout',

    ],

];
