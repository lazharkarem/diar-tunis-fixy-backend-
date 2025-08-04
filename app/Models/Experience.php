<?php
namespace App\Models;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;

 class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'category',
        'price_per_person',
        'duration',
        'max_participants',
        'status',
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function schedules()
    {
        return $this->hasMany(ExperienceSchedule::class);
    }
}