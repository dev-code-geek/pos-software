<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.quotation') }} #{{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info div { width: 48%; }
        .info .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #777; }
        [dir="rtl"] th, [dir="rtl"] td { text-align: right; }
        [dir="rtl"] .text-right { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.quotation_title') }}</h1>
        <p>#{{ $quotation->quotation_number }}</p>
    </div>

    <div class="info">
        <div>
            <span class="label">{{ __('messages.customer') }}:</span><br>
            {{ $quotation->customer->name ?? 'N/A' }}<br>
            {{ $quotation->customer->email ?? '' }}<br>
            {{ $quotation->customer->phone ?? '' }}<br>
            {{ $quotation->customer->address ?? '' }}
        </div>
        <div style="text-align: right;">
            <span class="label">{{ __('messages.quotation_date') }}:</span> {{ $quotation->quotation_date->format('M d, Y') }}<br>
            <span class="label">{{ __('messages.valid_until') }}:</span> {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'N/A' }}<br>
            <span class="label">{{ __('messages.currency') }}:</span> {{ $quotation->currency->code ?? 'TZS' }}<br>
            <span class="label">{{ __('messages.status') }}:</span> {{ __('messages.' . $quotation->status) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:40%;">{{ __('messages.product') }}</th>
                <th style="width:15%;" class="text-right">{{ __('messages.qty') }}</th>
                <th style="width:20%;" class="text-right">{{ __('messages.unit_price') }}</th>
                <th style="width:20%;" class="text-right">{{ __('messages.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $index => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product_name ?? $item->product->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>{{ __('messages.subtotal') }}:</strong></td>
                <td class="text-right">{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            @if($quotation->discount > 0)
            <tr>
                <td colspan="4" class="text-right"><strong>{{ __('messages.discount') }}:</strong></td>
                <td class="text-right">-{{ number_format($quotation->discount, 2) }}</td>
            </tr>
            @endif
            @if($quotation->tax > 0)
            <tr>
                <td colspan="4" class="text-right"><strong>{{ __('messages.tax') }}:</strong></td>
                <td class="text-right">{{ number_format($quotation->tax, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>{{ __('messages.total') }}:</strong></td>
                <td class="text-right">{{ number_format($quotation->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($quotation->notes)
    <div style="margin-top:20px;">
        <strong>{{ __('messages.notes') }}:</strong>
        <p>{{ $quotation->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>{{ __('messages.quotation_footer_note') }}</p>
    </div>
</body>
</html>