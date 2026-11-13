<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\GymDetailResource;
use App\Http\Resources\GymResource;
use App\Models\Gym;
use App\Services\GymService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class GymController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PaginationRequest $request, GymService $gymService): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Gym::class);
        $gyms = $gymService->listActiveGyms($request->pageSize(), $request->pageNumber());

        return GymResource::collection($gyms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Gym $gym, GymService $gymService): GymDetailResource
    {
        Gate::authorize('view', $gym);

        return new GymDetailResource($gymService->details($gym));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
