<?php

use App\Http\Controllers\System\AirplaneController;
use App\Http\Controllers\System\AirplaneSitController;
use App\Http\Controllers\System\FlightController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::apiResource('airplanes', AirplaneController::class);
Route::apiResource('airplanes.sits', AirplaneSitController::class)->only(['index', 'show']);
Route::apiResource('flights', FlightController::class);
