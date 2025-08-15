<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\FolderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HemisAuthController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/hemis/redirect', [HemisAuthController::class, 'redirectToHemis'])->name('hemis.redirect');
Route::get('/hemis/callback', [HemisAuthController::class, 'login'])->name('hemis.callback');
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware('auth')->name('dashboard');
Route::resources([
    'folders' => FolderController::class,
    'archives' => ArchiveController::class,
]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
