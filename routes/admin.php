<?php
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::resources([
        'categories' => CategoryController::class,
    ]);
});