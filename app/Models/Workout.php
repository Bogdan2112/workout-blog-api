<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function volume(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->exercises->sum('volume'),
        );
    }
}
