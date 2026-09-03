<?php

namespace App\Notifications;

use App\Models\ProductSale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProductSale $sale
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🛒 Nouvelle commande sur Olten')
            ->greeting(
                'Bonjour ' . ($notifiable->firstname ?? $notifiable->name) . ','
            )
            ->view('emails.orders.new-order', [
                'sale' => $this->sale,
                'seller' => $notifiable,
            ]);
    }
}