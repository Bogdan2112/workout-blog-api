<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Set extends Model
{
    use HasFactory;
    
    protected $fillable = ['kg', 'reps'];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
