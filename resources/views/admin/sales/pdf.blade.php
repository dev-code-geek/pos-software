<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.invoice') }} - {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #ddd; background: #fff; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #4f46e5; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #4f46e5; font-size: 24px; }
        .header .invoice-info { text-align: right; font-size: 14px; color: #555; }
        .customer-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; }
        .customer-info .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #f8f9fa; text-align: left; padding: 10px; border-bottom: 2px solid #dee2e6; }
        table td { padding: 10px; border-bottom: 1px solid #dee2e6; }
        table .text-end { text-align: right; }
        .totals { margin-top: 20px; text-align: right; font-size: 14px; }
        .totals .grand-total { font-size: 18px; font-weight: bold; color: #4f46e5; }
        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px; font-size: 12px; color: #777; text-align: center; }
        .payment-status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-partial { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-overdue { background: #f8d7da; color: #721c24; }
        [dir="rtl"] table th, [dir="rtl"] table td { text-align: right; }
        [dir="rtl"] .text-end { text-align: left; }
        [dir="rtl"] .totals { text-align: left; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div>
                <h1>{{ __('messages.invoice') }}</h1>
                <p style="margin:5px 0 0; color:#666;">{{ config('app.name') }}</p>
            </div>
            <div class="invoice-info">
                <div><strong>{{ __('messages.invoice_number') }}:</strong> {{ $sale->invoice_number }}</div>
                <div><strong>{{ __('messages.date') }}:</strong> {{ $sale->sale_date->format('M d, Y') }}</div>
                @if($sale->due_date)
                    <div><strong>{{ __('messages.due_date') }}:</strong> {{ $sale->due_date->format('M d, Y') }}</div>
                @endif
                <div>
                    <strong>{{ __('messages.status') }}:</strong>
                    <span class="payment-status status-{{ $sale->payment_status }}">{{ __('messages.' . $sale->payment_status) }}</span>
                </div>
            </div>
        </div>

        <div class="customer-info">
            <div>
                <div class="label">{{ __('messages.bill_to') }}:</div>
                <div>{{ $sale->customer->name ?? __('messages.walk_in_customer') }}</div>
                @if($sale->customer)
                    <div>{{ $sale->customer->address ?? '' }}</div>
                    <div>{{ $sale->customer->phone ?? '' }}</div>
                    <div>{{ $sale->customer->email ?? '' }}</div>
                @endif
            </div>
            <div>
                <div class="label">{{ __('messages.payment_method') }}:</div>
                <div>{{ __('messages.' . ($sale->payment_method ?? 'cash')) }}</div>
                <div class="label">{{ __('messages.currency') }}:</div>
                <div>{{ $sale->currency->code ?? 'USD' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.product') }}</th>
                    <th class="text-end">{{ __('messages.qty') }}</th>
                    <th class="text-end">{{ __('messages.price') }}</th>
                    <th class="text-end">{{ __('messages.discount') }}</th>
                    <th class="text-end">{{ __('messages.tax') }}</th>
                    <th class="text-end">{{ __('messages.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $idx => $item)
                <tr>
                    <td>{{ $idx+1 }}</td>
                    <td>{{ $item->product_name ?? $item->product->name ?? 'N/A' }}</td>
                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->tax_amount ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div>{{ __('messages.subtotal') }}: <strong>{{ number_format($sale->subtotal, 2) }}</strong></div>
            @if($sale->discount > 0)<div>{{ __('messages.discount') }}: <strong>- {{ number_format($sale->discount, 2) }}</strong></div>@endif
            @if($sale->tax > 0)<div>{{ __('messages.tax') }}: <strong>{{ number_format($sale->tax, 2) }}</strong></div>@endif
            @if($sale->shipping_cost > 0)<div>{{ __('messages.shipping') }}: <strong>{{ number_format($sale->shipping_cost, 2) }}</strong></div>@endif
            <div class="grand-total">{{ __('messages.total') }}: {{ number_format($sale->total, 2) }}</div>
            <div>{{ __('messages.amount_paid') }}: {{ number_format($sale->amount_paid ?? 0, 2) }}</div>
            <div>{{ __('messages.balance') }}: {{ number_format($sale->balance ?? 0, 2) }}</div>
        </div>

        @if($sale->notes)
            <div style="margin-top:20px; border-top:1px solid #eee; padding-top:10px;">
                <strong>{{ __('messages.notes') }}:</strong>
                <p>{{ $sale->notes }}</p>
            </div>
        @endif

        <div class="footer">
            <p>{{ __('messages.thank_you_business') }}</p>
            <p>{{ config('app.name') }} &bull; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>