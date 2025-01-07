<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/send-message', function () {
    return view('send-message');
})->name('send-message');;

// Route::middleware('auth')->group(function () {
//     Route::get('/send-message', function () {
//         return view('send-message');
//     })->name('send-message');

//     Route::post('/send-message', [MessageController::class, 'sendMessage']);
// });

Route::middleware('auth')->group(function () {
    Route::post('/send-message', [MessageController::class, 'sendMessage']);
    Route::get('/messages/{recipientId}', [MessageController::class, 'getMessages']);
    Route::get('/users', function () {
        return \App\Models\User::where('id', '!=', auth()->id())->get(['id', 'name']);
    });
});



Route::middleware('auth')->group(function () {
    Route::post('/send-message', [MessageController::class, 'sendMessage']);
    Route::get('/messages', [MessageController::class, 'getMessages']);
});

Route::get('/users', function () {
    return \App\Models\User::where('id', '!=', auth()->id())->get(['id', 'name']);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
