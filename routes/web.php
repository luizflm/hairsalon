<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoneServiceController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\HairdresserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(('guest'))->group(function() {
    Route::get('/register', [AuthController::class, 'insertView'])->name('register'); //
    Route::post('register', [AuthController::class, 'insertAction'])->name('register_action'); //
    Route::get('/login', [AuthController::class, 'loginView'])->name('login'); //
    Route::post('/login', [AuthController::class, 'loginAction'])->name('login_action'); //
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function(){
    // user
    Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout'); //
    // Route::get('/user/{id}', [UserController::class, 'getInfo'])->name('get_user_info');
    Route::get('/user/edit/{id}', [UserController::class, 'updateView'])->name('edit_user'); //
    Route::put('/user/edit/{id}', [UserController::class, 'updateAction'])->name('edit_user_action'); //
    Route::delete('/user/delete/{id}', [UserController::class, 'delete'])->name('delete_user_action'); //

    // cabelereiras
    Route::post('/hairdresser', [HairdresserController::class, 'insert']);
    Route::put('/hairdresser/edit/{id}', [HairdresserController::class, 'update']);
    Route::delete('/hairdresser/delete/{id}', [HairdresserController::class, 'delete']);
    Route::get('/hairdresser/{id}', [HairdresserController::class, 'getInfo']);
    Route::get('/hairdressers', [HairdresserController::class, 'getAll']);

    // appointments
    Route::get('/appointments', [AppointmentController::class, 'getMyAppointments']);
    Route::get('/appointment', [AppointmentController::class, 'setAppointmentView'])->name('set_appointment');
    Route::post('/appointment', [AppointmentController::class, 'setAppointmentAction'])->name('set_appointment_action');
    Route::delete('/appointment/delete/{id}', [AppointmentController::class, 'delete']);
    Route::put('/appointment/edit/{id}', [AppointmentController::class, 'update']);

    // hd_services
    Route::get('/services', [ServiceController::class, 'getAll']);
    Route::get('/service/{id}', [ServiceController::class, 'getOne']);
    Route::post('/service', [ServiceController::class, 'insert']);
    Route::put('/service/edit/{id}', [ServiceController::class, 'update']);
    Route::delete('/service/delete/{id}', [ServiceController::class, 'delete']);

    // hd_done_services
    Route::get('/done_services', [DoneServiceController::class, 'getDoneServices']); 
    Route::get('/done_service/{id}', [DoneServiceController::class, 'getOne']);
    Route::post('/done_service', [DoneServiceController::class, 'insert']);
    Route::delete('/done_service/delete/{id}', [DoneServiceController::class, 'delete']);

    // hd_evaluation
    Route::get('/evaluations', [EvaluationController::class, 'getMyEvaluations']);
    Route::get('/evaluation/{id}', [EvaluationController::class, 'getOne']);
    Route::get('/evaluations/{id}', [EvaluationController::class, 'getHairdresserEvaluations']);
    Route::post('/evaluation', [EvaluationController::class, 'insert']); 
    Route::put('/evaluation/edit/{id}', [EvaluationController::class, 'update']);
    Route::delete('/evaluation/delete/{id}', [EvaluationController::class, 'delete']);

    // products
    Route::get('/products', [ProductController::class, 'getAll']);
    Route::get('/product/{id}', [ProductController::class, 'getOne']);
    Route::post('/product', [ProductController::class, 'insert']);
    Route::put('/product/edit/{id}', [ProductController::class, 'update']);
    Route::delete('/product/delete/{id}', [ProductController::class, 'delete']);
});