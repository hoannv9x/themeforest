<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyTypeRequest;
use App\Http\Resources\PropertyTypeResource;
use App\Models\PropertyType;
use Illuminate\Http\Response;

class AdminPropertyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = PropertyType::withCount('properties')
            ->orderBy('name')
            ->get();

        return PropertyTypeResource::collection($types);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyTypeRequest $request)
    {
        $propertyType = PropertyType::create($request->validated());

        return new PropertyTypeResource($propertyType);
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertyType $propertyType)
    {
        return new PropertyTypeResource($propertyType->loadCount('properties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PropertyTypeRequest $request, PropertyType $propertyType)
    {
        $propertyType->update($request->validated());

        return new PropertyTypeResource($propertyType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyType $propertyType)
    {
        $propertyType->delete();

        return response()->noContent();
    }
}
