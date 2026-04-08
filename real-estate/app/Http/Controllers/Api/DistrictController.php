<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $districts = District::query()
            ->when($request->city_id, fn($q) => $q->where('city_id', $request->city_id))
            ->with('city')
            ->orderBy('name')
            ->get();

        return DistrictResource::collection($districts);
    }

    /**
     * Display the specified resource.
     */
    public function show(District $district)
    {
        return new DistrictResource($district->load('city'));
    }
}
