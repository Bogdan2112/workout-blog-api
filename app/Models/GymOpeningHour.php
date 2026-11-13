<?php

namespace App\Models;

use Database\Factories\GymOpeningHourFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymOpeningHour extends Model
{
    /** @use HasFactory<GymOpeningHourFactory> */
    use HasFactory;

    protected $fillable = ['day_of_week', 'opens_at', 'closes_at'];

    protected function casts(): array
    {
        return ['day_of_week' => 'integer'];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
