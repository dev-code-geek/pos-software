<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.invoice') }} {{ $sale->invoice_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #f4f5f7; }
        .invoice-sheet { max-width: 900px; margin: 30px auto; background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08); padding: 40px; }
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #eee; padding-bottom: 24px; margin-bottom: 24px; }
        .invoice-title { font-size: 1.8rem; font-weight: 700; color: #212529; letter-spacing: .5px; }
        .invoice-meta td { padding: 2px 8px 2px 0; }
        .invoice-meta td:first-child { color: #6c757d; }
        table.items th { background: #f8f9fa; font-size: .8rem; text-transform: uppercase; letter-spacing: .3px; color: #6c757d; }
        .totals-table td { padding: 6px 0; }
        .totals-table tr.grand-total td { border-top: 2px solid #212529; font-weight: 700; font-size: 1.1rem; padding-top: 12px; }
        .status-badge { font-size: .85rem; padding: .4em .9em; }
        @media print {
            body { background: #fff; }
            .invoice-sheet { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid no-print py-3">
    <div class="d-flex justify-content-between align-items-center" style="max-width:900px; margin:0 auto;">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back_to_sales') }}
        </a>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i> {{ __('messages.print') }}
            </button>
            @can('sale.pdf')
            <a href="{{ route('admin.sales.pdf', $sale->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> {{ __('messages.download_pdf') }}
            </a>
            @endcan
        </div>
    </div>
</div>

<div class="invoice-sheet">

    <div class="invoice-header">
        <div>
            <div class="invoice-title">{{ config('app.name') }}</div>
            <div class="text-muted small mt-1">
                {{ config('app.address', '') }}<br>
                {{ __('messages.phone') }}: {{ config('app.phone', '') }} &nbsp;|&nbsp; {{ __('messages.email') }}: {{ config('app.email', '') }}
            </div>
        </div>
        <div class="text-end">
            <div class="invoice-title text-primary">{{ __('messages.invoice') }}</div>
            <div class="text-muted small mt-1">#{{ $sale->invoice_number }}</div>
            <span class="badge status-badge mt-2 d-inline-block bg-{{ $sale->payment_status == 'paid' ? 'success' : ($sale->payment_status == 'pending' ? 'warning text-dark' : ($sale->payment_status == 'partial' ? 'info' : 'secondary')) }}">
                {{ __('messages.' . $sale->payment_status) }}
            </span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="text-muted small fw-bold text-uppercase mb-2">{{ __('messages.bill_to') }}</div>
            <div class="fw-bold">{{ $sale->customer->name ?? 'N/A' }}</div>
            @if(!empty($sale->customer->email))<div class="small text-muted">{{ $sale->customer->email }}</div>@endif
            @if(!empty($sale->customer->phone))<div class="small text-muted">{{ $sale->customer->phone }}</div>@endif
            @if(!empty($sale->customer->address))<div class="small text-muted">{{ $sale->customer->address }}</div>@endif
        </div>
        <div class="col-md-6">
            <table class="invoice-meta ms-auto" style="width:auto; margin-left:auto;">
                <tr><td>{{ __('messages.invoice_date') }}</td><td class="fw-bold">{{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : 'N/A' }}</td></tr>
                @if($sale->due_date)
                <tr><td>{{ __('messages.due_date') }}</td><td class="fw-bold">{{ $sale->due_date->format('M d, Y') }}</td></tr>
                @endif
                <tr><td>{{ __('messages.payment_method') }}</td><td class="fw-bold">{{ __('messages.' . ($sale->payment_method ?? 'cash')) }}</td></tr>
                <tr><td>{{ __('messages.currency') }}</td><td class="fw-bold">{{ $sale->currency->code ?? 'N/A' }}</td></tr>
                @if($sale->quotation_id)
                <tr><td>{{ __('messages.source') }}</td><td class="fw-bold">{{ __('messages.quotation') }} #{{ $sale->quotation_id }}</td></tr>
                @endif
                <tr><td>{{ __('messages.served_by') }}</td><td class="fw-bold">{{ $sale->user->name ?? 'N/A' }}</td></tr>
            </table>
        </div>
    </div>

    <table class="table items mb-4">
        <thead>
            <tr>
                <th style="width:35px;">#</th>
                <th>{{ __('messages.item') }}</th>
                <th>{{ __('messages.sku') }}</th>
                <th class="text-end">{{ __('messages.qty') }}</th>
                <th class="text-end">{{ __('messages.unit_price') }}</th>
                <th class="text-end">{{ __('messages.discount') }}</th>
                <th class="text-end">{{ __('messages.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td class="text-muted">{{ $item->product_sku }}</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->discount ?? 0, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($item->total, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-3">{{ __('messages.no_items_on_invoice') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="row">
        <div class="col-md-6">
            @if($sale->notes)
            <div class="text-muted small fw-bold text-uppercase mb-1">{{ __('messages.notes') }}</div>
            <p class="small">{{ $sale->notes }}</p>
            @endif
        </div>
        <div class="col-md-6">
            <table class="totals-table w-100">
                <tr><td class="text-muted">{{ __('messages.subtotal') }}</td><td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->subtotal, 2) }}</td></tr>
                @if($sale->discount > 0)
                <tr><td class="text-muted">{{ __('messages.discount') }}</td><td class="text-end">- {{ $sale->currency->code ?? '' }} {{ number_format($sale->discount, 2) }}</td></tr>
                @endif
                @if($sale->tax > 0)
                <tr><td class="text-muted">{{ __('messages.tax') }}</td><td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->tax, 2) }}</td></tr>
                @endif
                @if($sale->shipping_cost > 0)
                <tr><td class="text-muted">{{ __('messages.shipping') }}</td><td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->shipping_cost, 2) }}</td></tr>
                @endif
                <tr class="grand-total"><td>{{ __('messages.total') }}</td><td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->total, 2) }}</td></tr>
                <tr><td class="text-muted pt-2">{{ __('messages.amount_paid') }}</td><td class="text-end pt-2">{{ $sale->currency->code ?? '' }} {{ number_format($sale->amount_paid ?? 0, 2) }}</td></tr>
                <tr><td class="text-muted fw-bold">{{ __('messages.balance_due') }}</td><td class="text-end fw-bold {{ ($sale->total - ($sale->amount_paid ?? 0)) > 0 ? 'text-danger' : 'text-success' }}">{{ $sale->currency->code ?? '' }} {{ number_format($sale->total - ($sale->amount_paid ?? 0), 2) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="text-center text-muted small mt-5 pt-4 border-top">
        {{ __('messages.thank_you_business') }}
    </div>
</div>

</body>
</html>