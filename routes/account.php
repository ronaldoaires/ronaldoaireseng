<?php

use App\Http\Controllers\Account\AvatarController;
use App\Http\Controllers\Account\DangerController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\EmailController;
use App\Http\Controllers\Account\FileDownloadController;
use App\Http\Controllers\Account\LibraryController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\PurchaseController;
use App\Http\Controllers\Account\ReaderController;
use Illuminate\Support\Facades\Route;

// The "/conta" prefix and the "account." name prefix come from routes/web.php.

Route::get('/', DashboardController::class)->name('index');

// Library: everything the user bought, with downloads and the online reader.
Route::get('/biblioteca', [LibraryController::class, 'index'])->name('library');
Route::get('/biblioteca/{product:slug}', [LibraryController::class, 'show'])
    ->withTrashed()
    ->name('library.show');

Route::get('/biblioteca/{product:slug}/ler/{chapter:slug?}', ReaderController::class)
    ->scopeBindings()
    ->withTrashed()
    ->name('reader');

Route::get('/arquivos/{file}/baixar', FileDownloadController::class)
    ->whereNumber('file')
    ->middleware('throttle:30,1')
    ->name('files.download');

Route::get('/compras', [PurchaseController::class, 'index'])->name('purchases');

Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile');
Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');

Route::get('/senha', [PasswordController::class, 'edit'])->name('password');
Route::put('/senha', [PasswordController::class, 'update'])
    ->middleware('throttle:6,1')
    ->name('password.update');

Route::get('/email', [EmailController::class, 'edit'])->name('email');
Route::put('/email', [EmailController::class, 'update'])
    ->middleware('throttle:5,10')
    ->name('email.update');
Route::delete('/email', [EmailController::class, 'cancel'])->name('email.cancel');

Route::get('/avatar', [AvatarController::class, 'edit'])->name('avatar');
Route::post('/avatar', [AvatarController::class, 'update'])
    ->middleware('throttle:10,1')
    ->name('avatar.update');
Route::delete('/avatar', [AvatarController::class, 'destroy'])->name('avatar.destroy');

Route::get('/encerrar', [DangerController::class, 'edit'])->name('danger');
Route::post('/encerrar', [DangerController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('danger.store');
Route::delete('/encerrar', [DangerController::class, 'destroy'])->name('danger.cancel');
