<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/jobs', [JobController::class, 'index'])->name('dashboard');
Route::post('/admin/jobs/cancel/{id}', [JobController::class, 'cancelJob'])->name('cancel-job');
