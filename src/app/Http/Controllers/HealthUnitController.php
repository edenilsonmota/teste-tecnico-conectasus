<?php

namespace App\Http\Controllers;

use App\Models\HealthUnit;
use Illuminate\Http\JsonResponse;

class HealthUnitController extends Controller
{
    public function index(): JsonResponse
    {
        $healthUnits = HealthUnit::query()
            ->select(['id', 'name', 'city'])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return response()->json($healthUnits);
    }
}
