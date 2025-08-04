<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'license_number',
        'years_of_experience',
        'is_premium',
        'average_rating',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(ProviderService::class);
    }

    public function providerServices()
    {
        return $this->hasMany(ProviderService::class);
    }
}