<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\StaticAnalysisController;
use App\Http\Controllers\TrafficController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Route untuk menampilkan halaman dashboard (Real-time)
Route::get('/dashboard', [TrafficController::class, 'showDashboard'])->name('dashboard');

// Route untuk menangani proses upload dan menampilkan hasil (Real-time analyze)
Route::post('/dashboard/analyze', [TrafficController::class, 'analyzeVideo'])->name('dashboard.analyze');

// Menu Analisis Statis
Route::get('/static-analysis', [StaticAnalysisController::class, 'index'])->name('static.index');
Route::post('/static-analysis/upload', [StaticAnalysisController::class, 'upload'])->name('static.upload');

Route::get('/history', [TrafficController::class, 'showHistory'])->name('history');

// Test route for WebSocket
Route::get('/test-ws', function () {
    event(new App\Events\TrafficDataUpdated([
        'total_vehicles' => 5,
        'streams' => [
            [
                'id' => 'stream-1',
                'name' => 'Simpang Lima',
                'car_count' => 3,
                'timestamp' => now()
            ],
            [
                'id' => 'stream-2',
                'name' => 'Simpang Emat',
                'car_count' => 2,
                'timestamp' => now()
            ]
        ]
    ]));
    return 'Test event dispatched!';
});
