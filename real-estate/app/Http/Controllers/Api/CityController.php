<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cities = City::with(['districts'])
            ->orderBy('name')->get();

        return CityResource::collection($cities);
    }

    /**
     * Display the specified resource.
     */
    public function districts(City $city)
    {
        return new CityResource($city->districts);
    }
}
