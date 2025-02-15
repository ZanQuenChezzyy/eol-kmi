<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductExpiredChart extends ChartWidget
{
    protected static ?int $sort = 5;
    protected static ?string $heading = 'Produk Berdasarkan Masa Berlaku (Expired vs Aktif)';

    protected function getData(): array
    {
        $now = now();

        // Hitung jumlah produk yang sudah expired
        $expiredCount = Product::whereNotNull('expired_at')
            ->where('expired_at', '<', $now)
            ->count();

        // Hitung jumlah produk yang masih aktif
        $activeCount = Product::whereNotNull('expired_at')
            ->where('expired_at', '>=', $now)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Produk',
                    'data' => [$activeCount, $expiredCount],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.5)',  // Hijau untuk aktif
                        'rgba(239, 68, 68, 0.5)',  // Merah untuk expired
                    ],
                    'borderColor' => 'rgba(0, 0, 0, 0)',
                    'borderRadius' => 20,
                ],
            ],
            'labels' => ['Aktif', 'Expired'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
