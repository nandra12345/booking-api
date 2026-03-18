<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'schedule_id' => 'integer',
        'user_id'     => 'integer',
    ];

    // -------------------------------------------------------------------------
    // relationships
    // -------------------------------------------------------------------------

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // helpers
    // -------------------------------------------------------------------------
public function isCancelled(): bool
{
    return $this->status === 'cancelled';
}

public function cancel(): void
{
    $this->update([
        'status' => 'cancelled'
    ]);
}
}