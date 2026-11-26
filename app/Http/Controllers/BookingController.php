<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Gym;
use App\Models\TrainingSession;
use App\Services\BookingService;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class BookingController extends Controller
{
    #[Authorize('view', 'gym')]
    public function store(
        StoreBookingRequest $request,
        Gym $gym,
        TrainingSession $trainingSession,
        BookingService $service,
    ): BookingResource {
        $booking = $service->create(
            $request->user(),
            $trainingSession,
            (int) $request->validated('membership_id'),
        );

        return new BookingResource($booking);
    }
}
