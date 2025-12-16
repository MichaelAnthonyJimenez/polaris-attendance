<?php

use App\Http\Controllers\Api\OfflineSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::post('/offline/attendance', OfflineSyncController::class)->name('api.offline.attendance');
});

