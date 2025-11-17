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
    // Validasi data (opsional tapi disarankan)
    $stats = $request->validate([
        'car_count' => 'required|integer',
    ]);

    // LANGKAH BARU 1: Simpan ke Database
    TrafficLog::create([
        'vehicle_count' => $stats['car_count'],
        'location_name' => 'simpang_pasteur' // Ganti jika Anda menganalisis lokasi lain
    ]);

    // LANGKAH 2: Tetap Siarkan ke Dashboard
    TrafficDataUpdated::dispatch($stats);

    // Siarkan event ke semua pendengar
    TrafficDataUpdated::dispatch($stats);

    return response()->json(['status' => 'success']);
});
