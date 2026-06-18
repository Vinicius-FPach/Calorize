<?php

use App\Controllers\AuthenticationsController;
use App\Controllers\DietsController;
use App\Controllers\FoodsController;
use App\Controllers\ProfileController;
use App\Controllers\AdminController;
use App\Controllers\HomeController;
use App\Controllers\MealsController;
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

    // Users private food (profile)
    Route::get('/profile/foods', [FoodsController::class, 'userIndex'])->name('profile.foods.index');
    Route::get('/profile/foods/page/{page}', [FoodsController::class, 'userIndex'])->name('profile.foods.paginate');
    Route::get('/profile/foods/new', [FoodsController::class, 'new'])->name('profile.foods.new');
    Route::post('/profile/foods', [FoodsController::class, 'create'])->name('profile.foods.create');
    Route::get('/profile/foods/{uuid}', [FoodsController::class, 'show'])->name('profile.foods.show');
    Route::get('/profile/foods/{uuid}/edit', [FoodsController::class, 'edit'])->name('profile.foods.edit');
    Route::put('/profile/foods/{uuid}', [FoodsController::class, 'update'])->name('profile.foods.update');
    Route::delete('/profile/foods/{uuid}', [FoodsController::class, 'destroy'])->name('profile.foods.destroy');

    // Meals
    Route::get('/diets/{diet_id}/meals/new', [MealsController::class, 'new'])->name('meals.new');
    Route::post('/diets/{diet_id}/meals', [MealsController::class, 'create'])->name('meals.create');
    Route::get('/diets/{diet_id}/meals/{meal_id}', [MealsController::class, 'show'])->name('meals.show');
    Route::delete('/diets/{diet_id}/meals/{meal_id}', [MealsController::class, 'destroy'])->name('meals.destroy');
    
    Route::post('/diets/{diet_id}/meals/{meal_id}/foods', [MealsController::class, 'addFood'])->name('meals.foods.add');

    Route::delete('/food_meal/{food_meal_id}', [MealsController::class, 'removeFood'])->name('food_meal.destroy');
});
