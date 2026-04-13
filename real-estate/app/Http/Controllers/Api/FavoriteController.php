<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\FavoriteResource;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()->with('property')->paginate(15);
        return FavoriteResource::collection($favorites);
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
        ]);

        $user = $request->user();
        $propertyId = $request->property_id;

        if ($user->favorites()->where('property_id', $propertyId)->exists()) {
            return response()->json(['message' => 'Property already in favorites'], Response::HTTP_CONFLICT);
        }

        $favorite = $user->favorites()->create([
            'property_id' => $propertyId,
        ]);

        return response()->json(['message' => 'Property added to favorites', 'favorite' => $favorite], Response::HTTP_CREATED);
    }

    public function destroy(string $propertyId, Request $request)
    {
        $user = $request->user();

        $deleted = $user->favorites()->where('property_id', $propertyId)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Property not found in favorites'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['message' => 'Property removed from favorites'], Response::HTTP_NO_CONTENT);
    }
}
