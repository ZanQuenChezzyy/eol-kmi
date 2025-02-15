<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProductCategoryChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Jumlah Produk per Kategori';

    protected function getData(): array
    {
        // Ambil kategori dan ID-nya
        $categories = Category::pluck('name', 'id')->toArray();

        // Hitung jumlah produk per kategori
        $productCounts = Product::select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id')
            ->toArray();

        // Pastikan kategori yang tidak memiliki produk tetap muncul dengan nilai 0
        $chartData = [];
        foreach ($categories as $id => $name) {
            $chartData[] = $productCounts[$id] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Produk per Kategori',
                    'data' => $chartData,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',  // Biru
                        'rgba(239, 68, 68, 0.5)',   // Merah
                        'rgba(249, 115, 22, 0.5)',  // Oranye
                        'rgba(34, 197, 94, 0.5)',   // Hijau
                        'rgba(168, 85, 247, 0.5)',  // Ungu
                        'rgba(236, 72, 153, 0.5)',  // Pink
                        'rgba(139, 92, 246, 0.5)',  // Indigo
                        'rgba(52, 211, 153, 0.5)',  // Hijau Muda
                        'rgba(251, 191, 36, 0.5)',  // Kuning
                        'rgba(163, 163, 163, 0.5)', // Abu-abu
                        'rgba(255, 99, 132, 0.5)',  // Merah Muda
                        'rgba(75, 192, 192, 0.5)',  // Cyan
                        'rgba(153, 102, 255, 0.5)', // Ungu Terang
                        'rgba(255, 159, 64, 0.5)',  // Oranye Muda
                        'rgba(201, 203, 207, 0.5)', // Abu-abu Muda
                    ],
                    'borderColor' => 'rgba(0, 0, 0, 0)',
                    'borderRadius' => 20,

                ],
            ],
            'labels' => array_values($categories),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
