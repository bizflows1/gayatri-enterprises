<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * GST invoice generation. Tax split is a flat 18% (9% CGST + 9% SGST)
 * placeholder applied uniformly — swap for real per-HSN/inter-state rates
 * once those are configured; this is intentionally not invented further
 * than the architecture doc specifies, since real tax rules need sign-off.
 */
class InvoiceService
{
    private const GST_RATE = 0.18;

    public function generate(Order $order): Invoice
    {
        $cgst = round((float) $order->subtotal * self::GST_RATE / 2, 2);
        $sgst = $cgst;

        $hsnSummary = $order->items
            ->groupBy(fn ($item) => $item->product->hsn_code ?? 'UNSPECIFIED')
            ->map(fn ($items, $hsn) => [
                'hsn_code' => $hsn,
                'qty' => $items->sum('qty'),
                'taxable_value' => $items->sum(fn ($item) => $item->lineTotal()),
            ])
            ->values()
            ->all();

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_no' => $this->nextInvoiceNumber(),
            'gst_breakup_json' => ['cgst' => $cgst, 'sgst' => $sgst, 'rate' => self::GST_RATE],
            'hsn_summary_json' => $hsnSummary,
        ]);

        $path = $this->renderPdf($invoice, $order);
        $invoice->update(['pdf_path' => $path]);

        return $invoice;
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $seq = Invoice::whereYear('created_at', now()->year)->count() + 1;

        return sprintf('GE/%s/%05d', $year, $seq);
    }

    private function renderPdf(Invoice $invoice, Order $order): string
    {
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'order' => $order]);
        $path = "invoices/{$invoice->invoice_no}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}
