<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckfrontWebhookController;
use App\Http\Controllers\CheckinController;
use Livewire\Volt\Volt;
use App\Livewire\Admin\ReservationsModule;
use App\Livewire\Admin\DettaglioArrivo;

Route::get('/', function () {
    return view('welcome');
});

// Rotta Webhook per Checkfront
Route::post('/webhook/checkfront', [CheckfrontWebhookController::class, 'handle']);

// La porta d'ingresso per l'ospite (Super-Lucchetto)
Route::get('/checkin/{token}', [CheckinController::class, 'show'])->name('checkin.show');


Route::get('/checkin/{token}/documents', [App\Http\Controllers\CheckinController::class, 'documents'])->name('checkin.documents');

// Rotta per la Dashboard di Serenella
Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// Rotte per i Moduli Admin di Serenella
Route::prefix('admin')->group(function () {
    Route::get('/arrivi', function () { return view('admin.arrivi'); })->name('admin.arrivi');
    Route::get('/video', function () { return view('admin.video'); })->name('admin.video');
    Route::get('/contratti', function () { return view('admin.contratti'); })->name('admin.contratti');
    Route::get('/configura', function () { return view('admin.configura'); })->name('admin.configura');
});

// Rotta per il modulo di controllo documenti (Livewire)
Route::get('/admin/arrivi/{id}', DettaglioArrivo::class)->name('admin.arrivi.show');