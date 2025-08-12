<?php
namespace App\Models;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;

 class Property extends Model
{
        use HasFactory;

        protected $fillable = [
            'host_id',
            'title',
            'description',
            'address',
            'latitude',
            'longitude',
            'property_type',
            'number_of_guests',
            'number_of_bedrooms',
            'number_of_beds',
            'number_of_bathrooms',
            'price_per_night',
            'status',
        ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function amenities()
    {
       return $this->hasMany(PropertyAmenity::class);
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}