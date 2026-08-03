<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AdminPasswordResetController;
use App\Http\Controllers\CheckfrontWebhookController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\EntryVideoController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\PwaIconController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TicketTrackingController;
use Illuminate\Support\Facades\Auth;
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

// Chi arriva sulla home nuda va reindirizzato dove ha davvero accesso:
// il team (già loggato) dritto in admin, chiunque altro su una pagina
// minimale che rimanda al proprio link di prenotazione.
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return view('welcome');
})->name('home');

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

// Ticket di assistenza — pubblico, nessun login richiesto.
Route::get('/assistenza', [SupportTicketController::class, 'show'])->name('assistenza');
Route::post('/assistenza', [SupportTicketController::class, 'store'])->name('assistenza.store');

// Pagina pubblica di tracciamento ticket (link personale nelle email)
Route::get('/ticket/{token}', [TicketTrackingController::class, 'show'])->name('ticket.show');
Route::post('/ticket/{token}/reply', [TicketTrackingController::class, 'reply'])->name('ticket.reply');

// Pagina pubblica del video di ingresso — raggiunta scansionando il QR fisico.
Route::get('/qr/{token}', [EntryVideoController::class, 'show'])->name('qr.show');

// Login team per l'area admin.
Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Password dimenticata. Il nome "password.reset" è quello atteso dalla notifica
// di reset di Laravel per costruire il link nell'email — non rinominarlo.
Route::get('/admin/password/forgot', [AdminPasswordResetController::class, 'showRequestForm'])->name('admin.password.request');
Route::post('/admin/password/forgot', [AdminPasswordResetController::class, 'sendResetLink'])->name('admin.password.email');
Route::get('/admin/password/reset/{token}', [AdminPasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/admin/password/reset', [AdminPasswordResetController::class, 'reset'])->name('admin.password.update');

// La porta d'ingresso per l'ospite (Super-Lucchetto)
Route::get('/checkin/{token}/manifest.webmanifest', [PwaManifestController::class, 'guest'])
    ->name('checkin.manifest');

Route::get('/checkin/{token}', [CheckinController::class, 'show'])->name('checkin.show');


Route::get('/checkin/{token}/documents', [App\Http\Controllers\CheckinController::class, 'documents'])->name('checkin.documents');

Route::get('/checkin/{token}/contract', [CheckinController::class, 'contract'])->name('checkin.contract');

Route::get('/checkin/{token}/elettrodomestici', [CheckinController::class, 'appliances'])->name('checkin.appliances');

// Manifest PWA admin — metadati pubblici, nessun dato sensibile.
Route::get('/admin/manifest.webmanifest', [PwaManifestController::class, 'admin'])
    ->name('admin.manifest');

// Tutta l'area admin richiede login.
Route::middleware('auth')->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

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
});
