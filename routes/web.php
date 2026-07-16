<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckfrontWebhookController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\EntryVideoController;
use App\Http\Controllers\MaintenanceAccessController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\PwaIconController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\SerenellaAccessController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TicketTrackingController;
use Livewire\Volt\Volt;
use App\Livewire\Admin\ContrattiPage;
use App\Livewire\Admin\DettaglioArrivo;
use App\Livewire\Admin\EntryVideosPage;
use App\Livewire\Admin\Notifiche\CanaleEmailPage;
use App\Livewire\Admin\Notifiche\CanalePushPage;
use App\Livewire\Admin\Notifiche\CanaleTelegramPage;
use App\Livewire\Admin\Notifiche\CanaleWhatsAppPage;
use App\Livewire\Admin\NotifichePage;
use App\Livewire\Admin\ProgettoPage;
use App\Livewire\Admin\ProvaPage;
use App\Livewire\Admin\ReservationsModule;
use App\Livewire\Admin\SviluppoPage;
use App\Livewire\Admin\TestoContrattoPage;

Route::get('/', function () {
    return view('welcome');
});

// Icone PWA — servite da Laravel (path /pwa-icons/ evita blocchi nginx su /icons/)
Route::get('/pwa-icons/{filename}', [PwaIconController::class, 'show'])
    ->where('filename', '[a-z0-9\-]+\.png')
    ->name('pwa.icon');

Route::get('/icons/{filename}', [PwaIconController::class, 'show'])
    ->where('filename', '[a-z0-9\-]+\.png');

// Web Push (PWA admin / ospite)
Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

// Rotta Webhook per Checkfront
Route::post('/webhook/checkfront', [CheckfrontWebhookController::class, 'handle']);

Route::post('/webhook/telegram/{secret?}', [TelegramWebhookController::class, 'handle'])->name('webhook.telegram');

// Ticket di assistenza — resta raggiungibile anche a sito in manutenzione (vedi AppServiceProvider).
Route::get('/assistenza', [SupportTicketController::class, 'show'])->name('assistenza');
Route::post('/assistenza', [SupportTicketController::class, 'store'])->name('assistenza.store');

// Pagina pubblica di tracciamento ticket (link personale nelle email)
Route::get('/ticket/{token}', [TicketTrackingController::class, 'show'])->name('ticket.show');
Route::post('/ticket/{token}/reply', [TicketTrackingController::class, 'reply'])->name('ticket.reply');

// Pagina pubblica del video di ingresso — raggiunta scansionando il QR fisico.
Route::get('/qr/{token}', [EntryVideoController::class, 'show'])->name('qr.show');

// Link personale "accesso Serenella" — rinnovato a ogni prenotazione reale.
// Chiede conferma email prima di attivare l'accesso permanente (non basta più il solo link).
// La rotta /accesso/verifica va registrata PRIMA di /accesso/{token} (jolly), altrimenti
// quest'ultima la intercetta trattando "verifica" come se fosse il token.
Route::get('/accesso/verifica', [SerenellaAccessController::class, 'verify'])->name('serenella.access.verify')->middleware('signed');
Route::get('/accesso/{token}', [SerenellaAccessController::class, 'confirm'])->name('serenella.access.confirm');
Route::post('/accesso/{token}/richiedi', [SerenellaAccessController::class, 'requestAccess'])->name('serenella.access.request');
Route::get('/entra', [SerenellaAccessController::class, 'enter'])->name('serenella.access.enter');

// Login diretto (email + password) nella pagina di manutenzione — alternativa al link.
Route::post('/accesso/login', [MaintenanceAccessController::class, 'login'])->name('maintenance.access.login');

// La porta d'ingresso per l'ospite (Super-Lucchetto)
Route::get('/checkin/{token}/manifest.webmanifest', [PwaManifestController::class, 'guest'])
    ->name('checkin.manifest');

Route::get('/checkin/{token}', [CheckinController::class, 'show'])->name('checkin.show');


Route::get('/checkin/{token}/documents', [App\Http\Controllers\CheckinController::class, 'documents'])->name('checkin.documents');

Route::get('/checkin/{token}/contract', [CheckinController::class, 'contract'])->name('checkin.contract');

// Rotta per la Dashboard di Serenella
Route::get('/admin/manifest.webmanifest', [PwaManifestController::class, 'admin'])
    ->name('admin.manifest');

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// Rotte per i Moduli Admin di Serenella
Route::prefix('admin')->group(function () {
    Route::get('/arrivi', function () { return view('admin.arrivi'); })->name('admin.arrivi');
    Route::get('/video', EntryVideosPage::class)->name('admin.video');
    Route::get('/contratti', ContrattiPage::class)->name('admin.contratti');
    Route::get('/contratti/{reservation}/pdf', function (\App\Models\Reservation $reservation) {
        abort_unless(
            $reservation->contract_pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($reservation->contract_pdf_path),
            404
        );

        return \Illuminate\Support\Facades\Storage::disk('local')->download(
            $reservation->contract_pdf_path,
            "contratto-{$reservation->booking_code}.pdf"
        );
    })->name('admin.contratti.pdf');
    Route::get('/testo-contratto', TestoContrattoPage::class)->name('admin.testo-contratto');
    Route::get('/progetto', ProgettoPage::class)->name('admin.progetto');
    Route::redirect('/canali', '/admin/notifiche')->name('admin.canali');
    Route::get('/notifiche', NotifichePage::class)->name('admin.notifiche');
    Route::get('/notifiche/email', CanaleEmailPage::class)->name('admin.notifiche.email');
    Route::get('/notifiche/whatsapp', CanaleWhatsAppPage::class)->name('admin.notifiche.whatsapp');
    Route::get('/notifiche/telegram', CanaleTelegramPage::class)->name('admin.notifiche.telegram');
    Route::get('/notifiche/push', CanalePushPage::class)->name('admin.notifiche.push');
    Route::get('/prova', ProvaPage::class)->name('admin.prova');
    Route::get('/sviluppo', SviluppoPage::class)->name('admin.sviluppo');
    Route::redirect('/configura', '/admin/notifiche');
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