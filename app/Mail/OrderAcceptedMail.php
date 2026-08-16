<?php

namespace App\Mail;

use App\Models\ProductSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public ProductSale $order;

    public function __construct(ProductSale $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Votre commande a été acceptée')->view('emails.orders.accepted');
    }
}