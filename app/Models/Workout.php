<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workout extends Model
{
    use HasFactory;

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
