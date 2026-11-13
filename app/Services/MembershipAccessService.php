<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\User;
use Carbon\CarbonImmutable;

class MembershipAccessService
{
    public function accessEndsAt(User $user, Membership $membership, CarbonImmutable $moment): ?CarbonImmutable
    {
        if ($membership->user_id !== $user->id || $membership->statusAt($moment) !== 'active') {
            return null;
        }

        $gym = $membership->membershipPlan->gym;
        $localMoment = $moment->setTimezone($gym->timezone);

        if (! $gym->is_active || ! in_array($localMoment->dayOfWeekIso, $membership->allowed_days, true)) {
            return null;
        }

        $hours = $gym->openingHours()->where('day_of_week', $localMoment->dayOfWeekIso)->first();

        if ($hours === null || $hours->opens_at === null || $hours->closes_at === null) {
            return null;
        }

        $opensAt = $localMoment->setTimeFromTimeString($hours->opens_at);
        $closesAt = $localMoment->setTimeFromTimeString($hours->closes_at);

        if ($membership->access_ends_at !== null) {
            $closesAt = $closesAt->min($localMoment->setTimeFromTimeString($membership->access_ends_at));
        }

        $closesAt = $closesAt->min($membership->ends_at);

        if ($membership->cancelled_at !== null) {
            $closesAt = $closesAt->min($membership->cancelled_at);
        }

        if ($localMoment->lessThan($opensAt) || $localMoment->greaterThanOrEqualTo($closesAt)) {
            return null;
        }

        return $closesAt->utc();
    }

    public function allowsAccessAt(User $user, Membership $membership, CarbonImmutable $moment): bool
    {
        return $this->accessEndsAt($user, $membership, $moment) !== null;
    }

    public function allowsInterval(User $user, Membership $membership, CarbonImmutable $startsAt, CarbonImmutable $endsAt): bool
    {
        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            return false;
        }

        $accessEndsAt = $this->accessEndsAt($user, $membership, $startsAt);

        return $accessEndsAt !== null && $endsAt->lessThanOrEqualTo($accessEndsAt);
    }
}
