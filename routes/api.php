<?php

use App\Http\Controllers\ReconciliationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/v1/reconcile/match-demo', [ReconciliationController::class, 'match']);