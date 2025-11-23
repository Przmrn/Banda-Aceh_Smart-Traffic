<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


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

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [HomeController::class, 'home']);
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

	Route::get('billing', function () {
		return view('billing');
	})->name('billing');

	Route::get('profile', function () {
		return view('profile');
	})->name('profile');

	Route::get('rtl', function () {
		return view('rtl');
	})->name('rtl');

	Route::get('user-management', function () {
		return view('laravel-examples/user-management');
	})->name('user-management');

	Route::get('tables', function () {
		return view('tables');
	})->name('tables');

    Route::get('virtual-reality', function () {
		return view('virtual-reality');
	})->name('virtual-reality');

    Route::get('static-sign-in', function () {
		return view('static-sign-in');
	})->name('sign-in');

    Route::get('static-sign-up', function () {
		return view('static-sign-up');
	})->name('sign-up');

    Route::get('/logout', [SessionsController::class, 'destroy']);
	Route::get('/user-profile', [InfoUserController::class, 'create']);
	Route::post('/user-profile', [InfoUserController::class, 'store']);
    Route::get('/login', function () {
		return view('dashboard');
	})->name('sign-up');
});



Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionsController::class, 'create']);
    Route::post('/session', [SessionsController::class, 'store']);
	Route::get('/login/forgot-password', [ResetController::class, 'create']);
	Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
	Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
	Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');

});

Route::get('/login', function () {
    return view('session/login-session');
})->name('login');

Route::get('/history', [App\Http\Controllers\TrafficController::class, 'showHistory'])->name('history');

// Route untuk menampilkan halaman dashboard dengan form upload
Route::get('/dashboard', [App\Http\Controllers\TrafficController::class, 'showDashboard'])->name('dashboard');

// Route untuk menangani proses upload dan menampilkan hasil
Route::post('/dashboard/analyze', [App\Http\Controllers\TrafficController::class, 'analyzeVideo'])->name('dashboard.analyze');

Route::get('/test-broadcast', function() {
    broadcast(new \App\Events\TrafficDataUpdated(['car_count' => 999]));
    return 'Broadcast sent!';
});
