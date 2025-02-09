<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        // Cek produk yang mendekati expired
        $products = Product::where('expired_at', '<=', now()->addDays(7))->get();
        $recipient = Auth::user();
        foreach ($products as $product) {
            Notification::make()
                ->title("Pengingat Kedaluwarsa Produk")
                ->body("Produk {$product->name} akan kedaluwarsa pada {$product->expired_at->TranslatedFormat('d F Y')}.")
                ->warning()
                ->send()
                ->sendToDatabase($recipient);
        }
    }
}
