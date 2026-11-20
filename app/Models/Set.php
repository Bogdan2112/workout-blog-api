<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Set extends Model
{
    use HasFactory;
    
    protected $fillable = ['kg', 'reps'];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    protected function volume(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->kg * $this->reps,
        );
    }
}
