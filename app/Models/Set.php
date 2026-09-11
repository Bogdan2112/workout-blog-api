<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    protected $fillable = ['kg', 'reps'];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
