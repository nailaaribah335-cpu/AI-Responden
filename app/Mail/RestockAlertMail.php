<?php

namespace App\Mail;

use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RestockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inventory $inventory,
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Peringatan Restock: {$this->inventory->nama_produk}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.restock-alert',
        );
    }
}
