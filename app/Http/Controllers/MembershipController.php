<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\MembershipResource;
use App\Models\Membership;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class MembershipController extends Controller
{
    public function index(PaginationRequest $request, MembershipService $service): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Membership::class);

        return MembershipResource::collection(
            $service->listForUser($request->user(), $request->pageSize(), $request->pageNumber()),
        );
    }

    public function show(Request $request, string $membership, MembershipService $service): MembershipResource
    {
        $record = $service->findForUser($request->user(), (int) $membership);
        Gate::authorize('view', $record);

        return new MembershipResource($record);
    }
}
