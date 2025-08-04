<?php
namespace App\Models;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;

 class ExperienceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'experience_id',
        'start_time',
        'end_time',
        'available_slots'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function experience()
    {
        return $this->belongsTo(Experience::class);
    }
}