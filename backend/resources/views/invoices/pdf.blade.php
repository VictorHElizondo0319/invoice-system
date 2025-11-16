<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .company { font-weight:700; font-size:18px; }
        .meta { text-align:right; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { padding:8px; border:1px solid #ddd; }
        th { background:#f5f5f5; }
        .right { text-align:right; }
        .total { font-weight:700; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company">Your Company</div>
            <div>123 Business Rd.</div>
            <div>business@example.com</div>
        </div>
        <div class="meta">
            <div>Invoice #: {{ $invoice->id }}</div>
            <div>Date: {{ $invoice->created_at->format('Y-m-d') }}</div>
            <div>Due: {{ optional($invoice->due_date)->format('Y-m-d') }}</div>
        </div>
    </div>

    <div>
        <strong>Bill To:</strong>
        <div>{{ $invoice->customer_name }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->qty }}</td>
                    <td class="right">{{ number_format($item->price, 2) }}</td>
                    <td class="right">{{ number_format($item->qty * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right total">Total</td>
                <td class="right total">{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->payments && $invoice->payments->count())
        <h4 style="margin-top:20px;">Payments</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        <td class="right">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top:30px; font-size:12px; color:#666;">Thank you for your business.</div>
</body>
</html>
