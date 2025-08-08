<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HostController extends Controller
{
    /**
     * Get all properties for the authenticated host
     */
    public function getProperties(Request $request)
    {
        $properties = Property::where('user_id', Auth::id())
            ->with(['user.profile', 'bookings'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $properties,
            'message' => 'Properties retrieved successfully'
        ]);
    }

    /**
     * Create a new property
     */
    public function createProperty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type' => 'required|string|in:apartment,house,villa,room',
            'price_per_night' => 'required|numeric|min:0',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'number_of_guests' => 'required|integer|min:1',
            'number_of_bedrooms' => 'required|integer|min:1',
            'number_of_beds' => 'required|integer|min:1',
            'number_of_bathrooms' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $property = Property::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'property_type' => $request->property_type,
            'price_per_night' => $request->price_per_night,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'number_of_guests' => $request->number_of_guests,
            'number_of_bedrooms' => $request->number_of_bedrooms,
            'number_of_beds' => $request->number_of_beds,
            'number_of_bathrooms' => $request->number_of_bathrooms,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => $property,
            'message' => 'Property created successfully'
        ], 201);
    }

    /**
     * Get a specific property
     */
    public function getProperty($id)
    {
        $property = Property::where('user_id', Auth::id())
            ->with(['user.profile', 'bookings'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $property,
            'message' => 'Property retrieved successfully'
        ]);
    }

    /**
     * Update a property
     */
    public function updateProperty(Request $request, $id)
    {
        $property = Property::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'property_type' => 'sometimes|required|string|in:apartment,house,villa,room',
            'price_per_night' => 'sometimes|required|numeric|min:0',
            'address' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'number_of_guests' => 'sometimes|required|integer|min:1',
            'number_of_bedrooms' => 'sometimes|required|integer|min:1',
            'number_of_beds' => 'sometimes|required|integer|min:1',
            'number_of_bathrooms' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $property->update($request->only([
            'title', 'description', 'property_type', 'price_per_night',
            'address', 'latitude', 'longitude', 'number_of_guests',
            'number_of_bedrooms', 'number_of_beds', 'number_of_bathrooms'
        ]));

        return response()->json([
            'success' => true,
            'data' => $property,
            'message' => 'Property updated successfully'
        ]);
    }

    /**
     * Delete a property
     */
    public function deleteProperty($id)
    {
        $property = Property::where('user_id', Auth::id())->findOrFail($id);
        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully'
        ]);
    }

    /**
     * Get all bookings for the host's properties
     */
    public function getBookings(Request $request)
    {
        $bookings = Booking::whereHas('bookable', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['bookable', 'user.profile'])
        ->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'message' => 'Bookings retrieved successfully'
        ]);
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(Request $request, $id)
    {
        $booking = Booking::whereHas('bookable', function($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,confirmed,cancelled,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => $booking,
            'message' => 'Booking status updated successfully'
        ]);
    }

    /**
     * Get all experiences for the host
     */
    public function getExperiences(Request $request)
    {
        $experiences = Experience::where('user_id', Auth::id())
            ->with(['user.profile', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $experiences,
            'message' => 'Experiences retrieved successfully'
        ]);
    }

    /**
     * Create a new experience
     */
    public function createExperience(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'max_participants' => 'required|integer|min:1',
            'location' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $experience = Experience::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'duration' => $request->duration,
            'max_participants' => $request->max_participants,
            'location' => $request->location,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => $experience,
            'message' => 'Experience created successfully'
        ], 201);
    }

    /**
     * Update an experience
     */
    public function updateExperience(Request $request, $id)
    {
        $experience = Experience::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration' => 'sometimes|required|integer|min:1',
            'max_participants' => 'sometimes|required|integer|min:1',
            'location' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $experience->update($request->only([
            'title', 'description', 'price', 'duration', 'max_participants', 'location'
        ]));

        return response()->json([
            'success' => true,
            'data' => $experience,
            'message' => 'Experience updated successfully'
        ]);
    }

    /**
     * Delete an experience
     */
    public function deleteExperience($id)
    {
        $experience = Experience::where('user_id', Auth::id())->findOrFail($id);
        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully'
        ]);
    }

    /**
     * Get host earnings
     */
    public function getEarnings(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $earnings = Booking::whereHas('bookable', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('status', 'completed')
        ->sum('total_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $earnings,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ],
            'message' => 'Earnings retrieved successfully'
        ]);
    }

    /**
     * Get host statistics
     */
    public function getStatistics()
    {
        $totalProperties = Property::where('user_id', Auth::id())->count();
        $totalExperiences = Experience::where('user_id', Auth::id())->count();
        $totalBookings = Booking::whereHas('bookable', function($query) {
            $query->where('user_id', Auth::id());
        })->count();
        $completedBookings = Booking::whereHas('bookable', function($query) {
            $query->where('user_id', Auth::id());
        })->where('status', 'completed')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_properties' => $totalProperties,
                'total_experiences' => $totalExperiences,
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'completion_rate' => $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100, 2) : 0
            ],
            'message' => 'Statistics retrieved successfully'
        ]);
    }
}
