<?php

namespace App\Models;

use Database\Factories\GymFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gym extends Model
{
    /** @use HasFactory<GymFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'city',
        'address',
        'timezone',
        'contact_email',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GymPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(GymOpeningHour::class)->orderBy('day_of_week');
    }

    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function inCity(Builder $query, string $city): void
    {
        $query->where('city', $city);
    }
}
