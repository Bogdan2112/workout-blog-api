<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exercise extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'workoutx_id',
        'target_muscle',
        'instructions',
        'mechanic',
    ];

    protected function casts(): array
    {
        return [
            'instructions' => 'array',
        ];
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}
