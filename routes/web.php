<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReconciliationController;

Route::post('/v1/reconcile/match-demo', [ReconciliationController::class, 'match']);

Route::get('/demo', function () {
    return view('demo');
});
Route::get('/', function () {
    return view('landing');
});
