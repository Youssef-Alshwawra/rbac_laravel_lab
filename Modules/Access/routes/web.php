<?php

use Illuminate\Support\Facades\Route;
use Modules\Access\Http\Controllers\AccessController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('accesses', AccessController::class)->names('access');
});
