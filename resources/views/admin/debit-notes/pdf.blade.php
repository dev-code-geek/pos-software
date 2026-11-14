<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debit Note #{{ $debitNote->debit_note_number }}</title>
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
            border-bottom: 2px solid #dc3545;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 28px;
            color: #dc3545;
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
            color: #dc3545;
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
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-draft { background: #f3f4f6; color: #4b5563; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #fef3c7; color: #92400e; }
        .notes-section {
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
        }
        .notes-section strong {
            color: #4b5563;
        }
        .badge {
            background: #dc3545;
            color: #fff;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>
            <h1>DEBIT NOTE</h1>
            <p style="margin-top:5px; color:#6b7280;">#{{ $debitNote->debit_note_number }}</p>
        </div>
        <div class="company">
            <strong>ICT Inventory System</strong><br>
            Dar es Salaam, Tanzania<br>
            info@ictsystem.com<br>
            +255 700 000 000
        </div>
    </div>

    <!-- Supplier & Debit Note Info -->
    <div class="info">
        <div>
            <div class="label">SUPPLIER</div>
            <p>
                <strong>{{ $debitNote->supplier->name ?? 'N/A' }}</strong><br>
                {{ $debitNote->supplier->contact_person ?? '' }}<br>
                {{ $debitNote->supplier->email ?? '' }}<br>
                {{ $debitNote->supplier->phone ?? '' }}<br>
                {{ $debitNote->supplier->address ?? '' }}
            </p>
        </div>
        <div style="text-align:right;">
            <div class="label">DEBIT NOTE DETAILS</div>
            <p>
                <span class="label">Date:</span> {{ $debitNote->debit_note_date->format('M d, Y') }}<br>
                <span class="label">Currency:</span> {{ $debitNote->currency->code ?? 'TZS' }}<br>
                <span class="label">Status:</span>
                <span class="status-badge status-{{ $debitNote->status }}">
                    {{ ucfirst($debitNote->status) }}
                </span>
            </p>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:40%;">Product</th>
                <th style="width:15%;" class="text-right">Qty</th>
                <th style="width:15%;" class="text-right">Unit Price</th>
                <th style="width:15%;" class="text-right">Total</th>
                <th style="width:10%;">Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debitNote->items as $index => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
                <td>{{ $item->reason ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Subtotal</strong></td>
                <td class="text-right">{{ number_format($debitNote->subtotal, 2) }}</td>
                <td></td>
            </tr>
            @if($debitNote->tax > 0)
            <tr>
                <td colspan="4" class="text-right"><strong>Tax</strong></td>
                <td class="text-right">{{ number_format($debitNote->tax, 2) }}</td>
                <td></td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>Total</strong></td>
                <td class="text-right grand-total">{{ number_format($debitNote->total, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Reason & Notes -->
    <div style="margin-top:20px;">
        <div class="label">Reason</div>
        <p><strong>{{ $debitNote->reason }}</strong></p>
    </div>

    @if($debitNote->notes)
    <div class="notes-section">
        <strong>Notes:</strong>
        <p style="margin-top:5px;">{{ $debitNote->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated debit note.</p>
        <p>Thank you for your business!</p>
    </div>
</body>
</html>