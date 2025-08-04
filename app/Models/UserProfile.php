<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'id_verification_status',
        'bank_account_details',
        'qualifications',
        'service_areas',
        'response_rate',
        'last_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}