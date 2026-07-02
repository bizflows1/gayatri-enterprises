<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $newStatus)
    {
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->newStatus) {
            'packed'    => "Your order #{$this->order->id} is being packed",
            'dispatched'=> "Your order #{$this->order->id} has been dispatched",
            'delivered' => "Your order #{$this->order->id} has been delivered",
            'cancelled' => "Your order #{$this->order->id} has been cancelled",
            default     => "Update on your order #{$this->order->id}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order-status-updated');
    }
}
