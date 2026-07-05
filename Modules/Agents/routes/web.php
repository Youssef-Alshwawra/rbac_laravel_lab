<?php

use Illuminate\Support\Facades\Route;
use Modules\Agents\Http\Controllers\AgentsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('agents', AgentsController::class)->names('agents');
});
