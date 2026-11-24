<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTrainingSessionsRequest;
use App\Http\Resources\TrainingSessionResource;
use App\Models\Gym;
use App\Models\TrainingSession;
use App\Services\TrainingSessionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class TrainingSessionController extends Controller
{
    #[Authorize('view', 'gym')]
    public function index(ListTrainingSessionsRequest $request, Gym $gym, TrainingSessionService $service): AnonymousResourceCollection
    {
        return TrainingSessionResource::collection(
            $service->listForDate($gym, $request->selectedDate(), $request->pageSize(), $request->pageNumber()),
        );
    }

    #[Authorize('view', 'gym')]
    public function show(Gym $gym, TrainingSession $trainingSession, TrainingSessionService $service): TrainingSessionResource
    {
        return new TrainingSessionResource($service->findForGym($gym, $trainingSession->id));
    }
}
