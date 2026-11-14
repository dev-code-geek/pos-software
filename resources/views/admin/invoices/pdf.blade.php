<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            padding: 30px;
            color: #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 28px;
            color: #4f46e5;
            margin: 0;
        }
        .header .company {
            text-align: right;
            font-size: 14px;
        }
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        .info div {
            width: 48%;
        }
        .info .label {
            font-weight: bold;
            color: #666;
            font-size: 11px;
        }
        .info p {
            margin: 3px 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #f3f4f6;
            color: #1f2937;
            font-weight: 600;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background: #f9fafb;
            font-weight: bold;
        }
        .total-row td {
            padding: 10px 8px;
        }
        .grand-total {
            font-size: 16px;
            color: #4f46e5;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-partial { background: #dbeafe; color: #1e40af; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .notes-section {
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
        }
        .notes-section strong {
            color: #4b5563;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>
            <h1>INVOICE</h1>
            <p style="margin-top:5px; color:#6b7280;">#{{ $invoice->invoice_number }}</p>
        </div>
        <div class="company">
            <strong>ICT Inventory System</strong><br>
            Dar es Salaam, Tanzania<br>
            info@ictsystem.com<br>
            +255 700 000 000
        </div>
    </div>

    <!-- Customer & Invoice Info -->
    <div class="info">
        <div>
            <div class="label">BILL TO</div>
            <p>
                <strong>{{ $invoice->customer->name ?? 'Walk-in Customer' }}</strong><br>
                {{ $invoice->customer->email ?? '' }}<br>
                {{ $invoice->customer->phone ?? '' }}<br>
                {{ $invoice->customer->address ?? '' }}
            </p>
        </div>
        <div style="text-align:right;">
            <div class="label">INVOICE DETAILS</div>
            <p>
                <span class="label">Invoice Date:</span> {{ $invoice->invoice_date->format('M d, Y') }}<br>
                <span class="label">Due Date:</span> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}<br>
                <span class="label">Status:</span>
                <span class="status-badge status-{{ $invoice->payment_status }}">
                    {{ ucfirst($invoice->payment_status) }}
                </span>
            </p>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:45%;">Description</th>
                <th style="width:15%;" class="text-right">Qty</th>
                <th style="width:15%;" class="text-right">Unit Price</th>
                <th style="width:20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->sale->items ?? [] as $index => $item)
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
                <td colspan="4" class="text-right"><strong>Subtotal</strong></td>
                <td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td colspan="4" class="text-right"><strong>Discount</strong></td>
                <td class="text-right">-{{ number_format($invoice->discount, 2) }}</td>
            </tr>
            @endif
            @if($invoice->tax > 0)
            <tr>
                <td colspan="4" class="text-right"><strong>Tax</strong></td>
                <td class="text-right">{{ number_format($invoice->tax, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>Total</strong></td>
                <td class="text-right grand-total">{{ number_format($invoice->total, 2) }}</td>
            </tr>
            @if($invoice->amount_paid > 0)
            <tr>
                <td colspan="4" class="text-right"><strong>Amount Paid</strong></td>
                <td class="text-right">{{ number_format($invoice->amount_paid, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Balance Due</strong></td>
                <td class="text-right">{{ number_format($invoice->balance, 2) }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    <!-- Notes -->
    @if($invoice->notes)
    <div class="notes-section">
        <strong>Notes:</strong>
        <p style="margin-top:5px;">{{ $invoice->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business! Payment is due within 30 days.</p>
        <p>This is a computer-generated invoice.</p>
    </div>
</body>
</html>