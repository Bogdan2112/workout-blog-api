<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Exercise extends Model
{
    protected $fillable = ['name'];

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}
