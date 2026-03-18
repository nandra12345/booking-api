<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'start_time',
        'end_time',
        'slot_capacity',
    ];

    protected $casts = [
        'start_time'    => 'datetime',
        'end_time'      => 'datetime',
        'slot_capacity' => 'integer',
    ];

    // relasi ke bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // hitung jumlah booking yang masih aktif (belum dibatalkan)
    public function activeBookingsCount(): int
    {
        return $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    // cek apakah slot masih tersedia
    public function hasAvailableSlots(): bool
    {
        return $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->count() < $this->slot_capacity;
    }
}