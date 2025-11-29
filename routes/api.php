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
    // Validate the incoming data structure from realtime_worker.py
    $data = $request->validate([
        'total_vehicles' => 'required|integer',
        'streams' => 'required|array',
        'streams.*.id' => 'required|string',
        'streams.*.car_count' => 'required|integer',
        'timestamp' => 'sometimes'
    ]);

    \Log::info('Received traffic data:', $data);

    // Optional: Log to database (iterating through streams if needed)
    // For now, we'll skip detailed DB logging to focus on real-time display
    // or just log the total.
    /*
    TrafficLog::create([
        'vehicle_count' => $data['total_vehicles'],
        'location_name' => 'aggregate_streams'
    ]);
    */

    \Log::info('About to broadcast...');
    broadcast(new TrafficDataUpdated($data));
    \Log::info('Broadcast called.');

    return response()->json(['status' => 'success']);
});
