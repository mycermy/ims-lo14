<?php

namespace App\Services;

use App\Finller\Invoice\PdfInvoice;
use App\Finller\Invoice\PdfInvoiceItem;
use App\Models\Invoice;
use App\Models\Sales\SalesOrder;
use App\Services\CompanyConfigService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brick\Money\Money;
use Carbon\Carbon;

class PdfService extends PdfInvoice
{
    private CompanyConfigService $companyConfig;

    public function __construct(
        CompanyConfigService $companyConfig
    ) {
        $this->companyConfig = $companyConfig;

        parent::__construct(
            logo: env('CLIENTLOGO_INVOICE_PATH') ?? config('invoices.pdf.logo') ?? config('invoices.default_logo'),
            color: config('invoices.pdf.color') ?? config('invoices.default_color'),
            font: config('invoices.pdf.options.defaultFont')
        );
    }

    public function orderPdf(SalesOrder $order, string $template = null): \Barryvdh\DomPDF\PDF
    {
        $order->load(['orderItems', 'orderItems.product', 'customer']);

        // Determine paper size based on content
        $itemCount = $order->orderItems->count();
        $paperSize = $itemCount > 3 ? 'a4' : 'a5';
        $paperOrientation = $itemCount > 3 ? 'portrait' : 'landscape';

        // Set PDF options with dynamic paper size
        $pdf = Pdf::setPaper($paperSize, $paperOrientation);

        // Transform invoice data to PdfInvoiceItem objects
        $items = $order->orderItems->map(function ($orderItem) {
            return new PdfInvoiceItem(
                label: $orderItem->product->code,
                description: $orderItem->product->name,
                unit_price: Money::of($orderItem->unit_price, $this->getCurrency()),
                quantity: $orderItem->quantity
            );
        })->toArray();

        // Set invoice properties
        $this->name = 'Order';
        $this->serial_number = $order->reference;
        $this->seller = $this->companyConfig->getCompanyDetails();
        $this->buyer = $order->customer->toArray();
        $this->items = $items;
        $this->created_at = Carbon::parse($order->date) ?? $order->created_at;
        $this->description = $order->note;

        return $pdf->loadView($template ?? $this->template, [
            'invoice' => $this,
        ]);
    }
}
