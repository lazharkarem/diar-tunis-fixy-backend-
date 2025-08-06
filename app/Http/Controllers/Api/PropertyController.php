<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    /**
     * Get all properties (public)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Property::with(['host', 'images', 'amenities']);

            // Apply filters
            if ($request->has('location')) {
                $query->where('address', 'like', '%' . $request->location . '%');
            }

            if ($request->has('property_type')) {
                $query->where('property_type', $request->property_type);
            }

            if ($request->has('guests')) {
                $query->where('number_of_guests', '>=', $request->guests);
            }

            if ($request->has('min_price')) {
                $query->where('price_per_night', '>=', $request->min_price);
            }

            if ($request->has('max_price')) {
                $query->where('price_per_night', '<=', $request->max_price);
            }

            // Only show active properties for public view
            $query->where('status', 'active');

            $properties = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $properties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single property (public)
     */
    public function show($id): JsonResponse
    {
        try {
            $property = Property::with(['host', 'images', 'amenities'])
                ->where('status', 'active')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $property
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get host's properties
     */
    public function hostProperties(Request $request): JsonResponse
    {
        try {
            $properties = Property::with(['images', 'amenities'])
                ->where('host_id', $request->user()->id)
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $properties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch host properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new property
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'property_type' => 'required|string',
            'number_of_guests' => 'required|integer|min:1',
            'number_of_bedrooms' => 'required|integer|min:0',
            'number_of_beds' => 'required|integer|min:1',
            'number_of_bathrooms' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'amenities' => 'array',
            'amenities.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = Property::create([
                'host_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'property_type' => $request->property_type,
                'number_of_guests' => $request->number_of_guests,
                'number_of_bedrooms' => $request->number_of_bedrooms,
                'number_of_beds' => $request->number_of_beds,
                'number_of_bathrooms' => $request->number_of_bathrooms,
                'price_per_night' => $request->price_per_night,
                'status' => 'pending', // Default status, needs admin approval
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('property_images', 'public');
                    $property->images()->create([
                        'image_url' => $path,
                        'is_primary' => $property->images()->count() === 0, // First image is primary
                    ]);
                }
            }

            // Handle amenities
            if ($request->has('amenities')) {
                foreach ($request->amenities as $amenity) {
                    $property->amenities()->create([
                        'name' => $amenity,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'data' => $property->load(['images', 'amenities'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update property
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'address' => 'sometimes|string|max:500',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'property_type' => 'sometimes|string',
            'number_of_guests' => 'sometimes|integer|min:1',
            'number_of_bedrooms' => 'sometimes|integer|min:0',
            'number_of_beds' => 'sometimes|integer|min:1',
            'number_of_bathrooms' => 'sometimes|integer|min:1',
            'price_per_night' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = Property::where('host_id', $request->user()->id)
                ->findOrFail($id);

            $property->update($request->only([
                'title', 'description', 'address', 'latitude', 'longitude',
                'property_type', 'number_of_guests', 'number_of_bedrooms',
                'number_of_beds', 'number_of_bathrooms', 'price_per_night'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => $property->load(['images', 'amenities'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property or property not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Delete property
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $property = Property::where('host_id', $request->user()->id)
                ->findOrFail($id);

            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete property or property not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
