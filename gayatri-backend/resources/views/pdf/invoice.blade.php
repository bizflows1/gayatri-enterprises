<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; font-size: 13px; line-height: 20px; color: #555; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .invoice-box table td, .invoice-box table th { padding: 6px 8px; vertical-align: top; }
        .title { font-size: 32px; line-height: 36px; color: #1b3a5c; font-weight: bold; }
        .meta td { padding-bottom: 4px; }
        .items th { background: #1b3a5c; color: #fff; font-weight: bold; }
        .items td { border-bottom: 1px solid #eee; }
        .totals td:first-child { text-align: right; font-weight: bold; }
        .totals .grand td { border-top: 2px solid #1b3a5c; font-size: 16px; color: #1b3a5c; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #888; border-top: 1px solid #eee; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="meta">
            <tr>
                <td class="title">Gayatri Enterprises</td>
                <td>
                    <strong>Tax Invoice</strong><br>
                    Invoice #: {{ $invoice->invoice_no }}<br>
                    Date: {{ $invoice->created_at->format('d M Y') }}
                </td>
            </tr>
            <tr>
                <td>Authorised distributor &mdash; B2B chemical &amp; reagent supply</td>
                <td>
                    <strong>Billed To</strong><br>
                    {{ $order->client->company_name }}<br>
                    GSTIN: {{ $order->client->gstin ?? '—' }}
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>HSN</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product->hsn_code ?? '-' }}</td>
                        <td>{{ $item->product->name }} ({{ $item->product->pack_size }})</td>
                        <td>{{ $item->qty }}</td>
                        <td>&#8377; {{ number_format($item->effectivePrice(), 2) }}</td>
                        <td>&#8377; {{ number_format($item->lineTotal(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td>Subtotal</td><td>&#8377; {{ number_format($order->subtotal, 2) }}</td></tr>
            <tr><td>CGST</td><td>&#8377; {{ number_format($invoice->gst_breakup_json['cgst'] ?? 0, 2) }}</td></tr>
            <tr><td>SGST</td><td>&#8377; {{ number_format($invoice->gst_breakup_json['sgst'] ?? 0, 2) }}</td></tr>
            <tr class="grand"><td>Total</td><td>&#8377; {{ number_format($order->total, 2) }}</td></tr>
        </table>

        <div class="footer">
            Computer generated invoice. No signature required.
        </div>
    </div>
</body>
</html>
