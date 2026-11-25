<?php

namespace App\Services;

use App\Models\TrainingSession;
use App\Models\User;
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
}
