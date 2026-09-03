<?php

namespace App\Notifications;

use App\Models\DeliveryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeliveryRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DeliveryRequest $deliveryRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isProduct = !is_null($this->deliveryRequest->product_sale_id);

        return (new MailMessage)
            ->subject('🚚 Nouvelle demande de livraison sur Olten')
            ->greeting(
                'Bonjour ' . ($notifiable->firstname ?? $notifiable->name) . ','
            )
            ->view('emails.new-delivery-request', [
                'deliveryRequest' => $this->deliveryRequest,
                'owner' => $notifiable,
                'isProduct' => $isProduct,
            ]);
    }
}