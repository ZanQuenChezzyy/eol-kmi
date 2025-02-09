<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductExpirationNotification extends Notification
{
    use Queueable;

    protected $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => "Pengingat Kedaluwarsa Produk",
            'body' => "Produk {$this->product->name} akan kedaluwarsa pada {$this->product->expired_at->format('d-m-Y')}.",
            'product_id' => $this->product->id,
        ];
    }
}
