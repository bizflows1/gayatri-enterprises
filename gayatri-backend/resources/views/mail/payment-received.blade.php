<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment Received</title>
  <style>
    body { margin: 0; padding: 0; background: #f5f5f5; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrap { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    .header { background: #0F2C4A; padding: 32px 40px; }
    .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 600; }
    .header p { margin: 4px 0 0; color: rgba(255,255,255,0.55); font-size: 13px; }
    .status-band { background: #1B7A52; padding: 18px 40px; }
    .status-band p { margin: 0; color: #ffffff; font-size: 15px; font-weight: 600; }
    .body { padding: 36px 40px; }
    .body p { color: #374151; font-size: 14px; line-height: 1.7; margin: 0 0 16px; }
    .receipt-box { background: #f8fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
    .receipt-box table { width: 100%; border-collapse: collapse; }
    .receipt-box td { padding: 7px 0; font-size: 13px; color: #374151; border-bottom: 1px solid #f0f0f0; }
    .receipt-box td:last-child { text-align: right; font-weight: 600; color: #0F2C4A; }
    .receipt-box tr:last-child td { border-bottom: none; }
    .amount { font-size: 28px; font-weight: 700; color: #1B7A52; text-align: center; padding: 20px 0 4px; }
    .amount-label { text-align: center; font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 24px; }
    .footer { padding: 24px 40px; background: #f8fafb; border-top: 1px solid #f0f0f0; }
    .footer p { margin: 0; font-size: 12px; color: #9ca3af; line-height: 1.6; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>Gayatri Enterprises</h1>
    <p>Laboratory Chemicals &amp; Reagents</p>
  </div>

  <div class="status-band">
    <p>✅ Payment received — thank you</p>
  </div>

  <div class="body">
    <p>Dear {{ $client->company_name }},</p>
    <p>We have recorded the following payment against your account. Your updated outstanding balance is shown below.</p>

    <div class="amount">₹{{ number_format((float)$payment->amount, 2) }}</div>
    <div class="amount-label">Amount Received</div>

    <div class="receipt-box">
      <table>
        <tr>
          <td>Payment Mode</td>
          <td>{{ match($payment->mode) { 'cash' => 'Cash', 'cheque' => 'Cheque', 'neft' => 'NEFT / Bank Transfer', default => ucfirst($payment->mode) } }}</td>
        </tr>
        @if($payment->reference)
        <tr>
          <td>Reference</td>
          <td>{{ $payment->reference }}</td>
        </tr>
        @endif
        <tr>
          <td>Date</td>
          <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
          <td>Outstanding Balance</td>
          <td>₹{{ number_format((float)$client->outstanding_balance, 2) }}</td>
        </tr>
        <tr>
          <td>Credit Limit</td>
          <td>₹{{ number_format((float)$client->credit_limit, 2) }}</td>
        </tr>
      </table>
    </div>

    <p>If you have any questions about this payment, please contact us at <a href="mailto:orders@gayatrient.com" style="color:#1B7A52;">orders@gayatrient.com</a> or call +91 90677 80801.</p>
  </div>

  <div class="footer">
    <p>Gayatri Enterprises &middot; Ground Floor, Pl19, Delhi Road, Preet Vihar, City Park, Hapur – 245101, UP, India<br>
    GSTIN: 09CPZPG0907C1ZT &middot; This is an automated receipt. Please retain for your records.</p>
  </div>
</div>
</body>
</html>
