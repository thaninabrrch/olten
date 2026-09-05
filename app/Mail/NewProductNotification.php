<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewProductNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Un nouveau produit correspond à vos préférences',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-product-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}