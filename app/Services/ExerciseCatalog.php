<?php

namespace App\Services;

use Illuminate\Http\Client\Response;

interface ExerciseCatalog
{
    public function find(string $id): Response;

    public function search(string $name): Response;
}