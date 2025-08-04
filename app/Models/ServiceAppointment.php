<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_service_id',
        'customer_id',
        'appointment_time',
        'notes',
        'status',
    ];

    protected $casts = [
        'appointment_time' => 'datetime',
    ];

    public function providerService()
    {
        return $this->belongsTo(ProviderService::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id');
    }
}