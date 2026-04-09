<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyStoreRequest;
use App\Http\Requests\PropertyUpdateRequest;
use App\Http\Resources\PropertyResource;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PropertyController extends Controller
{
    protected $propertyRepository;

    public function __construct(PropertyRepositoryInterface $propertyRepository)
    {
        $this->propertyRepository = $propertyRepository;
        $this->middleware('auth:sanctum')->except(['index', 'show', 'search', 'related']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->except(['sort_by', 'sort_direction', 'per_page']);
        $sortBy = $request->input('sort_by', 'published_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $perPage = $request->input('per_page', 15);

        $properties = $this->propertyRepository->all($filters, [$sortBy => $sortDirection], $perPage);

        return PropertyResource::collection($properties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyStoreRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id; // Assign the authenticated user as owner
        $data['published_at'] = now(); // Set published date

        $property = $this->propertyRepository->create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public'); // Store in storage/app/public/properties
                $property->images()->create([
                    'image_path' => $path,
                    // 'caption' => $image->getClientOriginalName(), // Optional: use original name as caption
                ]);
            }
        }

        return new PropertyResource($property);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $property = $this->propertyRepository->findBySlugOrId($slug);

        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        // Increment views count
        $property->increment('views_count');

        return new PropertyResource($property);
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => ['required', 'string', 'min:3'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'property_type_id' => ['nullable', 'exists:property_types,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort_by' => ['nullable', 'string', 'in:price,published_at,views_count'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $keyword = $request->input('keyword');
        $filters = $request->only(['city_id', 'property_type_id', 'min_price', 'max_price']);
        $sortBy = [$request->input('sort_by', 'published_at') => $request->input('sort_direction', 'desc')];
        $perPage = $request->input('per_page', 15);

        $properties = $this->propertyRepository->search($keyword, $filters, $sortBy, $perPage);

        return PropertyResource::collection($properties);
    }

    public function related(string $slug)
    {
        $property = $this->propertyRepository->findBySlugOrId($slug);

        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        $related = $this->propertyRepository->getRelatedProperties($property);

        return PropertyResource::collection($related);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PropertyUpdateRequest $request, string $id)
    {
        $property = $this->propertyRepository->find($id);

        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        // Authorization check: Only owner or admin can update
        if ($request->user()->id !== $property->user_id && ! $request->user()->is_admin) { // Assuming an 'is_admin' flag on User model
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $property = $this->propertyRepository->update($id, $request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public'); // Store in storage/app/public/properties
                $property->images()->create([
                    'image_path' => $path,
                    // 'caption' => $image->getClientOriginalName(), // Optional: use original name as caption
                ]);
            }
        }

        return new PropertyResource($property);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $property = $this->propertyRepository->find($id);

        if (! $property) {
            return response()->json(['message' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        // Authorization check
        if ($request->user()->id !== $property->user_id && ! $request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $this->propertyRepository->delete($id);

        return response()->json(['message' => 'Property deleted successfully'], Response::HTTP_NO_CONTENT);
    }

    public function nearbyPlaces(string $id, Request $request, PlacesService $placesService)
    {
        $property = $this->propertyRepository->find($id);

        if (! $property || !$property->latitude || !$property->longitude) {
            return response()->json(['message' => 'Property or location not found'], Response::HTTP_NOT_FOUND);
        }

        $type = $request->get('type', 'restaurant'); // e.g., restaurant, school, park
        $places = $placesService->findNearbyPlaces($property->latitude, $property->longitude, $type);

        return response()->json($places);
    }
}
