<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckfrontWebhookController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\PushSubscriptionController;
use Livewire\Volt\Volt;
use App\Livewire\Admin\CanaliInvioPage;
use App\Livewire\Admin\DettaglioArrivo;
use App\Livewire\Admin\ProgettoPage;
use App\Livewire\Admin\ProvaPage;
use App\Livewire\Admin\ReservationsModule;
use App\Livewire\Admin\SviluppoPage;

Route::get('/', function () {
    return view('welcome');
});

// Web Push (PWA admin / ospite)
Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

// Rotta Webhook per Checkfront
Route::post('/webhook/checkfront', [CheckfrontWebhookController::class, 'handle']);

// La porta d'ingresso per l'ospite (Super-Lucchetto)
Route::get('/checkin/{token}', [CheckinController::class, 'show'])->name('checkin.show');


Route::get('/checkin/{token}/documents', [App\Http\Controllers\CheckinController::class, 'documents'])->name('checkin.documents');

Route::get('/checkin/{token}/contract', [CheckinController::class, 'contract'])->name('checkin.contract');

// Rotta per la Dashboard di Serenella
Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// Rotte per i Moduli Admin di Serenella
Route::prefix('admin')->group(function () {
    Route::get('/arrivi', function () { return view('admin.arrivi'); })->name('admin.arrivi');
    Route::get('/video', function () { return view('admin.video'); })->name('admin.video');
    Route::get('/contratti', function () { return view('admin.contratti'); })->name('admin.contratti');
    Route::get('/progetto', ProgettoPage::class)->name('admin.progetto');
    Route::get('/canali', CanaliInvioPage::class)->name('admin.canali');
    Route::get('/prova', ProvaPage::class)->name('admin.prova');
    Route::get('/sviluppo', SviluppoPage::class)->name('admin.sviluppo');
    Route::redirect('/configura', '/admin/canali');
});

// Rotta per il modulo di controllo documenti (Livewire)
Route::get('/admin/arrivi/{id}', DettaglioArrivo::class)->name('admin.arrivi.show');

Route::get('/admin/arrivi/{id}/export/{format}', function (int $id, string $format) {
    $reservation = \App\Models\Reservation::with('guestDocuments')->findOrFail($id);

    return app(\App\Services\GuestDataExportService::class)->download($reservation, $format);
})->where('format', 'json|csv|xml')->name('admin.arrivo.export');

Route::get('/admin/progetto/guida-document-ai', function () {
    $path = resource_path('guides/google-document-ai-setup-it.md');

    if (! is_file($path)) {
        abort(404);
    }

    return response()->download(
        $path,
        'jlune-istruzioni-google-document-ai.md',
        ['Content-Type' => 'text/markdown; charset=UTF-8']
    );
})->name('admin.guide.document-ai');