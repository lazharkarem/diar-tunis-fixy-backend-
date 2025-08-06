<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'user_type',
        'profile_picture',
        'address',
        'email_verified_at',
        'is_active', // Add this field
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'user_type' => \App\Enums\UserType::class,
        'is_active' => 'boolean', // Add this cast
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true, // Default value
    ];

    // ========== RELATIONSHIPS ==========

    /**
     * Get the roles associated with the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's service provider profile.
     * This relationship only exists if user_type is 'service_provider'
     */
    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class);
    }

    /**
     * Get properties hosted by this user (for hosts).
     */
    public function hostedProperties()
    {
        return $this->hasMany(Property::class, 'host_id');
    }

    /**
     * Get bookings made by this user (as guest).
     */
    public function guestBookings()
    {
        return $this->hasMany(Booking::class, 'guest_id');
    }

    /**
     * Get bookings for properties hosted by this user.
     */
    public function hostBookings()
    {
        return $this->hasManyThrough(Booking::class, Property::class, 'host_id', 'property_id');
    }

    /**
     * Get reviews written by this user.
     */
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get reviews received by this user (for their properties or services).
     */
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get payments made by this user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payer_id');
    }

    /**
     * Get service appointments booked by this user.
     */
    public function serviceAppointments()
    {
        return $this->hasMany(ServiceAppointment::class, 'customer_id');
    }

    // ========== HELPER METHODS ==========

    /**
     * Check if user is a guest.
     */
    public function isGuest()
    {
        return $this->user_type === \App\Enums\UserType::GUEST;
    }

    /**
     * Check if user is a host.
     */
    public function isHost()
    {
        return $this->user_type === \App\Enums\UserType::HOST;
    }

    /**
     * Check if user is a service customer.
     */
    public function isServiceCustomer()
    {
        return $this->user_type === \App\Enums\UserType::SERVICE_CUSTOMER;
    }

    /**
     * Check if user is a service provider.
     */
    public function isServiceProvider()
    {
        return $this->user_type === \App\Enums\UserType::SERVICE_PROVIDER;
    }

    /**
     * Check if user account is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get user's full display name.
     */
    public function getDisplayNameAttribute()
    {
        return $this->profile?->full_name ?? $this->name;
    }

    /**
     * Get user's avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        return $this->profile?->avatar ?? '/default-avatar.png';
    }
}