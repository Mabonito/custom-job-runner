<?php

use App\Http\Controllers\JobDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/jobs', [JobDashboardController::class, 'index'])->name('jobs.index');
Route::get('/jobs/errors', [JobDashboardController::class, 'errors'])->name('jobs.errors');
Route::post('/jobs/cancel/{pid}', [JobDashboardController::class, 'cancel'])->name('jobs.cancel');

