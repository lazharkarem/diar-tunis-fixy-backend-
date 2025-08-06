<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Create a new booking (Guest)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'check_in_date' => 'required|date|after:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = Property::findOrFail($request->property_id);

            // Check if property accepts the number of guests
            if ($request->number_of_guests > $property->number_of_guests) {
                return response()->json([
                    'success' => false,
                    'message' => 'Number of guests exceeds property capacity'
                ], 422);
            }

            // Check availability (simplified - in real app, check for conflicting bookings)
            $conflictingBooking = Booking::where('property_id', $request->property_id)
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($request) {
                    $query->whereBetween('check_in_date', [$request->check_in_date, $request->check_out_date])
                          ->orWhereBetween('check_out_date', [$request->check_in_date, $request->check_out_date])
                          ->orWhere(function($q) use ($request) {
                              $q->where('check_in_date', '<=', $request->check_in_date)
                                ->where('check_out_date', '>=', $request->check_out_date);
                          });
                })
                ->exists();

            if ($conflictingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property is not available for the selected dates'
                ], 422);
            }

            // Calculate total amount
            $checkIn = \Carbon\Carbon::parse($request->check_in_date);
            $checkOut = \Carbon\Carbon::parse($request->check_out_date);
            $nights = $checkIn->diffInDays($checkOut);
            $totalAmount = $nights * $property->price_per_night;

            $booking = Booking::create([
                'guest_id' => $request->user()->id,
                'property_id' => $request->property_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'number_of_guests' => $request->number_of_guests,
                'number_of_nights' => $nights,
                'price_per_night' => $property->price_per_night,
                'total_amount' => $totalAmount,
                'special_requests' => $request->special_requests,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking->load(['property', 'guest'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get guest's bookings
     */
    public function guestBookings(Request $request): JsonResponse
    {
        try {
            $bookings = Booking::with(['property', 'property.images'])
                ->where('guest_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get host's bookings
     */
    public function hostBookings(Request $request): JsonResponse
    {
        try {
            $bookings = Booking::with(['guest', 'property'])
                ->whereHas('property', function($query) use ($request) {
                    $query->where('host_id', $request->user()->id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch host bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get host earnings
     */
    public function hostEarnings(Request $request): JsonResponse
    {
        try {
            $hostId = $request->user()->id;

            $earnings = [
                'total_earnings' => Booking::whereHas('property', function($query) use ($hostId) {
                    $query->where('host_id', $hostId);
                })->where('status', 'completed')->sum('total_amount'),
                
                'this_month' => Booking::whereHas('property', function($query) use ($hostId) {
                    $query->where('host_id', $hostId);
                })->where('status', 'completed')
                  ->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year)
                  ->sum('total_amount'),

                'last_month' => Booking::whereHas('property', function($query) use ($hostId) {
                    $query->where('host_id', $hostId);
                })->where('status', 'completed')
                  ->whereMonth('created_at', now()->subMonth()->month)
                  ->whereYear('created_at', now()->subMonth()->year)
                  ->sum('total_amount'),

                'total_bookings' => Booking::whereHas('property', function($query) use ($hostId) {
                    $query->where('host_id', $hostId);
                })->count(),

                'completed_bookings' => Booking::whereHas('property', function($query) use ($hostId) {
                    $query->where('host_id', $hostId);
                })->where('status', 'completed')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $earnings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch earnings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single booking
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $booking = Booking::with(['property', 'property.images', 'guest'])
                ->where('guest_id', $request->user()->id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Cancel booking
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $booking = Booking::where('guest_id', $request->user()->id)
                ->findOrFail($id);

            if ($booking->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is already cancelled'
                ], 422);
            }

            if ($booking->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel completed booking'
                ], 422);
            }

            $booking->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking or booking not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
