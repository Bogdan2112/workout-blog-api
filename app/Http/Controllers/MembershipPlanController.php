<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\MembershipPlanResource;
use App\Models\Gym;
use App\Models\MembershipPlan;
use App\Services\MembershipPlanService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class MembershipPlanController extends Controller
{
    public function index(PaginationRequest $request, Gym $gym, MembershipPlanService $service): AnonymousResourceCollection
    {
        Gate::authorize('view', $gym);

        return MembershipPlanResource::collection(
            $service->listActivePlans($gym, $request->pageSize(), $request->pageNumber()),
        );
    }

    public function show(Gym $gym, MembershipPlan $membershipPlan, MembershipPlanService $service): MembershipPlanResource
    {
        Gate::authorize('view', $gym);
        Gate::authorize('view', $membershipPlan);

        return new MembershipPlanResource($service->findActivePlan($gym, $membershipPlan->id));
    }
}
