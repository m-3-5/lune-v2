<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckfrontWebhookController;
use App\Http\Controllers\CheckinController;

Route::get('/', function () {
    return view('welcome');
});

// Rotta Webhook per Checkfront
Route::post('/webhook/checkfront', [CheckfrontWebhookController::class, 'handle']);

// La porta d'ingresso per l'ospite (Super-Lucchetto)
Route::get('/checkin/{token}', [CheckinController::class, 'show'])->name('checkin.show');
