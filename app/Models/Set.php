<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    use HasFactory;

    protected $fillable = ['kg', 'reps'];

    protected $appends = ['volume'];

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
