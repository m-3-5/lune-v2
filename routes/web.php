<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckfrontWebhookController;
use App\Http\Controllers\CheckinController;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

// Rotta Webhook per Checkfront
Route::post('/webhook/checkfront', [CheckfrontWebhookController::class, 'handle']);

// La porta d'ingresso per l'ospite (Super-Lucchetto)
Route::get('/checkin/{token}', [CheckinController::class, 'show'])->name('checkin.show');


Route::get('/checkin/{token}/documents', [App\Http\Controllers\CheckinController::class, 'documents'])->name('checkin.documents');


Volt::route('/test-ia', 'gemini-test');