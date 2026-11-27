<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Gym;
use App\Models\TrainingSession;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
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

    public function destroy(
        Request $request,
        string $booking,
        BookingService $service
    ): Response {
        $service->cancel($request->user(), (int) $booking);

        return response()->nocontent();
    }

    public function index(
        PaginationRequest $request,
        BookingService $service,
    ): AnonymousResourceCollection {
        return BookingResource::collection(
            $service->listForUser(
                $request->user(),
                $request->pageSize(),
                $request->pageNumber(),
            ),
        );
    }
}
