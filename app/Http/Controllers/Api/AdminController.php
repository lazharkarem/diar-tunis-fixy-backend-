<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Get all users
     */
    public function getAllUsers(Request $request): JsonResponse
    {
        try {
            $query = User::with(['profile']);

            if ($request->has('user_type')) {
                $query->where('user_type', $request->user_type);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            $users = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all properties
     */
    public function getAllProperties(Request $request): JsonResponse
    {
        try {
            $query = Property::with(['host', 'images', 'amenities']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('host_id')) {
                $query->where('host_id', $request->host_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

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
     * Update property status
     */
    public function updatePropertyStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,active,inactive,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = Property::findOrFail($id);
            $property->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Property status updated successfully',
                'data' => $property
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property status',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all bookings
     */
    public function getAllBookings(Request $request): JsonResponse
    {
        try {
            $query = Booking::with(['guest', 'property', 'property.host']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('guest_id')) {
                $query->where('guest_id', $request->guest_id);
            }

            if ($request->has('property_id')) {
                $query->where('property_id', $request->property_id);
            }

            if ($request->has('from_date')) {
                $query->where('check_in_date', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('check_out_date', '<=', $request->to_date);
            }

            $bookings = $query->orderBy('created_at', 'desc')->paginate(20);

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
     * Get dashboard statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'guests' => User::where('user_type', 'guest')->count(),
                    'hosts' => User::where('user_type', 'host')->count(),
                    'service_providers' => User::where('user_type', 'service_provider')->count(),
                    'new_this_month' => User::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)->count(),
                ],
                'properties' => [
                    'total' => Property::count(),
                    'active' => Property::where('status', 'active')->count(),
                    'pending' => Property::where('status', 'pending')->count(),
                    'inactive' => Property::where('status', 'inactive')->count(),
                    'rejected' => Property::where('status', 'rejected')->count(),
                ],
                'bookings' => [
                    'total' => Booking::count(),
                    'confirmed' => Booking::where('status', 'confirmed')->count(),
                    'pending' => Booking::where('status', 'pending')->count(),
                    'cancelled' => Booking::where('status', 'cancelled')->count(),
                    'completed' => Booking::where('status', 'completed')->count(),
                    'this_month' => Booking::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)->count(),
                ],
                'revenue' => [
                    'total' => Booking::where('status', 'completed')->sum('total_amount'),
                    'this_month' => Booking::where('status', 'completed')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('total_amount'),
                    'last_month' => Booking::where('status', 'completed')
                        ->whereMonth('created_at', now()->subMonth()->month)
                        ->whereYear('created_at', now()->subMonth()->year)
                        ->sum('total_amount'),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
