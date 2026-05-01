<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('landing-page');
})->name('Landing Page');

Route::get('/dashboard', function () {
    // Proteksi Tambahan: Jika Admin nyasar ke dashboard warga, lempar ke portal admin
    if (Auth::user()->hasRole(['super_admin', 'admin', 'admin-PDAM'])) {
        return redirect(env('FILAMENT_PATH', 'admin-fallback-path'));
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/admin', function () {
    return redirect()->route('login'); 
});

require __DIR__.'/auth.php';
