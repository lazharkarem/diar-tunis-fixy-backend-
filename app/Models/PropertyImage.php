<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'image_url',
        'is_thumbnail',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

  
}