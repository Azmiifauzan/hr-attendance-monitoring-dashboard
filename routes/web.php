<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;

Route::get('/', [AbsenController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('/foto/{id}', [AbsenController::class, 'foto']);
Route::get('/get-divisions', [AbsenController::class, 'getDivisions'])->middleware('auth'); 

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}/edit', [UserController::class, 'edit']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/autocomplete', [AbsenController::class, 'autocomplete'])->middleware('auth');
    
});
Route::middleware('auth')->group(function () {
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::post('/companies', [CompanyController::class, 'update']);
});

require __DIR__.'/auth.php';