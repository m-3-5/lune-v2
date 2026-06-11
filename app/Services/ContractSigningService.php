<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class ContractSigningService
{
    public function __construct(
        protected ContractRenderService $contracts,
        protected AdminNotificationService $adminNotifications,
        protected GuestEmailNotifier $guestEmails,
    ) {}

    /**
     * Registra la firma del contratto: timestamp, PDF, notifica admin
     * ed email all'ospite con il contratto allegato.
     *
     * @return array{success: bool, message: string}
     */
    public function sign(Reservation $reservation): array
    {
        if ($reservation->requiresTaxCodeForContract()) {
            return [
                'success' => false,
                'message' => 'Per firmare manca il codice fiscale di uno o più ospiti italiani. Inseriscilo nella sezione dedicata qui sopra.',
            ];
        }

        $reservation->forceFill([
            'contract_accepted' => true,
            'contract_accepted_at' => now(),
        ])->save();

        $pdfPath = null;
        try {
            $pdfPath = $this->contracts->savePdfSnapshot($reservation);
        } catch (\Throwable $e) {
            Log::error('Generazione PDF contratto fallita', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->adminNotifications->contractSigned($reservation);

        if ($pdfPath) {
            $this->guestEmails->send(
                $reservation,
                'Contratto firmato — copia in allegato',
                "Grazie! Il contratto della prenotazione {$reservation->booking_code} è stato firmato correttamente. In allegato trovi la tua copia in PDF.",
                route('checkin.show', ['token' => $reservation->token]),
                attachmentStoragePath: $pdfPath,
                attachmentName: "contratto-{$reservation->booking_code}.pdf",
            );
        }

        return [
            'success' => true,
            'message' => 'Contratto firmato.',
        ];
    }
}
