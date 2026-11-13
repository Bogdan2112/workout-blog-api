<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'plan_name', 'price_amount', 'currency', 'duration_days', 'allowed_days',
        'access_ends_at', 'starts_at', 'ends_at', 'cancelled_at', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'price_amount' => 'integer',
            'duration_days' => 'integer',
            'allowed_days' => 'array',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'is_demo' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function statusAt(CarbonInterface $moment): string
    {
        if ($this->cancelled_at !== null && $moment->greaterThanOrEqualTo($this->cancelled_at)) {
            return 'cancelled';
        }

        if ($moment->lessThan($this->starts_at)) {
            return 'scheduled';
        }

        return $moment->lessThan($this->ends_at) ? 'active' : 'expired';
    }
}
