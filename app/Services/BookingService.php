<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private MembershipAccessService $membershipAccessService) {}

    private function ensureMembershipAllowsSession(
        User $user,
        TrainingSession $trainingSession,
        int $membershipId,
    ): void {
        $membership = $user->memberships()
            ->with('membershipPlan.gym')
            ->findOrFail($membershipId);

        if (
            $membership->membershipPlan->gym_id !== $trainingSession->gym_id
            || ! $this->membershipAccessService->allowsInterval(
                $user,
                $membership,
                $trainingSession->starts_at,
                $trainingSession->ends_at,
            )
        ) {
            throw ValidationException::withMessages([
                'membership_id' => 'Abonamentul nu permite accesul la această ședință.',
            ]);
        }

    }

    private function ensureSessionIsBookable(
        TrainingSession $trainingSession,
    ): void {
        $now = CarbonImmutable::now('UTC');

        if (
            $trainingSession->cancelled_at !== null
            || $trainingSession->starts_at->lessThanOrEqualTo($now)
        ) {
            throw ValidationException::withMessages([
                'training_session' => 'Ședința este anulată sau a început deja',
            ]);
        }
    }

    private function ensurePlaceIsAvailable(
        User $user,
        TrainingSession $trainingSession
    ): void {
        if ($trainingSession->bookings()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'training_session' => 'Ai deja o rezervare la aceasta sedinta',
            ]);
        }

        if ($trainingSession->capacity !== null
            && $trainingSession->bookings()->count() >= $trainingSession->capacity
        ) {
            throw ValidationException::withMessages([
                'training_session' => 'Nu mai sunt locuri disponibile',
            ]);
        }
    }

    public function create(
        User $user,
        TrainingSession $trainingSession,
        int $membershipId,
    ): Booking {
        return DB::transaction(function () use (
            $user,
            $trainingSession,
            $membershipId,
        ): Booking {
            $lockedSession = TrainingSession::query()
                ->lockForUpdate()
                ->findOrFail($trainingSession->id);

            $this->ensureSessionIsBookable($lockedSession);

            $this->ensureMembershipAllowsSession(
                $user,
                $lockedSession,
                $membershipId
            );

            $this->ensurePlaceIsAvailable($user, $lockedSession);

            $booking = new Booking;
            $booking->user()->associate($user);
            $booking->trainingSession()->associate($lockedSession);
            $booking->save();

            return $booking;
        });
    }
}
