<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ShareController;
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
//add midleware auth to folders and archives routes
Route::middleware('auth')->group(function () {
    Route::resources([
        'folders' => FolderController::class,
        'archives' => ArchiveController::class,
    ]);
    Route::get('/users', function(){
        return response()->json([
            'data' => \App\Models\User::all(['name','id'])
        ]);
    });
    Route::post('/shareble/{id}/share', [ShareController::class, 'store'])->name('folders.share');
    Route::post('/shareble/{id}/share/send', [ShareController::class,'send'])->name('folders.share.send');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
