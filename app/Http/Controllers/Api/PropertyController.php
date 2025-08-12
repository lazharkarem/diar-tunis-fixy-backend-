<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties
     */
    public function index(Request $request)
    {
        $query = Property::with(['user.profile'])
            ->where('status', 'active');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('property_type', $request->get('type'));
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->get('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->get('max_price'));
        }

        if ($request->filled('guests')) {
            $query->where('number_of_guests', '>=', $request->get('guests'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $properties = $query->orderBy('created_at', 'desc')
                           ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $properties,
            'message' => 'Properties retrieved successfully'
        ]);
    }

    /**
     * Display the specified property
     */
    public function show($id)
    {
        try {
            $property = Property::with([
                'user.profile',
                'bookings' => function($query) {
                    $query->where('status', '!=', 'cancelled')
                          ->select(['id', 'property_id', 'check_in_date', 'check_out_date']);
                }
            ])->where('status', 'active')->findOrFail($id);

            $propertyData = [
                'id' => $property->id,
                'title' => $property->title,
                'description' => $property->description,
                'type' => $property->property_type,
                'price_per_night' => (float) $property->price_per_night,
                'location' => [
                    'address' => $property->address,
                    'coordinates' => [
                        'latitude' => (float) $property->latitude,
                        'longitude' => (float) $property->longitude,
                    ],
                ],
                'capacity' => [
                    'guests' => $property->number_of_guests,
                    'bedrooms' => $property->number_of_bedrooms,
                    'beds' => $property->number_of_beds,
                    'bathrooms' => $property->number_of_bathrooms,
                ],
                'amenities' => [], // Simplified for now to avoid relationship issues
                'images' => [], // Simplified for now to avoid relationship issues
                'host' => [
                    'id' => $property->user->id,
                    'name' => $property->user->name,
                    'avatar' => isset($property->user->profile) && $property->user->profile->avatar ? $property->user->profile->avatar : '/default-avatar.png',
                    'joined_date' => $property->user->created_at->format('Y-m-d'),
                ],
                'reviews' => [], // Simplified for now to avoid relationship issues
                'availability' => $this->getAvailability($property),
                'rating' => 4.5, // Static for now
                'created_at' => $property->created_at->format('Y-m-d'),
            ];

            return response()->json([
                'success' => true,
                'data' => $propertyData,
                'message' => 'Property retrieved successfully'
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
     * Get property availability (basic implementation)
     */
    private function getAvailability($property)
    {
        // This is a simplified availability check using correct column names
        $bookedDates = [];
        
        if ($property->bookings) {
            foreach ($property->bookings as $booking) {
                $bookedDates[] = [
                    'start_date' => $booking->check_in_date,
                    'end_date' => $booking->check_out_date,
                ];
            }
        }

        return [
            'is_available' => true,
            'booked_dates' => $bookedDates,
        ];
    }
}