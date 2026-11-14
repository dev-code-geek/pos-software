<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
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
        .no-print { }
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
            <i class="fas fa-arrow-left me-1"></i> Back to Sales
        </a>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i> Print
            </button>
            @can('sale.pdf')
            <a href="{{ route('admin.sales.pdf', $sale->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </a>
            @endcan
        </div>
    </div>
</div>

<div class="invoice-sheet">

    <div class="invoice-header">
        <div>
            {{-- TODO: replace with your actual company name / logo / address --}}
            <div class="invoice-title">Your Company Name</div>
            <div class="text-muted small mt-1">
                Address line 1, City, Country<br>
                Phone: +000 000 0000 &nbsp;|&nbsp; Email: info@yourcompany.com
            </div>
        </div>
        <div class="text-end">
            <div class="invoice-title text-primary">INVOICE</div>
            <div class="text-muted small mt-1">#{{ $sale->invoice_number }}</div>
            <span class="badge status-badge mt-2 d-inline-block bg-{{ $sale->payment_status == 'paid' ? 'success' : ($sale->payment_status == 'pending' ? 'warning text-dark' : ($sale->payment_status == 'partial' ? 'info' : 'secondary')) }}">
                {{ strtoupper($sale->payment_status) }}
            </span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="text-muted small fw-bold text-uppercase mb-2">Bill To</div>
            <div class="fw-bold">{{ $sale->customer->name ?? 'N/A' }}</div>
            @if(!empty($sale->customer->email))
                <div class="small text-muted">{{ $sale->customer->email }}</div>
            @endif
            @if(!empty($sale->customer->phone))
                <div class="small text-muted">{{ $sale->customer->phone }}</div>
            @endif
            @if(!empty($sale->customer->address))
                <div class="small text-muted">{{ $sale->customer->address }}</div>
            @endif
        </div>
        <div class="col-md-6">
            <table class="invoice-meta ms-auto" style="width:auto; margin-left:auto;">
                <tr>
                    <td>Invoice Date</td>
                    <td class="fw-bold">{{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @if($sale->due_date)
                <tr>
                    <td>Due Date</td>
                    <td class="fw-bold">{{ $sale->due_date->format('M d, Y') }}</td>
                </tr>
                @endif
                <tr>
                    <td>Payment Method</td>
                    <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'N/A')) }}</td>
                </tr>
                <tr>
                    <td>Currency</td>
                    <td class="fw-bold">{{ $sale->currency->code ?? 'N/A' }}</td>
                </tr>
                @if($sale->quotation_id)
                <tr>
                    <td>Source</td>
                    <td class="fw-bold">Quotation #{{ $sale->quotation_id }}</td>
                </tr>
                @endif
                <tr>
                    <td>Served By</td>
                    <td class="fw-bold">{{ $sale->user->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="table items mb-4">
        <thead>
            <tr>
                <th style="width:35px;">#</th>
                <th>Item</th>
                <th>SKU</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Total</th>
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
            <tr><td colspan="7" class="text-center text-muted py-3">No items on this invoice.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="row">
        <div class="col-md-6">
            @if($sale->notes)
            <div class="text-muted small fw-bold text-uppercase mb-1">Notes</div>
            <p class="small">{{ $sale->notes }}</p>
            @endif
        </div>
        <div class="col-md-6">
            <table class="totals-table w-100">
                <tr>
                    <td class="text-muted">Subtotal</td>
                    <td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                @if($sale->discount > 0)
                <tr>
                    <td class="text-muted">Discount</td>
                    <td class="text-end">- {{ $sale->currency->code ?? '' }} {{ number_format($sale->discount, 2) }}</td>
                </tr>
                @endif
                @if($sale->tax > 0)
                <tr>
                    <td class="text-muted">Tax</td>
                    <td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->tax, 2) }}</td>
                </tr>
                @endif
                @if($sale->shipping_cost > 0)
                <tr>
                    <td class="text-muted">Shipping</td>
                    <td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->shipping_cost, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>Total</td>
                    <td class="text-end">{{ $sale->currency->code ?? '' }} {{ number_format($sale->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted pt-2">Amount Paid</td>
                    <td class="text-end pt-2">{{ $sale->currency->code ?? '' }} {{ number_format($sale->amount_paid ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-bold">Balance Due</td>
                    <td class="text-end fw-bold {{ ($sale->total - ($sale->amount_paid ?? 0)) > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $sale->currency->code ?? '' }} {{ number_format($sale->total - ($sale->amount_paid ?? 0), 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="text-center text-muted small mt-5 pt-4 border-top">
        Thank you for your business!
    </div>
</div>

</body>
</html>