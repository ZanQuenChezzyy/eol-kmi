<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manufactur_id',
        'category_id',
        'sub_category_id',
        'lisence_number',
        'duration',
        'installed_at',
        'expired_at',
        'notified_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function scopeReminderNotification($query)
    {
        $now = now();

        return $query->whereNotNull('expired_at')->where(function ($q) use ($now) {
            $q->when(
                $this->notified_at === 0,
                fn($subQuery) => $subQuery->whereDate('expired_at', $now->copy()->addDay()->toDateString())
            )->when(
                    $this->notified_at === 1,
                    fn($subQuery) => $subQuery->whereDate('expired_at', $now->copy()->addWeek()->toDateString())
                )->when(
                    $this->notified_at === 2,
                    fn($subQuery) => $subQuery->whereDate('expired_at', $now->copy()->addMonth()->toDateString())
                );
        });
    }

    public function Manufactur(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Manufactur::class, 'manufactur_id', 'id');
    }
    public function Category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id', 'id');
    }
    public function SubCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SubCategory::class, 'sub_category_id', 'id');
    }

}
