<?php

namespace App\Mail;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAdNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Ad $ad;

    public function __construct(Ad $ad)
    {
        $this->ad = $ad;
    }

    public function build()
    {
        return $this
            ->subject('Une nouvelle annonce correspond à vos préférences')
            ->view('emails.new-ad-notification');
    }
}