<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingDetail extends Model
{
    protected $fillable = [
        'booking_id',
        'passenger_id',
        'seat_id',
        'price',
        'ticket_number',
        'boarding_status',
        'checked_in_at',
        'boarded_at',
        'checkin_reference',
        'boarding_group',
        'gate_number',
        'boarding_pass_pdf_path',
        'boarding_pass_qr_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'checked_in_at' => 'datetime',
            'boarded_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }
}
