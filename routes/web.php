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

    // appointments (admin)
    Route::get('/appointments/admin', [AppointmentController::class, 'getAllUndone'])->name('appointments'); 
    Route::get('/appointments/admin/done', [AppointmentController::class, 'getAllDone'])->name('appointments_done'); 

    // hd_done_services
    Route::get('/comission', [DoneServiceController::class, 'getComission'])->name('comission'); 
    Route::post('/done_service', [DoneServiceController::class, 'insertAction'])->name('insert_done_service_action');

    // availability
    Route::get('/hairdresser/availability/{id}', [AvailabilityController::class, 'getHairdresserAvailability'])->name('hairdresser_availability'); 
});

Route::middleware('auth')->group(function(){
    Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    
    // user
    Route::get('/user/edit/{id}', [UserController::class, 'updateView'])->name('edit_user'); 
    Route::put('/user/edit/{id}', [UserController::class, 'updateAction'])->name('edit_user_action');
    Route::delete('/user/delete/{id}', [UserController::class, 'delete'])->name('delete_user_action');

    // appointments
    Route::get('/appointments', [AppointmentController::class, 'getMyAppointments'])->name('user_appointments'); 
    Route::get('/appointment', [AppointmentController::class, 'setAppointmentView'])->name('set_appointment'); 
    Route::post('/appointment', [AppointmentController::class, 'setAppointmentAction'])->name('set_appointment_action');
    Route::get('/appointment/edit/{id}', [AppointmentController::class, 'updateView'])->name('edit_appointment');
    Route::put('/appointment/edit/{id}', [AppointmentController::class, 'updateAction'])->name('edit_appointment_action');
    Route::delete('/appointment/delete/{id}', [AppointmentController::class, 'delete'])->name('delete_appointment_action');

    // ajax
    Route::get('/hairdresser/services/{id}', [ServiceController::class, 'getHairdresserAllAjax']);
});