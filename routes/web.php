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

// tarefa: fazer a verificação se o horário escolhido no appointment é o ULTIMO horário da availability do 
// hairdresser, caso seja, não permitir naquele horário, permitir apenas até o penultimo

// ao terminar as verificações, popular o banco de dados e verificar se todas as views estão certas

Route::get('/', [HomeController::class, 'index'])->name('home'); // responsiva

Route::middleware('guest')->group(function() {
    Route::get('/register', [AuthController::class, 'insertView'])->name('register'); // responsiva
    Route::post('register', [AuthController::class, 'insertAction'])->name('register_action'); //
    Route::get('/login', [AuthController::class, 'loginView'])->name('login'); // responsiva
    Route::post('/login', [AuthController::class, 'loginAction'])->name('login_action'); //
});

Route::middleware('admin')->group(function() {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin_home'); // responsiva

    // cabelereiras
    Route::get('/hairdresser', [HairdresserController::class, 'insertView'])->name('insert_hairdresser'); // responsiva
    Route::post('/hairdresser', [HairdresserController::class, 'insertAction'])->name('insert_hairdresser_action'); //
    Route::get('/hairdressers', [HairdresserController::class, 'getAll'])->name('hairdressers'); // responsiva
    Route::get('/hairdresser/edit/{id}', [HairdresserController::class, 'updateView'])->name('edit_hairdresser'); // responsiva
    Route::put('/hairdresser/edit/{id}', [HairdresserController::class, 'updateAction'])->name('edit_hairdresser_action'); //
    Route::delete('/hairdresser/delete/{id}', [HairdresserController::class, 'delete'])->name('delete_hairdresser_action'); //

    // hd_services
    Route::get('/service', [ServiceController::class, 'insertView'])->name('insert_service'); // responsiva
    Route::post('/service', [ServiceController::class, 'insertAction'])->name('insert_service_action'); //
    Route::get('/services', [ServiceController::class, 'getHairdresserAll'])->name('services'); // responsiva
    Route::get('/service/edit/{id}', [ServiceController::class, 'updateView'])->name('edit_service'); // responsiva
    Route::put('/service/edit/{id}', [ServiceController::class, 'updateAction'])->name('edit_service_action'); //
    Route::delete('/service/delete/{id}', [ServiceController::class, 'delete'])->name('delete_service_action'); //

    // appointments (admin)
    Route::get('/appointments/admin', [AppointmentController::class, 'getAllUndone'])->name('appointments'); // responsiva
    Route::get('/appointments/admin/done', [AppointmentController::class, 'getAllDone'])->name('appointments_done'); // responsiva

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
});