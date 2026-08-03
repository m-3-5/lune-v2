<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Models\GuestDocument;
use App\Models\Reservation;
use App\Services\TestReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Comando manuale (mai schedulato): svuota tutte le prenotazioni e i documenti reali
 * (con i file caricati e i PDF firmati) per lasciare il progetto come base pulita
 * riutilizzabile per un nuovo cliente. Opzionalmente crea una prenotazione demo.
 */
class ResetReservationsForTemplate extends Command
{
    protected $signature = 'reservations:reset-for-template
        {--dry-run : Mostra cosa verrebbe eliminato senza cambiare nulla}
        {--force : Salta la conferma interattiva}
        {--no-demo : Non creare la prenotazione dimostrativa dopo la pulizia}';

    protected $description = 'ATTENZIONE: elimina TUTTE le prenotazioni e i documenti ospite reali (con i file). Da eseguire manualmente.';

    public function handle(TestReservationService $tests): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $prefix = $dryRun ? '[DRY-RUN] ' : '';

        $reservations = Reservation::with('guestDocuments')->get();
        $totalDocs = GuestDocument::count();

        if ($reservations->isEmpty()) {
            $this->info('Nessuna prenotazione da eliminare.');
        } else {
            $this->warn("{$prefix}Verranno eliminate {$reservations->count()} prenotazioni e {$totalDocs} documenti ospite, inclusi i file caricati (documenti d'identità) e i PDF dei contratti firmati.");

            if (! $dryRun && ! $this->option('force') && ! $this->confirm('Azione NON reversibile. Confermi?')) {
                $this->info('Annullato.');

                return self::SUCCESS;
            }

            foreach ($reservations as $reservation) {
                foreach ($reservation->guestDocuments as $doc) {
                    if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                        $this->line("{$prefix}Rimuovo file documento: {$doc->file_path}");
                        if (! $dryRun) {
                            Storage::disk('public')->delete($doc->file_path);
                        }
                    }
                }

                if ($reservation->contract_pdf_path && Storage::disk('local')->exists($reservation->contract_pdf_path)) {
                    $this->line("{$prefix}Rimuovo PDF contratto: {$reservation->contract_pdf_path}");
                    if (! $dryRun) {
                        Storage::disk('local')->delete($reservation->contract_pdf_path);
                    }
                }

                $this->line("{$prefix}Elimino prenotazione {$reservation->booking_code}");
                if (! $dryRun) {
                    $reservation->delete();
                }
            }
        }

        if (! $this->option('no-demo')) {
            $apartment = Apartment::query()->orderBy('display_order')->orderBy('name')->first();

            if (! $apartment) {
                $this->warn('Nessun appartamento configurato: creane uno prima di generare la demo.');

                return self::SUCCESS;
            }

            if ($dryRun) {
                $this->info("{$prefix}Creerei una prenotazione dimostrativa su «{$apartment->name}».");

                return self::SUCCESS;
            }

            $demo = $tests->create([
                'apartment_id' => $apartment->id,
                'guest_name' => 'Ospite',
                'guest_cognome' => 'Demo',
                'guest_email' => 'demo@esempio.it',
                'check_in' => now()->addDays(2)->format('Y-m-d'),
                'check_out' => now()->addDays(5)->format('Y-m-d'),
                'adults' => 2,
                'is_paid' => true,
                'test_notes' => 'Prenotazione dimostrativa — mostra il funzionamento dell\'app.',
            ]);

            $this->info('Prenotazione demo creata su «'.$apartment->name.'»: '.$demo->guest_portal_url);
        }

        return self::SUCCESS;
    }
}
