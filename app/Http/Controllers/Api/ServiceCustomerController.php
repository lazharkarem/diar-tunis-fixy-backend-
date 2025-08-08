<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Models\ServiceAppointment;
use App\Models\ProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceCustomerController extends Controller
{
    /**
     * Get all service providers
     */
    public function getServiceProviders(Request $request)
    {
        $query = ServiceProvider::with(['user.profile', 'services'])
            ->where('is_available', true);

        // Apply filters
        if ($request->filled('category_id')) {
            $query->whereHas('services', function($q) use ($request) {
                $q->where('service_category_id', $request->get('category_id'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $serviceProviders = $query->orderBy('created_at', 'desc')
                                 ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $serviceProviders,
            'message' => 'Service providers retrieved successfully'
        ]);
    }

    /**
     * Get a specific service provider
     */
    public function getServiceProvider($id)
    {
        $serviceProvider = ServiceProvider::with([
            'user.profile',
            'services.serviceCategory'
        ])->where('is_available', true)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $serviceProvider,
            'message' => 'Service provider retrieved successfully'
        ]);
    }

    /**
     * Get all appointments for the customer
     */
    public function getAppointments(Request $request)
    {
        $appointments = ServiceAppointment::where('user_id', Auth::id())
            ->with(['providerService.serviceProvider.user.profile', 'providerService.serviceCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $appointments,
            'message' => 'Appointments retrieved successfully'
        ]);
    }

    /**
     * Book an appointment
     */
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_service_id' => 'required|exists:provider_services,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the service is available
        $providerService = ProviderService::with('serviceProvider')
            ->where('is_available', true)
            ->findOrFail($request->provider_service_id);

        // Check if the service provider is available
        if (!$providerService->serviceProvider->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Service provider is not available'
            ], 400);
        }

        // Check for conflicting appointments
        $conflictingAppointment = ServiceAppointment::where('provider_service_id', $request->provider_service_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($conflictingAppointment) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is already booked'
            ], 400);
        }

        $appointment = ServiceAppointment::create([
            'user_id' => Auth::id(),
            'provider_service_id' => $request->provider_service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'notes' => $request->notes,
            'total_amount' => $providerService->price,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Appointment booked successfully'
        ], 201);
    }

    /**
     * Get a specific appointment
     */
    public function getAppointment($id)
    {
        $appointment = ServiceAppointment::where('user_id', Auth::id())
            ->with(['providerService.serviceProvider.user.profile', 'providerService.serviceCategory'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Appointment retrieved successfully'
        ]);
    }

    /**
     * Cancel an appointment
     */
    public function cancelAppointment(Request $request, $id)
    {
        $appointment = ServiceAppointment::where('user_id', Auth::id())
            ->where('status', '!=', 'cancelled')
            ->findOrFail($id);

        // Check if appointment can be cancelled (e.g., not too close to appointment time)
        $appointmentDateTime = \Carbon\Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time);
        $now = \Carbon\Carbon::now();

        if ($appointmentDateTime->diffInHours($now) < 24) {
            return response()->json([
                'success' => false,
                'message' => 'Appointments can only be cancelled at least 24 hours in advance'
            ], 400);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Appointment cancelled successfully'
        ]);
    }
}
