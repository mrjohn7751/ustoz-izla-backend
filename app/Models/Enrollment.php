<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'elon_id',
        'ustoz_id',
        'status',
        'enrolled_at',
        'completed_at',
        'cancelled_at',
        'can_rate',
        'can_rate_from',
        'has_rated',
        'rated_at',
        'notes',
        'paid_amount',
        'payment_status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'can_rate_from' => 'datetime',
        'rated_at' => 'datetime',
        'can_rate' => 'boolean',
        'has_rated' => 'boolean',
        'paid_amount' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function elon()
    {
        return $this->belongsTo(Elon::class);
    }

    public function ustoz()
    {
        return $this->belongsTo(Ustoz::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCanRate($query)
    {
        return $query->where('can_rate', true)
                     ->where('has_rated', false);
    }

    public function scopeHasRated($query)
    {
        return $query->where('has_rated', true);
    }

    // Helper Methods

    /**
     * Kursga yozilgandan 1 oy o'tganini tekshirish
     */
    public function checkIfCanRate()
    {
        if ($this->has_rated) {
            return false;
        }

        $oneMonthAgo = Carbon::now()->subMonth();

        if ($this->enrolled_at <= $oneMonthAgo) {
            if (!$this->can_rate) {
                $this->update([
                    'can_rate' => true,
                    'can_rate_from' => $oneMonthAgo,
                ]);
            }
            return true;
        }

        return false;
    }

    /**
     * Baholash imkoniyati qachon ochilishini hisoblash
     */
    public function getRatingAvailableDateAttribute()
    {
        return $this->enrolled_at->copy()->addMonth();
    }

    /**
     * Baholash uchun qancha kun qolganini hisoblash
     */
    public function getDaysUntilRatingAttribute()
    {
        if ($this->can_rate) {
            return 0;
        }

        $ratingDate = $this->rating_available_date;
        $now = Carbon::now();

        if ($now >= $ratingDate) {
            return 0;
        }

        return $now->diffInDays($ratingDate);
    }

    /**
     * Baholash tugmasini ko'rsatish kerakmi
     */
    public function getCanShowRatingButtonAttribute()
    {
        return $this->can_rate && !$this->has_rated && $this->status === 'active';
    }

    /**
     * Kurs davomiyligi (kunlarda)
     */
    public function getDurationInDaysAttribute()
    {
        if ($this->completed_at) {
            return $this->enrolled_at->diffInDays($this->completed_at);
        }

        return $this->enrolled_at->diffInDays(Carbon::now());
    }

    /**
     * Kurs holatini o'zgartirish
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);
    }

    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
        ]);
    }

    /**
     * Baholash amalga oshirilganini belgilash
     */
    public function markAsRated()
    {
        $this->update([
            'has_rated' => true,
            'rated_at' => Carbon::now(),
        ]);
    }

    /**
     * To'lov holatini yangilash
     */
    public function markAsPaid($amount = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_amount' => $amount ?? $this->paid_amount,
        ]);
    }
}
