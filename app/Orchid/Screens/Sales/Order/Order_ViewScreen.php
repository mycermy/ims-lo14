<?php

namespace App\Orchid\Screens\Sales\Order;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Sales\SalesPayment;
use App\Orchid\Screens\Sales\TabMenuOrder;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Screen;
use App\Orchid\Screen\TD;
use App\Services\PdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Order_ViewScreen extends Screen
{
    public ?SalesOrder $order = null;
    public $orderDetail;

    private PdfService $pdfService;

    public function __construct(
        public ?string $logo = null,
        public ?string $color = null,
        public ?string $font = null,
        PdfService $pdfService,
    ) {
        $this->logo = $logo ?? config('invoices.pdf.logo') ?? config('invoices.default_logo');
        $this->color = $color ?? config('invoices.pdf.color') ?? config('invoices.default_color');
        $this->font = $font ?? config('invoices.pdf.options.defaultFont');
        $this->pdfService = $pdfService;
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(SalesOrder $order): iterable
    {
        return [
            'order' => $order,
            'orderDetails' => $order->orderItems()->get(),
            'order_model' => SalesOrder::where('id', $order->id)->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Sales Order: ' . $this->order->reference;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Approve'))
                ->icon('bs.bag-check')
                ->confirm(__('You\'re about to approve this order.'))
                // ->canSee($this->can('update'))
                ->canSee($this->order->status == SalesOrder::STATUS_PENDING)
                ->method('approve'),

            Button::make(__('Revoke Approval'))
                ->icon('bs.bag-check')
                ->confirm(__('You\'re about to revoke this order approval.'))
                // ->canSee($this->can('update'))
                ->canSee($this->order->status == SalesOrder::STATUS_APPROVED && $this->order->payment_status == SalesPayment::STATUS_UNPAID)
                ->method('approvedRevoke'),

            // Link::make(__('Generate PDF'))
            //     ->canSee($this->order->status == SalesOrder::STATUS_APPROVED && $this->order->payment_status == SalesPayment::STATUS_UNPAID)
            //     ->icon('bs.filetype-pdf')
            //     ->route('platform.orders.pdf.store', [
            //         'order' => $this->order->id,
            //     ]),

            Link::make(__('View PDF'))
                ->icon('bs.filetype-pdf')
                ->route('platform.orders.pdf.stream', [
                    'order' => $this->order->id,
                ])
                ->target('_blank'),

            Link::make(__('Back'))
                ->icon('bs.x-circle')
                ->route('platform.orders'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            new TabMenuOrder($this->order),

            Layout::table('order_model', [
                TD::make('status')->alignCenter()
                    ->render(
                        function ($target) {
                            $class = ($target->status == SalesOrder::STATUS_APPROVED
                                || ($target->status == SalesOrder::STATUS_COMPLETED && $target->payment_status == SalesPayment::STATUS_PAID))
                                ? 'text-bg-success text-white'
                                : 'text-bg-danger';
                            return Link::make($target->status)
                                ->class($class . ' badge text-uppercase')
                                ->route('platform.orders.view', $target);
                        }
                    ),
                TD::make('date'),
                TD::make('reference')
                    ->render(
                        fn($target) =>
                        Link::make($target->reference)
                            ->route('platform.orders.view', $target)
                    ),
                TD::make('customer_name', 'Customer'),
                TD::make('updated_by', 'Updated By')->alignRight()->render(fn($target) => $target->updatedBy->name ?? null),
            ]),

            Layout::rows([
                Label::make('order.note')
                    ->title('Note: ')
                    ->horizontal(),
            ]),

            Layout::table('orderDetails', [
                TD::make('id', '#')->render(fn($target, object $loop) => $loop->iteration + (getPage() - 1) * $target->getPerPage()),
                TD::make('product_id', 'Code')
                    ->render(
                        function ($target) {
                            if ($target->product->code) {
                                return Link::make($target->product->code)
                                    ->route('platform.product.hist', $target->product->id);
                            } else {
                                return null;
                            }
                        }
                    ),
                TD::make('product_id', 'Product')->render(fn($target) => $target->product->name ?? null),
                TD::make('quantity', 'Qty')->alignCenter()->width(50),
                TD::make('unit_price', 'Unit Price')->alignRight()->width(100),
                TD::make('sub_total', 'Total')->alignRight()->width(100),
                TD::make('quantity_return', 'QtyReturn')->alignCenter()->width(50),
                // 
                TD::make('actions')->alignCenter()
                    ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor', 'platform.items.editor']))
                    ->width('10px')
                    ->render(
                        fn($target) =>
                        $this->getTableActions($target)
                            ->alignCenter()
                            ->autoWidth()
                            ->render()
                    ),
            ]),
        ];
    }

    /**
     * @param Model $model
     *
     * @return Group
     */
    private function getTableActions($target): Group
    {
        return Group::make([
            DropDown::make()
                ->icon('three-dots-vertical')
                ->list([
                    Link::make(__('Add Return'))
                        ->icon('bs.plus-circle')
                        // ->canSee($this->can('view'))
                        ->canSee(($target->quantity > $target->quantity_return)
                                && ($this->order->status == SalesOrder::STATUS_APPROVED || $this->order->status == SalesOrder::STATUS_COMPLETED)
                        )
                        ->route('platform.orders.returnbyorderitem.create', [$this->order, $target]),
                ]),
        ]);
    }

    public function approve(SalesOrder $order)
    {
        $orderDetails = SalesOrderItem::where('sales_order_id', $order->id)->get();

        foreach ($orderDetails as $orderDetail) {
            updateStock($orderDetail->product_id, $orderDetail->quantity, 'sales');
        }

        SalesOrder::findOrFail($order->id)
            ->update([
                'status' => SalesOrder::STATUS_APPROVED,
                'updated_by' => auth()->user()->id
            ]);

        // send notification to purchase creator and approver

        // 
        Toast::info(__('Sales order has been approved.'));

        return redirect()->route('platform.orders.view', $this->order);
    }

    // hanya terpakai pada SalesOrder::STATUS_APPROVED dan SalesOrder::STATUS_UNPAID
    public function approvedRevoke(SalesOrder $order)
    {
        $orderDetails = SalesOrderItem::where('sales_order_id', $order->id)->get();

        foreach ($orderDetails as $orderDetail) {
            // Product::where('id', $product->product_id)
            //         ->update(['quantity' => DB::raw('quantity+'.$product->quantity)]);
            updateStock($orderDetail->product_id, $orderDetail->quantity, 'salesRevoke');
        }

        SalesOrder::findOrFail($order->id)
            ->update([
                'status' => SalesOrder::STATUS_PENDING,
                'updated_by' => auth()->id(),
            ]);
        // send notification to purchase creator and approver

        // 
        Toast::warning(__('Sales order approval has been revoked!'));

        return redirect()->route('platform.orders.view', $this->order);
    }

    public function getFilename(SalesOrder $order): string
    {
        return $order->reference . '_order.pdf';
    }

    public function streamPDF(SalesOrder $order, $template = 'pdfs.finller.default.layout')
    {
        return $this->pdfService->orderPdf($order)->stream($this->getFilename($order));
    }

    protected function storePDF(SalesOrder $order, $template = 'pdfs.finller.default.layout')
    {
        // Generate PDF
        $pdf = $this->pdfService->orderPdf($order, $template);

        // Generate a unique filename
        $filename = 'orders/' . $this->getFilename($order);

        // Save PDF to storage
        Storage::put($filename, $pdf->output());

        // Update order with PDF path
        $order->update(['pdf_path' => $filename]);

        // return redirect()->back();
    }

    public function loadPDF(SalesOrder $order)
    {
        // dd($order->pdf_path);
        if (!$order->pdf_path || !Storage::exists($order->pdf_path)) {
            $this->storePDF($order);
        }

        return response()->file(storage_path('app/' . $order->pdf_path));
    }
}
