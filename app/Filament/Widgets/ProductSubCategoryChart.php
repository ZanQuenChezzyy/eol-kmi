<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\SubCategory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProductSubCategoryChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Jumlah Produk per Sub Kategori';

    protected function getData(): array
    {
        // Ambil daftar sub-kategori dan ID-nya
        $subCategories = SubCategory::pluck('name', 'id')->toArray();

        // Hitung jumlah produk per sub-kategori
        $productCounts = Product::select('sub_category_id', DB::raw('COUNT(*) as total'))
            ->groupBy('sub_category_id')
            ->pluck('total', 'sub_category_id')
            ->toArray();

        // Pastikan setiap sub-kategori tetap muncul meskipun jumlahnya nol
        $chartData = [];
        foreach ($subCategories as $id => $name) {
            $chartData[] = $productCounts[$id] ?? 0;
        }

        // Warna latar belakang (RGBA dengan opacity 0.5) - 15 warna
        $backgroundColors = [
            'rgba(59, 130, 246, 0.5)',  // Biru
            'rgba(239, 68, 68, 0.5)',   // Merah
            'rgba(249, 115, 22, 0.5)',  // Orange
            'rgba(34, 197, 94, 0.5)',   // Hijau
            'rgba(168, 85, 247, 0.5)',  // Ungu
            'rgba(236, 72, 153, 0.5)',  // Pink
            'rgba(16, 185, 129, 0.5)',  // Teal
            'rgba(255, 159, 64, 0.5)',  // Oranye terang
            'rgba(75, 192, 192, 0.5)',  // Cyan
            'rgba(153, 102, 255, 0.5)', // Violet
            'rgba(255, 205, 86, 0.5)',  // Kuning
            'rgba(54, 162, 235, 0.5)',  // Biru muda
            'rgba(201, 203, 207, 0.5)', // Abu-abu
            'rgba(102, 51, 153, 0.5)',  // Ungu gelap
            'rgba(220, 20, 60, 0.5)',   // Merah tua
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Produk per Sub Kategori',
                    'data' => $chartData,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($chartData)), // Ambil warna sesuai jumlah data
                    'borderColor' => 'rgba(0, 0, 0, 0)', // Tidak ada border
                    'borderRadius' => 20, // Membuat sudut bar lebih lembut
                ],
            ],
            'labels' => array_values($subCategories), // Label kategori di X-Axis
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
