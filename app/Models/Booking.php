<?php
namespace App\Models;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;

 class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'property_id',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'number_of_nights',
        'price_per_night',
        'total_amount',
        'special_requests',
        'status',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'price_per_night' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
