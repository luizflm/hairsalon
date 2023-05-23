<?php

use App\Http\Controllers\{
    HomeController,
    AuthController,
    AdminController,
    HairdresserController,
    ServiceController,
    AppointmentController,
    DoneServiceController,
    AvailabilityController,
    UserController,
};

use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home'); 

Route::middleware('guest')->group(function() {
    Route::view('/register', 'auth.register')->name('register'); 
    Route::post('/auth/register', [AuthController::class, 'registerAction'])->name('register_action'); 
    Route::view('/login', 'auth.login')->name('login'); 
    Route::post('/auth/login', [AuthController::class, 'loginAction'])->name('login_action');
});

Route::middleware('admin')->group(function() {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin_home'); 

    Route::resource('hairdressers', HairdresserController::class)->except(['show']);   

    Route::resource('services', ServiceController::class)->except(['show']);

    Route::get('/appointments/undone', [AppointmentController::class, 'getAllUndone'])->name('appointments.undone'); 
    Route::get('/appointments/done', [AppointmentController::class, 'getAllDone'])->name('appointments.done'); 

    Route::resource('comissions', DoneServiceController::class)->only(['index', 'store']);

    Route::get('/hairdresser/{hairdresser}/availability', [AvailabilityController::class, 'index'])->name('hairdresser_availability'); 
});

Route::middleware('auth')->group(function(){
    Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::resource('users', UserController::class)->only(['edit', 'update', 'destroy']);

    Route::resource('appointments', AppointmentController::class)->except(['show']);

    Route::get('/hairdresser/{id}/services', [ServiceController::class, 'getHairdresserAllAjax']);
});