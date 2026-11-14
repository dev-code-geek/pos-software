<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 3px solid #0d6efd;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            color: #0d6efd;
        }
        .content {
            padding: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 0.9rem;
            color: #666;
        }
        .po-details {
            background: #f1f3f5;
            padding: 12px 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .po-details strong {
            display: inline-block;
            width: 120px;
        }
        .btn {
            display: inline-block;
            background: #0d6efd;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .btn:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Purchase Order – {{ $purchaseOrder->po_number }}</h2>
    </div>

    <div class="content">
        <p>Dear {{ $purchaseOrder->supplier->name ?? 'Supplier' }},</p>

        <p>{{ $messageText ?? 'Please find attached the LPO for your reference.' }}</p>

        <div class="po-details">
            <p><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</p>
            <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('F d, Y') }}</p>
            <p><strong>Total Amount:</strong> {{ $purchaseOrder->currency->symbol ?? '$' }}{{ number_format($purchaseOrder->total_amount, 2) }}</p>
            @if($purchaseOrder->expected_delivery_date)
                <p><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date->format('F d, Y') }}</p>
            @endif
            @if($purchaseOrder->supplier_invoice_number)
                <p><strong>Supplier Invoice #:</strong> {{ $purchaseOrder->supplier_invoice_number }}</p>
            @endif
        </div>

        <p>If you have any questions, please do not hesitate to contact us.</p>

        <p>Thank you for your cooperation.</p>
    </div>

    <div class="footer">
        <p>
            <strong>{{ config('app.name') }}</strong><br>
            This is an automated email. Please do not reply directly.
        </p>
    </div>
</body>
</html>