<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Models\ProviderService;
use App\Models\ServiceAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceProviderController extends Controller
{
    /**
     * Get all services for the authenticated service provider
     */
    public function getServices(Request $request)
    {
        $services = ProviderService::where('service_provider_id', Auth::id())
            ->with(['serviceProvider', 'serviceCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $services,
            'message' => 'Services retrieved successfully'
        ]);
    }

    /**
     * Create a new service
     */
    public function createService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_category_id' => 'required|exists:service_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $service = ProviderService::create([
            'service_provider_id' => Auth::id(),
            'service_category_id' => $request->service_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'duration' => $request->duration,
            'is_available' => $request->get('is_available', true),
        ]);

        return response()->json([
            'success' => true,
            'data' => $service,
            'message' => 'Service created successfully'
        ], 201);
    }

    /**
     * Update a service
     */
    public function updateService(Request $request, $id)
    {
        $service = ProviderService::where('service_provider_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'service_category_id' => 'sometimes|required|exists:service_categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration' => 'sometimes|required|integer|min:1',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $service->update($request->only([
            'service_category_id', 'title', 'description', 'price', 'duration', 'is_available'
        ]));

        return response()->json([
            'success' => true,
            'data' => $service,
            'message' => 'Service updated successfully'
        ]);
    }

    /**
     * Delete a service
     */
    public function deleteService($id)
    {
        $service = ProviderService::where('service_provider_id', Auth::id())->findOrFail($id);
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
    }

    /**
     * Get all appointments for the service provider
     */
    public function getAppointments(Request $request)
    {
        $appointments = ServiceAppointment::whereHas('providerService', function($query) {
            $query->where('service_provider_id', Auth::id());
        })
        ->with(['providerService', 'user.profile'])
        ->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $appointments,
            'message' => 'Appointments retrieved successfully'
        ]);
    }

    /**
     * Update appointment status
     */
    public function updateAppointmentStatus(Request $request, $id)
    {
        $appointment = ServiceAppointment::whereHas('providerService', function($query) {
            $query->where('service_provider_id', Auth::id());
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

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Appointment status updated successfully'
        ]);
    }

    /**
     * Get service provider earnings
     */
    public function getEarnings(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $earnings = ServiceAppointment::whereHas('providerService', function($query) {
            $query->where('service_provider_id', Auth::id());
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
     * Get service provider statistics
     */
    public function getStatistics()
    {
        $totalServices = ProviderService::where('service_provider_id', Auth::id())->count();
        $totalAppointments = ServiceAppointment::whereHas('providerService', function($query) {
            $query->where('service_provider_id', Auth::id());
        })->count();
        $completedAppointments = ServiceAppointment::whereHas('providerService', function($query) {
            $query->where('service_provider_id', Auth::id());
        })->where('status', 'completed')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_services' => $totalServices,
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'completion_rate' => $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 2) : 0
            ],
            'message' => 'Statistics retrieved successfully'
        ]);
    }

    /**
     * Update service provider availability
     */
    public function updateAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_available' => 'required|boolean',
            'available_hours' => 'sometimes|array',
            'available_days' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceProvider = ServiceProvider::where('user_id', Auth::id())->first();

        if (!$serviceProvider) {
            return response()->json([
                'success' => false,
                'message' => 'Service provider profile not found'
            ], 404);
        }

        $serviceProvider->update([
            'is_available' => $request->is_available,
            'available_hours' => $request->get('available_hours'),
            'available_days' => $request->get('available_days'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $serviceProvider,
            'message' => 'Availability updated successfully'
        ]);
    }
}
