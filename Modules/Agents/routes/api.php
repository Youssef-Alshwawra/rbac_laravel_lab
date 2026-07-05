<?php

use Illuminate\Support\Facades\Route;
use Modules\Agents\Http\Controllers\AgentsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('agents', AgentsController::class)->names('agents');
});
