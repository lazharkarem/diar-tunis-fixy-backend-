<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Experience;
use App\Models\ServiceProvider;
use App\Models\ServiceAppointment;
use App\Models\Payment;
use Illuminate\Http\Response;

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


// USER MANAGEMENT
    public function getUsers(Request $request)
    {
        $users = User::with(['profile', 'serviceProvider'])->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'user_type' => 'required|in:guest,host,service_customer,service_provider,admin',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'user_type' => $validated['user_type'],
        ]);

        if (isset($validated['phone']) || isset($validated['address'])) {
            $user->profile()->create([
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $user->load(['profile']),
            'message' => 'User created successfully'
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::with('profile')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'user_type' => 'sometimes|in:guest,host,service_customer,service_provider,admin',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update(array_intersect_key($validated, array_flip(['name', 'email', 'user_type', 'is_active'])));

        if (isset($validated['phone']) || isset($validated['address'])) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $validated['phone'] ?? $user->profile?->phone,
                    'address' => $validated['address'] ?? $user->profile?->address,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $user->load('profile'),
            'message' => 'User updated successfully'
        ]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deletion of the current admin user
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    // PROPERTY MANAGEMENT
    public function getAllProperties(Request $request)
    {
        $properties = Property::with(['user', 'amenities', 'images'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $properties,
            'message' => 'Properties retrieved successfully'
        ]);
    }

    public function updatePropertyStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,suspended'
        ]);

        $property = Property::findOrFail($id);
        $property->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $property,
            'message' => 'Property status updated successfully'
        ]);
    }

    public function deleteProperty($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully'
        ]);
    }

    // BOOKING MANAGEMENT
    public function getAllBookings(Request $request)
    {
        $bookings = Booking::with(['user', 'property'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'message' => 'Bookings retrieved successfully'
        ]);
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $booking->load(['user', 'property']),
            'message' => 'Booking status updated successfully'
        ]);
    }

    // EXPERIENCE MANAGEMENT
    public function getAllExperiences(Request $request)
    {
        $experiences = Experience::with(['provider', 'schedules'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $experiences,
            'message' => 'Experiences retrieved successfully'
        ]);
    }

    public function updateExperienceStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,suspended'
        ]);

        $experience = Experience::findOrFail($id);
        $experience->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $experience,
            'message' => 'Experience status updated successfully'
        ]);
    }

    public function deleteExperience($id)
    {
        $experience = Experience::findOrFail($id);
        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully'
        ]);
    }

    // SERVICE PROVIDER MANAGEMENT
    public function getAllServiceProviders(Request $request)
    {
        $providers = ServiceProvider::with(['user', 'services'])
            ->when($request->status, function($query, $status) {
                return $query->where('verification_status', $status);
            })
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $providers,
            'message' => 'Service providers retrieved successfully'
        ]);
    }

    public function updateServiceProviderStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:pending,verified,rejected,suspended'
        ]);

        $provider = ServiceProvider::findOrFail($id);
        $provider->update(['verification_status' => $validated['verification_status']]);

        return response()->json([
            'success' => true,
            'data' => $provider->load(['user', 'services']),
            'message' => 'Service provider status updated successfully'
        ]);
    }

    // SERVICE APPOINTMENTS MANAGEMENT
    public function getAllServiceAppointments(Request $request)
    {
        $appointments = ServiceAppointment::with(['customer', 'serviceProvider', 'service'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('scheduled_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $appointments,
            'message' => 'Service appointments retrieved successfully'
        ]);
    }

    // PAYMENTS MANAGEMENT
    public function getAllPayments(Request $request)
    {
        $payments = Payment::with(['payable'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
            'message' => 'Payments retrieved successfully'
        ]);
    }

    // STATISTICS AND ANALYTICS
    public function getStatistics()
    {
        $stats = [
            'total_users' => User::count(),
            'total_properties' => Property::count(),
            'total_bookings' => Booking::count(),
            'total_service_providers' => ServiceProvider::count(),
            'total_appointments' => ServiceAppointment::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'pending_properties' => Property::where('status', 'pending')->count(),
            'pending_providers' => ServiceProvider::where('verification_status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ]);
    }
}
