<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminPropertyController extends Controller
{
    protected $propertyRepository;

    public function __construct(PropertyRepositoryInterface $propertyRepository)
    {
        $this->propertyRepository = $propertyRepository;
    }

    public function index(Request $request)
    {
        // Admin can view all properties, including pending ones
        $filters = $request->only(['status', 'city_id', 'property_type_id']);
        $properties = $this->propertyRepository->all($filters, [], 20); // Adjust per_page as needed
        return PropertyResource::collection($properties);
    }

    public function show(string $id)
    {
        $property = $this->propertyRepository->find($id);
        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }
        return new PropertyResource($property);
    }

    public function update(Request $request, string $id)
    {
        $property = $this->propertyRepository->find($id);
        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:pending,active,sold,rented,inactive'],
            'is_featured' => ['sometimes', 'boolean'],
            // ... other fields that admin can update
        ]);

        $property = $this->propertyRepository->update($id, $validatedData);
        return new PropertyResource($property);
    }

    public function approve(string $id)
    {
        $property = $this->propertyRepository->find($id);
        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        $property = $this->propertyRepository->update($id, ['status' => 'active', 'published_at' => now()]);
        return new PropertyResource($property);
    }

    public function destroy(string $id)
    {
        $property = $this->propertyRepository->find($id);
        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        $this->propertyRepository->delete($id);
        return response()->json(['message' => 'Property deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}