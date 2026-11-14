<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LPO - {{ $purchaseOrder->po_number }}</title>
    <style>
        /* ============================================
           LPO PDF STYLES
           NOTE: Built for DomPDF / wkhtmltopdf compatibility.
           - No flexbox (unreliable in most PDF renderers)
           - No client-side JS (PDF renderers don't execute it)
           - Table-based layout throughout
        ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            padding: 18px 22px;
            background: #fff;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* ============================================
           HEADER
        ============================================ */
        .lpo-header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .lpo-header td {
            vertical-align: middle;
        }

        .lpo-logo-icon {
            width: 44px;
            height: 44px;
            background: #4f46e5;
            border-radius: 8px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
        }

        .lpo-logo-text {
            padding-left: 10px;
        }

        .lpo-logo-text h1 {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .lpo-logo-text p {
            font-size: 9px;
            color: #64748b;
            margin-top: 1px;
        }

        .lpo-title {
            text-align: right;
        }

        .lpo-title h2 {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
            letter-spacing: 1.5px;
        }

        .lpo-title .po-number {
            font-size: 12px;
            color: #0f172a;
            font-weight: 600;
        }

        .lpo-title .po-date {
            font-size: 9.5px;
            color: #64748b;
        }

        .lpo-status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .lpo-status-badge.draft     { background: #e2e8f0; color: #475569; }
        .lpo-status-badge.ordered   { background: #fef3c7; color: #92400e; }
        .lpo-status-badge.received  { background: #dcfce7; color: #166534; }
        .lpo-status-badge.cancelled { background: #fee2e2; color: #991b1b; }

        /* ============================================
           ADDRESS + DETAILS (combined, single band)
        ============================================ */
        .lpo-info {
            background: #f8fafc;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        .lpo-info td {
            vertical-align: top;
            font-size: 10px;
        }

        .lpo-info td.right-col {
            text-align: right;
        }

        .lpo-info .label {
            font-size: 9px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: block;
            margin-bottom: 3px;
        }

        .lpo-info .company-name {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
        }

        .lpo-info .address-text,
        .lpo-info .contact-text {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.4;
        }

        .lpo-meta {
            margin-bottom: 14px;
        }

        .lpo-meta td {
            padding: 0 10px 0 0;
        }

        .lpo-meta .label {
            font-size: 8.5px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: block;
        }

        .lpo-meta .value {
            font-size: 11px;
            font-weight: 600;
            color: #0f172a;
        }

        /* ============================================
           ITEMS TABLE
        ============================================ */
        .lpo-table {
            margin-bottom: 14px;
        }

        .lpo-table thead th {
            background: #f1f5f9;
            color: #0f172a;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        .lpo-table tbody td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .lpo-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        .lpo-table .item-name { font-weight: 500; }
        .lpo-table .item-sku  { font-size: 8.5px; color: #64748b; }

        /* ============================================
           TOTALS
        ============================================ */
        .lpo-totals-wrap { margin-bottom: 10px; }

        .lpo-totals-box {
            width: 260px;
            float: right;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .lpo-total-row td {
            padding: 5px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .lpo-total-row.grand-total td {
            background: #f1f5f9;
            font-weight: 700;
        }

        .lpo-total-row.grand-total .value {
            font-size: 13px;
            color: #4f46e5;
        }

        /* ============================================
           AMOUNT IN WORDS
        ============================================ */
        .lpo-amount-words {
            clear: both;
            margin-bottom: 12px;
            padding: 7px 12px;
            background: #f8fafc;
            border-radius: 5px;
            border-left: 3px solid #4f46e5;
            font-size: 9.5px;
        }

        .lpo-amount-words strong { color: #0f172a; }
        .lpo-amount-words .words { color: #475569; }

        /* ============================================
           TERMS
        ============================================ */
        .lpo-terms {
            margin-bottom: 10px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .lpo-terms .label {
            font-size: 9.5px;
            font-weight: 700;
            color: #0f172a;
            display: block;
            margin-bottom: 3px;
        }

        .lpo-terms .text {
            font-size: 9px;
            color: #475569;
            line-height: 1.5;
        }

        /* ============================================
           SIGNATURES
        ============================================ */
        .lpo-signatures {
            margin-top: 16px;
        }

        .lpo-signatures td {
            text-align: center;
            width: 33.33%;
            padding-top: 20px;
        }

        .lpo-signature-box .label {
            font-size: 8.5px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: block;
            margin-bottom: 22px;
        }

        .lpo-signature-box .line {
            width: 140px;
            border-bottom: 1px solid #cbd5e1;
            margin: 0 auto 3px;
        }

        .lpo-signature-box .name  { font-size: 9.5px; font-weight: 600; color: #0f172a; }
        .lpo-signature-box .title { font-size: 8.5px; color: #64748b; }
        .lpo-signature-box .date  { font-size: 8.5px; color: #94a3b8; }

        /* ============================================
           FOOTER NOTE
        ============================================ */
        .lpo-footer-note {
            margin-top: 14px;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    @php
        // ============================================
        // Currency-aware number-to-words function
        // ============================================
        if (!function_exists('lpoNumberToWords')) {
            function lpoNumberToWords($number, $currencyCode = 'Dollars')
            {
                $number = round((float) $number, 2);
                $whole = (int) floor($number);
                $cents = (int) round(($number - $whole) * 100);

                $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                          'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
                          'Eighteen', 'Nineteen'];
                $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

                $convertGroup = function ($num) use ($ones, $tens, &$convertGroup) {
                    if ($num == 0) return '';
                    if ($num < 20) return $ones[$num];
                    if ($num < 100) {
                        return rtrim($tens[intdiv($num, 10)] . '-' . $ones[$num % 10], '-');
                    }
                    return trim($ones[intdiv($num, 100)] . ' Hundred ' . $convertGroup($num % 100));
                };

                if ($whole === 0) {
                    $wordsWhole = 'Zero';
                } else {
                    $groups = ['', ' Thousand', ' Million', ' Billion'];
                    $groupIndex = 0;
                    $parts = [];
                    $n = $whole;
                    while ($n > 0) {
                        $group = $n % 1000;
                        if ($group > 0) {
                            $parts[] = trim($convertGroup($group) . $groups[$groupIndex]);
                        }
                        $n = intdiv($n, 1000);
                        $groupIndex++;
                    }
                    $wordsWhole = implode(' ', array_reverse($parts));
                }

                // Use the provided currency code
                $result = $wordsWhole . ' ' . $currencyCode;
                if ($cents > 0) {
                    $result .= ' and ' . $convertGroup($cents) . ' Cents';
                }

                return $result . ' Only';
            }
        }

        // Retrieve currency symbol and code from the purchase order
        $currencySymbol = $purchaseOrder->currency->symbol ?? '$';
        $currencyCode   = $purchaseOrder->currency->code ?? 'Dollars';

        // Generate the amount in words with the correct currency code
        $amountInWords = lpoNumberToWords($purchaseOrder->total_amount, $currencyCode);
    @endphp

    <!-- ============================================
    HEADER
    ============================================ -->
    <table class="lpo-header">
        <tr>
            <td style="width: 60%;">
                <table>
                    <tr>
                        <td style="width: 44px;">
                            <div class="lpo-logo-icon">ICT</div>
                        </td>
                        <td class="lpo-logo-text">
                            <h1>ICT Inventory</h1>
                            <p>Asset Management System</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="lpo-title" style="width: 40%;">
                <h2>LOCAL PURCHASE ORDER</h2>
                <div class="po-number">{{ $purchaseOrder->po_number }}</div>
                <div class="po-date">Date: {{ $purchaseOrder->order_date->format('F d, Y') }}</div>
                <span class="lpo-status-badge {{ $purchaseOrder->status }}">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
            </td>
        </tr>
    </table>

    <!-- ============================================
    ADDRESSES
    ============================================ -->
    <table class="lpo-info">
        <tr>
            <td style="width: 50%;">
                <span class="label">Supplier</span>
                <div class="company-name">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</div>
                @if($purchaseOrder->supplier)
                    <div class="address-text">{{ $purchaseOrder->supplier->address ?? '' }}</div>
                    <div class="contact-text">{{ $purchaseOrder->supplier->contact_person ?? 'N/A' }} &middot; {{ $purchaseOrder->supplier->email ?? 'N/A' }} &middot; {{ $purchaseOrder->supplier->phone ?? 'N/A' }}</div>
                @endif
            </td>
            <td class="right-col" style="width: 50%;">
                <span class="label">Bill To</span>
                <div class="company-name">ICT Inventory</div>
                <div class="address-text">123 ICT Street, Technology City, TC 12345</div>
                <div class="contact-text">procurement@ictinventory.com &middot; +254 700 000 000</div>
            </td>
        </tr>
    </table>

    <!-- ============================================
    DETAILS
    ============================================ -->
    <table class="lpo-meta">
        <tr>
            <td>
                <span class="label">PO Number</span>
                <span class="value">{{ $purchaseOrder->po_number }}</span>
            </td>
            <td>
                <span class="label">Order Date</span>
                <span class="value">{{ $purchaseOrder->order_date->format('F d, Y') }}</span>
            </td>
            <td>
                <span class="label">Expected Delivery</span>
                <span class="value">{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F d, Y') : 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Payment Terms</span>
                <span class="value">Net 30 Days</span>
            </td>
        </tr>
    </table>

    <!-- ============================================
    ITEMS TABLE
    ============================================ -->
    <table class="lpo-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Item Description</th>
                <th style="width: 15%;">SKU</th>
                <th style="width: 13%; text-align:center;">Qty</th>
                <th style="width: 13%; text-align:right;">Unit Price</th>
                <th style="width: 14%; text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="item-name">{{ $item->product->name ?? 'N/A' }}</td>
                <td class="item-sku">{{ $item->product->sku ?? 'N/A' }}</td>
                <td class="text-center">{{ $item->quantity_ordered }}</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ============================================
    TOTALS
    ============================================ -->
    <div class="lpo-totals-wrap">
        <table class="lpo-totals-box">
            <tr class="lpo-total-row">
                <td class="label">Subtotal</td>
                <td class="value text-right">{{ $currencySymbol }}{{ number_format($purchaseOrder->total_amount, 2) }}</td>
            </tr>
            <tr class="lpo-total-row">
                <td class="label">Tax (0%)</td>
                <td class="value text-right">{{ $currencySymbol }}0.00</td>
            </tr>
            <tr class="lpo-total-row">
                <td class="label">Shipping</td>
                <td class="value text-right">{{ $currencySymbol }}0.00</td>
            </tr>
            <tr class="lpo-total-row grand-total">
                <td class="label">Grand Total</td>
                <td class="value text-right">{{ $currencySymbol }}{{ number_format($purchaseOrder->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- ============================================
    AMOUNT IN WORDS
    ============================================ -->
    <div class="lpo-amount-words">
        <strong>Amount in Words:</strong>
        <span class="words">{{ $amountInWords }}</span>
    </div>

    <!-- ============================================
    TERMS & CONDITIONS
    ============================================ -->
    <div class="lpo-terms">
        <span class="label">Terms &amp; Conditions</span>
        <div class="text">
            Payment: Net 30 days from invoice date. Delivery per agreed schedule. All items must meet
            specified quality standards. 12-month warranty on all supplied items. Disputes resolved through
            mutual consultation.
        </div>
    </div>

    @if($purchaseOrder->notes)
    <div class="lpo-terms">
        <span class="label">Additional Notes</span>
        <div class="text">{{ $purchaseOrder->notes }}</div>
    </div>
    @endif

    <!-- ============================================
    SIGNATURES
    ============================================ -->
    <table class="lpo-signatures">
        <tr>
            <td>
                <div class="lpo-signature-box">
                    <span class="label">Prepared By</span>
                    <div class="line"></div>
                    <div class="name">{{ $purchaseOrder->user->name ?? 'N/A' }}</div>
                    <div class="title">Procurement Officer</div>
                    <div class="date">{{ now()->format('F d, Y') }}</div>
                </div>
            </td>
            <td>
                <div class="lpo-signature-box">
                    <span class="label">Approved By</span>
                    <div class="line"></div>
                    <div class="name">&mdash;</div>
                    <div class="title">Director</div>
                    <div class="date">&mdash;</div>
                </div>
            </td>
            <td>
                <div class="lpo-signature-box">
                    <span class="label">Supplier Signature</span>
                    <div class="line"></div>
                    <div class="name">&mdash;</div>
                    <div class="title">Authorized Signatory</div>
                    <div class="date">&mdash;</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="lpo-footer-note">
        This is a computer-generated document. No signature is required. &middot; Generated on: {{ now()->format('F d, Y H:i:s') }}
    </div>

</body>
</html>