<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order Update</title>
  <style>
    body { margin: 0; padding: 0; background: #f5f5f5; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrap { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    .header { background: #0F2C4A; padding: 32px 40px; }
    .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
    .header p { margin: 4px 0 0; color: rgba(255,255,255,0.55); font-size: 13px; }
    .status-band { background: #1B7A52; padding: 18px 40px; }
    .status-band p { margin: 0; color: #ffffff; font-size: 15px; font-weight: 600; }
    .body { padding: 36px 40px; }
    .body p { color: #374151; font-size: 14px; line-height: 1.7; margin: 0 0 16px; }
    .order-box { background: #f8fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
    .order-box table { width: 100%; border-collapse: collapse; }
    .order-box td { padding: 6px 0; font-size: 13px; color: #374151; }
    .order-box td:last-child { text-align: right; font-weight: 600; color: #0F2C4A; }
    .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    .items-table th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; padding: 0 0 10px; border-bottom: 1px solid #e5e7eb; }
    .items-table td { padding: 10px 0; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; }
    .items-table td:last-child { text-align: right; }
    .footer { padding: 24px 40px; background: #f8fafb; border-top: 1px solid #f0f0f0; }
    .footer p { margin: 0; font-size: 12px; color: #9ca3af; line-height: 1.6; }
    .status-icon { font-size: 28px; margin-bottom: 8px; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>Gayatri Enterprises</h1>
    <p>Laboratory Chemicals &amp; Reagents</p>
  </div>

  <div class="status-band">
    <p>
      @if($order->status === 'dispatched') 🚚 Your order is on its way
      @elseif($order->status === 'delivered') ✅ Order delivered
      @elseif($order->status === 'packed') 📦 Order is being packed
      @elseif($order->status === 'cancelled') ❌ Order cancelled
      @else 🔔 Order status updated
      @endif
    </p>
  </div>

  <div class="body">
    <p>Dear {{ $order->client->company_name }},</p>

    @if($order->status === 'dispatched')
      <p>Your order <strong>#{{ $order->id }}</strong> has been dispatched and is on its way to you. Our team will coordinate delivery at your registered address.</p>
    @elseif($order->status === 'delivered')
      <p>Your order <strong>#{{ $order->id }}</strong> has been marked as delivered. We hope everything arrived in perfect condition.</p>
      <p>If you have any concerns about the shipment, please contact us immediately at <a href="mailto:orders@gayatrient.com" style="color:#1B7A52;">orders@gayatrient.com</a>.</p>
    @elseif($order->status === 'packed')
      <p>Your order <strong>#{{ $order->id }}</strong> has been packed and is ready for dispatch. You will receive another update once it ships.</p>
    @elseif($order->status === 'cancelled')
      <p>Your order <strong>#{{ $order->id }}</strong> has been cancelled. If you believe this is an error, please contact us at <a href="mailto:orders@gayatrient.com" style="color:#1B7A52;">orders@gayatrient.com</a>.</p>
    @endif

    <div class="order-box">
      <table>
        <tr>
          <td>Order #</td>
          <td>{{ $order->id }}</td>
        </tr>
        <tr>
          <td>Order Total</td>
          <td>₹{{ number_format((float)$order->total, 2) }}</td>
        </tr>
        <tr>
          <td>Payment Mode</td>
          <td>{{ match($order->payment_mode) { 'cash' => 'Cash', 'cheque' => 'Cheque', 'neft' => 'NEFT / Bank Transfer', default => '—' } }}</td>
        </tr>
        <tr>
          <td>Payment Status</td>
          <td>{{ ucfirst($order->payment_status ?? 'Unpaid') }}</td>
        </tr>
        @if($order->invoice)
        <tr>
          <td>Invoice No.</td>
          <td>{{ $order->invoice->invoice_no }}</td>
        </tr>
        @endif
      </table>
    </div>

    @if($order->items->count())
    <table class="items-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Unit Price</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
        <tr>
          <td>{{ $item->product->name }}</td>
          <td>{{ $item->qty }}</td>
          <td>₹{{ number_format((float)$item->unit_price, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

    <p>For queries, reach us at <a href="mailto:orders@gayatrient.com" style="color:#1B7A52;">orders@gayatrient.com</a> or call +91 90677 80801.</p>
  </div>

  <div class="footer">
    <p>Gayatri Enterprises &middot; Ground Floor, Pl19, Delhi Road, Preet Vihar, City Park, Hapur – 245101, UP, India<br>
    GSTIN: 09CPZPG0907C1ZT &middot; This is an automated notification. Please do not reply to this email.</p>
  </div>
</div>
</body>
</html>
