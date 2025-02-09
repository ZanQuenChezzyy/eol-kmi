<?php

namespace App\Console\Commands;

use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifyProductExpiration extends Command
{
    protected $signature = 'notify:product-expiration';
    protected $description = 'Send notification for product expiration based on notified_at settings';

    public function handle()
    {
        $products = Product::reminderNotification()->get();

        foreach ($products as $product) {
            if ($product->expired_at) {
                Notification::make()
                    ->title("Pengingat Kedaluwarsa Produk")
                    ->body("Produk {$product->name} akan expired pada {$product->expired_at->format('d-m-Y')}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title("Produk Tidak Valid")
                    ->body("Produk {$product->name} tidak memiliki tanggal expired yang valid.")
                    ->warning()
                    ->send();
            }
        }

        return Command::SUCCESS;
    }
}
