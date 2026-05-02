<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('landing-page');
})->name('Landing Page');


Route::livewire('/dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('/tagihan', 'pages.tagihan')
    ->middleware(['auth', 'verified'])
    ->name('tagihan.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Rute Placeholder Dashboard Warga
    Route::get('/statistik', fn() => view('dashboard'))->name('statistik.index');
    Route::get('/meteran', fn() => view('dashboard'))->name('meteran.index');
    Route::get('/pengaduan', fn() => view('dashboard'))->name('pengaduan.index');
});

// Route::middleware(['auth', 'verified'])->group(function() {
//     Route::get('/dashboard', [])
// });
// Route::middleware(['auth', 'verified'])->group(function () {
//     // ... rute dashboard bawaan ...
//     Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
//     Route::post('/tagihan/{id}/bayar', [TagihanController::class, 'uploadBukti'])->name('tagihan.bayar');
// });

Route::get('/admin', function () {
    return redirect()->route('login'); 
});

require __DIR__.'/auth.php';
