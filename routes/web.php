<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DoneServiceController;
use App\Http\Controllers\HairdresserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home'); 

Route::middleware('guest')->group(function() {
    Route::get('/register', [AuthController::class, 'insertView'])->name('register'); 
    Route::post('register', [AuthController::class, 'insertAction'])->name('register_action'); 
    Route::get('/login', [AuthController::class, 'loginView'])->name('login'); 
    Route::post('/login', [AuthController::class, 'loginAction'])->name('login_action');
});

Route::middleware('admin')->group(function() {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin_home'); 

    Route::resource('hairdressers', HairdresserController::class)->except(['show']);   

    Route::resource('services', ServiceController::class)->except(['show']);

    Route::get('/appointments/undone', [AppointmentController::class, 'getAllUndone'])->name('appointments.undone'); 
    Route::get('/appointments/done', [AppointmentController::class, 'getAllDone'])->name('appointments.done'); 

    Route::resource('comissions', DoneServiceController::class)->only(['index', 'store']);

    // availability
    Route::get('/hairdresser/availability/{id}', [AvailabilityController::class, 'getHairdresserAvailability'])->name('hairdresser_availability'); 
});

Route::middleware('auth')->group(function(){
    Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::resource('users', UserController::class)->only(['edit', 'update', 'destroy']);

    Route::resource('appointments', AppointmentController::class)->except(['show']);

    // ajax
    Route::get('/hairdresser/services/{id}', [ServiceController::class, 'getHairdresserAllAjax']);
});