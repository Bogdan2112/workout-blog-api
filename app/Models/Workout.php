<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $fillable = ['name'];

    public function week()
    {
        return $this->belongsTo(Week::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }
}
