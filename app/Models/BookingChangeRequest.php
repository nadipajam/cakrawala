<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingChangeRequest extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'request_type',
        'reason',
        'preferred_flight_id',
        'status',
        'admin_notes',
        'resolution_amount',
        'resolution_details',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'resolution_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preferredFlight(): BelongsTo
    {
        return $this->belongsTo(Flight::class, 'preferred_flight_id');
    }

    public function processedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
