<?php

use App\Http\Controllers\Api\V1\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [SessionController::class, 'show'])->middleware('cache.headers:no_store')->name('api.v1.me');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('api.v1.logout');
});
