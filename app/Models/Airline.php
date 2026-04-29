<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends Model
{
    protected $fillable = [
        'name',
        'code',
        'logo',
        'description',
    ];

    public function airplanes(): HasMany
    {
        return $this->hasMany(Airplane::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }
}
