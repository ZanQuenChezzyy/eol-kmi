<?php

namespace App\Filament\Widgets;

use App\Models\Manufactur;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $expiredSoon = Product::whereBetween('expired_at', [now(), now()->addDays(30)])->count();
        $totalManufacturs = Manufactur::count();
        $expiredProducts = Product::where('expired_at', '<', now())->count();

        // Data untuk chart (7 hari terakhir)
        $last7Days = collect(range(6, 0))->map(fn($daysAgo) => now()->subDays($daysAgo)->toDateString());
        $productsLast7Days = $last7Days->map(fn($date) => Product::whereDate('created_at', $date)->count());
        $expiredLast7Days = $last7Days->map(fn($date) => Product::whereDate('expired_at', $date)->count());
        $manufactursLast7Days = $last7Days->map(fn($date) => Manufactur::whereDate('created_at', $date)->count());

        return [
            Stat::make('Total Produk Terdaftar', $totalProducts)
                ->description('Total produk yang terdaftar dalam sistem')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart($productsLast7Days->toArray())
                ->color('primary'),

            Stat::make('Produk Kedaluwarsa dalam 30 Hari', $expiredSoon)
                ->description($expiredSoon > 0 ? 'Produk yang akan kedaluwarsa dalam 30 hari ke depan' : 'Tidak Ada Produk Kedaluarsa dalam 30 hari ke depan')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([$expiredSoon, $expiredSoon * 0.8, $expiredSoon * 0.6, $expiredSoon * 0.4, $expiredSoon * 0.2, 0])
                ->color($expiredSoon > 0 ? 'warning' : 'success'),

            Stat::make('Total Manufaktur Terdaftar', $totalManufacturs)
                ->description('Jumlah manufaktur yang terdaftar dalam sistem')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->chart($manufactursLast7Days->toArray())
                ->color('info'),

            Stat::make('Produk yang Sudah Kedaluwarsa', $expiredProducts)
                ->description($expiredProducts > 0 ? 'Produk yang telah melewati tanggal kedaluwarsa' : 'Tidak Ada Produk Kedaluarsa untuk saat ini')
                ->descriptionIcon('heroicon-m-x-circle')
                ->chart($expiredLast7Days->toArray())
                ->color($expiredProducts > 0 ? 'danger' : 'success'),
        ];
    }
}
