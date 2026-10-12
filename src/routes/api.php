<?php

use App\Http\Controllers\HealthUnitController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::apiResource('patients', PatientController::class);
Route::get('health-units', [HealthUnitController::class, 'index'])
    ->name('health-units.index');
