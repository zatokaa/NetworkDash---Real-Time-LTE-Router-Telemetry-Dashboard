<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/', \App\Livewire\Dashboard\MainDashboard::class)->name('dashboard');
    Route::get('/history', \App\Livewire\History\SignalHistory::class)->name('history.index');
    Route::get('/routers', \App\Livewire\Routers\RouterManager::class)->name('routers.index');
    Route::get('/settings', \App\Livewire\Settings\SettingsManager::class)->name('settings.index');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
