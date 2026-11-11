<?php
use App\Http\Controllers\CampusController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CampusController::class, 'index'])->name('home');
Route::get('/auth/microsoft', [CampusController::class, 'microsoftStart'])->name('microsoft.start');
Route::get('/auth/microsoft/callback', [CampusController::class, 'microsoftCallback'])->name('microsoft.callback');
Route::get('/demo-login', [CampusController::class, 'demoLogin'])->name('demo.login');
Route::get('/items/{item}', [CampusController::class, 'show'])->name('items.show');
Route::middleware('auth')->group(function () {
    Route::get('/items/{item}/photos/{filename}', [CampusController::class, 'photo'])->name('items.photo');
    Route::post('/logout', [CampusController::class, 'logout'])->name('logout');
    Route::get('/my-items', [CampusController::class, 'mine'])->name('my-items');
    Route::get('/report', [CampusController::class, 'create'])->name('items.create');
    Route::post('/items', [CampusController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [CampusController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [CampusController::class, 'update'])->name('items.update');
    Route::patch('/items/{item}/status', [CampusController::class, 'close'])->name('items.close');
    Route::post('/items/{item}/claims', [CampusController::class, 'claim'])->name('claims.store');
    Route::patch('/claims/{claim}', [CampusController::class, 'decide'])->name('claims.decide');
    Route::get('/admin', [CampusController::class, 'admin'])->name('admin');
});
