<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Client $client, public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Payment received — Gayatri Enterprises');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.payment-received');
    }
}
