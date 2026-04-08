<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistrictRequest;
use App\Http\Resources\DistrictResource;
use App\Models\District;
use Illuminate\Http\Response;

class AdminDistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $districts = District::with('city')
            ->withCount('properties')
            ->orderBy('name')
            ->paginate(20);

        return DistrictResource::collection($districts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DistrictRequest $request)
    {
        $district = District::create($request->validated());

        return new DistrictResource($district);
    }

    /**
     * Display the specified resource.
     */
    public function show(District $district)
    {
        return new DistrictResource($district->load('city')->loadCount('properties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DistrictRequest $request, District $district)
    {
        $district->update($request->validated());

        return new DistrictResource($district);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(District $district)
    {
        $district->delete();

        return response()->noContent();
    }
}
