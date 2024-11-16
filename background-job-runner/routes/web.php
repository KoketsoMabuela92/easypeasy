<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

Route::prefix('dashboard')->name('dashboard.')->group(function() {
    // Route for the dashboard index page
    Route::get('/', [JobController::class, 'index'])->name('index');

    // Route for viewing a specific job (optional, based on your controller)
    Route::get('/{jobId}', [JobController::class, 'show'])->name('show');

    // Route for cancelling a job
    Route::post('/cancel/{jobId}', [JobController::class, 'cancelJob'])->name('cancel');

    // Route for retrying a job
    Route::post('retry-job/{jobId}', [JobController::class, 'retryJob'])->name('retry-job');
});
