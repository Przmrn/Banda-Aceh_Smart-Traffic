<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\TrafficDataUpdated;
use App\Models\TrafficLog;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/traffic-update', function (Request $request) {
    $stats = $request->validate([
        'car_count' => 'required|integer',
    ]);

    \Log::info('Received traffic data:', $stats);

    TrafficLog::create([
        'vehicle_count' => $stats['car_count'],
        'location_name' => 'simpang_pasteur'
    ]);

    \Log::info('About to broadcast...');
    broadcast(new TrafficDataUpdated($stats));
    \Log::info('Broadcast called.');

    return response()->json(['status' => 'success']);
});
