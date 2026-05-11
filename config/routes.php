<?php

use App\Controllers\AuthenticationsController;
use App\Controllers\DietsController;
use App\Controllers\ProblemsController;
use App\Controllers\ProfileController;
use App\Controllers\ReinforceProblemsController;
use App\Controllers\AdminController;
use App\Controllers\HomeController;
use Core\Router\Route;

// Authentication
Route::get('/login', [AuthenticationsController::class, 'new'])->name('users.login');
Route::post('/login', [AuthenticationsController::class, 'authenticate'])->name('users.authenticate');

Route::middleware('admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('root');

    // Create
    Route::get('/diets/new', [DietsController::class, 'new'])->name('diets.new');
    Route::post('/diets', [DietsController::class, 'create'])->name('diets.create');

    // Retrieve
    Route::get('/diets', [DietsController::class, 'index'])->name('diets.index');
    Route::get('/diets/page/{page}', [DietsController::class, 'index'])->name('diets.paginate');
    Route::get('/diets/{id}', [DietsController::class, 'show'])->name('diets.show');

    // Update
    Route::get('/diets/{id}/edit', [DietsController::class, 'edit'])->name('diets.edit');
    Route::put('/diets/{id}', [DietsController::class, 'update'])->name('diets.update');

    // Delete
    Route::delete('/diets/{id}', [DietsController::class, 'destroy'])->name('diets.destroy');

    // Logout
    Route::get('/logout', [AuthenticationsController::class, 'destroy'])->name('users.logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // Biometric Profile
    Route::get('/profile/biometric/new', [ProfileController::class, 'newBiometric'])->name('profile.biometric.new');
    Route::post('/profile/biometric', [ProfileController::class, 'createBiometric'])->name('profile.biometric.create');
    Route::get('/profile/biometric/edit', [ProfileController::class, 'editBiometric'])->name('profile.biometric.edit');
    Route::put('/profile/biometric', [ProfileController::class, 'updateBiometric'])->name('profile.biometric.update');

    // Reinforce Problems
    Route::get('/reinforce/problems', [ReinforceProblemsController::class, 'index'])
        ->name('reinforce.problems');
    Route::get('/reinforce/problems/page/{page}', [ReinforceProblemsController::class, 'index'])
        ->name('reinforce.problems.paginate');

    Route::get('/reinforce/problems/supported', [ReinforceProblemsController::class, 'supported'])
        ->name('reinforce.problems.supported');

    Route::post('/reinforce/problems/{id}', [ReinforceProblemsController::class, 'support'])
        ->name('reinforce.problems.create');
    Route::post(
        '/reinforce/problems/{id}/stopped-supporting',
        [ReinforceProblemsController::class, 'stoppedSupporting']
    )->name('reinforce.problems.stopped-supporting');
});
