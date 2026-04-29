<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airplane extends Model
{
    protected $fillable = [
        'airline_id',
        'model',
        'registration_number',
        'capacity',
        'description',
        'photo',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }
}
