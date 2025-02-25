@php
    $displayTaxColumn = false; //!$order->totalTaxAmount()->isZero();
    $colspan = $displayTaxColumn ? '3' : '2';
@endphp

<div class="first-page">
    <div class="h-3 w-full" style="background-color: {{ $order->color?:'darkgray' }}"></div>
    <div class="m-12">
        <table class="mb-5 w-full">
            <tbody>
                <tr>
                    <td class="p-0 align-top">
                        <h1 class="mb-1 text-2xl">
                            <strong>{{ Str::upper($order->name) }}</strong>
                        </h1>
                        @if ($order->status)
                            <p class="mb-5">
                                {{ Str::ucfirst($order->payment_status) }}
                            </p>
                        @endif

                        <table class="w-full">
                            <tbody>
                                <tr class="">
                                    <td class="whitespace-nowrap pr-2 text-sm">
                                        <strong>{{ __('invoice.reference') }} </strong>
                                    </td>
                                    <td class="whitespace-nowrap text-sm" width="100%">
                                        <strong>{{ $order->reference }}</strong>
                                    </td>
                                </tr>
                                <tr class="text-xs">
                                    <td class="whitespace-nowrap pr-2">
                                        {{ __('invoice.created_at') }}
                                    </td>
                                    <td class="" width="100%">
                                        {{ $order->date?:$order->created_at->format('d M Y') }}
                                        {{-- {{ $order->created_at?->format(config('invoices.date_format')) }} --}}
                                    </td>
                                </tr>
                                @if ($order->due_at)
                                    <tr class="text-xs">
                                        <td class="whitespace-nowrap pr-2">
                                            {{ __('invoice.due_at') }}
                                        </td>
                                        <td class="" width="100%">
                                            {{ $order->due_at->format('d M Y') }}
                                            {{-- {{ $order->due_at->format(config('invoices.date_format')) }} --}}
                                        </td>
                                    </tr>
                                @endif
                                @if ($order->paid_at)
                                    <tr class="text-xs">
                                        <td class="whitespace-nowrap pr-2">
                                            {{ __('invoice.paid_at') }}
                                        </td>
                                        <td class="" width="100%">
                                            {{ $order->paid_at->format('d M Y') }}
                                            {{-- {{ $order->paid_at->format(config('invoices.date_format')) }} --}}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </td>
                    @if ($order->logo)
                        <td class="p-0 align-top" width="30%">
                            <img src="{{ $order->getLogo() }}" alt="logo" height="100">
                        </td>
                    @endif
                </tr>

            </tbody>
        </table>

        <table class="mb-6 w-full">
            <tbody>
                <tr>
                    <td class="p-0 align-top" width="50%">
                        @php
                            $name = data_get($order->seller, 'name');
                            $street = data_get($order->seller, 'address.street');
                            $postal_code = data_get($order->seller, 'address.postal_code');
                            $city = data_get($order->seller, 'address.city');
                            $state = data_get($order->seller, 'address.state');
                            $country = data_get($order->seller, 'address.country');
                            $email = data_get($order->seller, 'email');
                            $phone_number = data_get($order->seller, 'phone_number');
                            $tax_number = data_get($order->seller, 'tax_number');
                            $company_number = data_get($order->seller, 'company_number');
                        @endphp
                        <p class="pb-1 text-sm"><strong>{{ __('invoice.from') }}</strong></p>
                        @if ($name)
                            <p class="pb-1 text-xs">{{ $name }}</p>
                        @endif
                        @if ($street)
                            <p class="pb-1 text-xs">{{ $street }}</p>
                        @endif
                        @if ($postal_code || $city)
                            <p class="pb-1 text-xs">
                                {{ $postal_code }}
                                {{ $city }}
                            </p>
                        @endif
                        @if ($state)
                            <p class="pb-1 text-xs">{{ $state }}</p>
                        @endif
                        @if ($country)
                            <p class="pb-1 text-xs">{{ $country }}</p>
                        @endif
                        @if ($email)
                            <p class="pb-1 text-xs">{{ $email }}</p>
                        @endif
                        @if ($phone_number)
                            <p class="pb-1 text-xs">{{ $phone_number }}</p>
                        @endif
                        @if ($tax_number)
                            <p class="pb-1 text-xs">{{ $tax_number }}</p>
                        @endif
                        @if ($company_number)
                            <p class="pb-1 text-xs">{{ $company_number }}</p>
                        @endif
                        @foreach (data_get($order->seller, 'data') ?? [] as $key => $item)
                            @if (is_string($key))
                                <p class="pb-1 text-xs">{{ $key }}: {{ $item }}</p>
                            @else
                                <p class="pb-1 text-xs">{{ $item }}</p>
                            @endif
                        @endforeach
                    </td>
                    <td class="p-0 align-top" width="50%">
                        @php
                            $name = data_get($order->customer, 'name');
                            $street = data_get($order->customer, 'address.street');
                            $postal_code = data_get($order->customer, 'address.postal_code');
                            $city = data_get($order->customer, 'address.city');
                            $state = data_get($order->customer, 'address.state');
                            $country = data_get($order->customer, 'address.country');
                            $email = data_get($order->customer, 'email');
                            $phone_number = data_get($order->customer, 'phone_number');
                            $tax_number = data_get($order->customer, 'tax_number');
                            $company_number = data_get($order->customer, 'company_number');
                        @endphp
                        <p class="pb-1 text-sm"><strong>{{ __('invoice.to') }}</strong></p>
                        @if ($name)
                            <p class="pb-1 text-xs">{{ $name }}</p>
                        @endif
                        @if ($street)
                            <p class="pb-1 text-xs">{{ $street }}</p>
                        @endif
                        @if ($postal_code || $city)
                            <p class="pb-1 text-xs">
                                {{ $postal_code }}
                                {{ $city }}
                            </p>
                        @endif
                        @if ($state)
                            <p class="pb-1 text-xs">{{ $state }}</p>
                        @endif
                        @if ($country)
                            <p class="pb-1 text-xs">{{ $country }}</p>
                        @endif
                        @if ($email)
                            <p class="pb-1 text-xs">{{ $email }}</p>
                        @endif
                        @if ($phone_number)
                            <p class="pb-1 text-xs">{{ $phone_number }}</p>
                        @endif
                        @if ($tax_number)
                            <p class="pb-1 text-xs">{{ $tax_number }}</p>
                        @endif
                        @if ($company_number)
                            <p class="pb-1 text-xs">{{ $company_number }}</p>
                        @endif
                        @foreach (data_get($order->customer, 'data') ?? [] as $key => $item)
                            @if (is_string($key))
                                <p class="pb-1 text-xs">{{ $key }}: {{ $item }}</p>
                            @else
                                <p class="pb-1 text-xs">{{ $item }}</p>
                            @endif
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="mb-5 w-full">
            <thead>
                <tr>
                    <th class="whitespace-nowrap border-b py-2 pr-2 text-left text-xs font-normal">
                        {{ __('invoice.description') }}
                    </th>
                    <th class="whitespace-nowrap border-b p-2 text-center text-xs font-normal">
                        {{ __('invoice.quantity') }}
                    </th>
                    <th class="whitespace-nowrap border-b p-2 text-right text-xs font-normal">
                        {{ __('invoice.unit_price') }}
                    </th>
                    @if ($displayTaxColumn)
                        <th class="whitespace-nowrap border-b p-2 text-right text-xs font-normal">
                            {{ __('invoice.tax') }}
                        </th>
                    @endif
                    <th class="whitespace-nowrap border-b py-2 pl-2 text-right text-xs font-normal">
                        {{ __('invoice.amount') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderItems as $item)
                    <tr>
                        <td @class(['align-top py-2 pr-2', 'border-b' => !$loop->last])>
                            <p class="text-xs"><strong>{{ $item->product_name }}</strong></p>
                            @if ($item->product->code)
                                <p class="pt-1 text-xs">{{ $item->product->code }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap border-b p-2 text-center align-top text-xs">
                            <p>{{ $item->quantity }}</p>
                        </td>
                        <td class="whitespace-nowrap border-b p-2 text-right align-top text-xs">
                            <p>{{ $item->unit_price }}</p>
                            {{-- <p>{{ $item->formatMoney($item->unit_price) }}</p> --}}
                        </td>
                        @if ($displayTaxColumn)
                            <td class="whitespace-nowrap border-b p-2 text-right align-top text-xs">
                                @if ($item->unit_tax && $item->tax_percentage)
                                    <p>{{ $item->unit_tax }}
                                    {{-- <p>{{ $item->formatMoney($item->unit_tax) }} --}}
                                        ({{ $item->tax_percentage }})</p>
                                        {{-- ({{ $item->formatPercentage($item->tax_percentage) }})</p> --}}
                                @elseif ($item->unit_tax)
                                    <p>{{ $item->unit_tax }}</p>
                                    {{-- <p>{{ $item->formatMoney($item->unit_tax) }}</p> --}}
                                @else
                                    <p>{{ $item->tax_percentage }}</p>
                                    {{-- <p>{{ $item->formatPercentage($item->tax_percentage) }}</p> --}}
                                @endif
                            </td>
                        @endif
                        <td class="whitespace-nowrap border-b py-2 pl-2 text-right align-top text-xs">
                            <p>{{ $item->sub_total }}</p>
                            {{-- <p>{{ $item->formatMoney($item->totalAmount()) }}</p> --}}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    {{-- empty space --}}
                    <td class="py-2 pr-2"></td>
                    <td class="border-b p-2 text-xs" colspan="{{ $colspan }}">
                        {{ __('invoice.subtotal_amount') }}</td>
                    <td class="whitespace-nowrap border-b py-2 pl-2 text-right text-xs">
                        {{-- {{ $order->total_amount }} --}}
                        {{ $order->formatMoney($order->totalAmount()) }}
                    </td>
                </tr>
                @if ($order->discounts)
                    @foreach ($order->discounts as $discount)
                        <tr>
                            {{-- empty space --}}
                            <td class="py-2 pr-2"></td>
                            <td class="border-b p-2 text-xs" colspan="{{ $colspan }}">
                                {{ __($discount->name) ?? __('invoice.discount_name') }}
                                @if ($discount->percent_off)
                                    ({{ $discount->formatPercentage($discount->percent_off) }})
                                @endif
                            </td>
                            <td class="whitespace-nowrap border-b py-2 pl-2 text-right text-xs">
                                {{ $order->formatMoney($discount->computeDiscountAmountOn($order->subTotalAmount())?->multipliedBy(-1)) }}
                            </td>
                        </tr>
                    @endforeach
                @endif
                @if ($order->tax_label || $displayTaxColumn)
                    <tr>
                        {{-- empty space --}}
                        <td class="py-2 pr-2"></td>
                        <td class="border-b p-2 text-xs" colspan="{{ $colspan }}">
                            {{ $order->tax_label ?? __('invoice.tax_label') }}
                        </td>
                        <td class="whitespace-nowrap border-b py-2 pl-2 text-right text-xs">
                            {{ $order->tax_amount }}
                            {{-- {{ $order->formatMoney($order->totalTaxAmount()) }} --}}
                        </td>
                    </tr>
                @endif
                <tr>
                    {{-- empty space --}}
                    <td class="py-2 pr-2"></td>
                    <td class="border-b p-2 text-sm" colspan="{{ $colspan }}">
                        <strong>{{ __('invoice.total_amount') }}</strong>
                    </td>
                    <td class="whitespace-nowrap border-b py-2 pl-2 text-right text-sm">
                        <strong>
                            {{-- {{ $order->total_amount }} --}}
                            {{ $order->formatMoney($order->totalAmount()) }}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>

        @if ($order->note)
            <p class="mb-1 text-sm"><strong>{{ __('invoice.note') }}</strong></p>
            <p class="whitespace-pre-line text-xs">{!! $order->note !!}</p>
        @endif

    </div>
</div>
