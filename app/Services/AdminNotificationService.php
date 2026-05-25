<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Reservation;

class AdminNotificationService
{
    public const TYPE_DOCUMENTS_SUBMITTED = 'documents_submitted';

    public const TYPE_DOCUMENTS_APPROVED = 'documents_approved';

    public const TYPE_DOCUMENTS_REJECTED = 'documents_rejected';

    public const TYPE_EXTRACTION_DONE = 'extraction_done';

    public const TYPE_EXTRACTION_FAILED = 'extraction_failed';

    public const TYPE_CONTRACT_AUTHORIZED = 'contract_authorized';

    public const TYPE_BOOKING_SYNCED = 'booking_synced';

    public const TYPE_CLIENT_NOTIFICATION_PREVIEW = 'client_notification_preview';

    public function notify(
        string $type,
        Reservation $reservation,
        string $title,
        ?string $body = null,
        int $dedupeHours = 2
    ): AdminNotification {
        if ($dedupeHours > 0) {
            $exists = AdminNotification::query()
                ->where('type', $type)
                ->where('reservation_id', $reservation->id)
                ->where('created_at', '>=', now()->subHours($dedupeHours))
                ->exists();

            if ($exists) {
                $existing = AdminNotification::query()
                    ->where('type', $type)
                    ->where('reservation_id', $reservation->id)
                    ->latest()
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }
        }

        return AdminNotification::create([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'reservation_id' => $reservation->id,
        ]);
    }

    public function documentsSubmitted(Reservation $reservation): void
    {
        $name = $reservation->guestDisplayName();
        $apt = $reservation->apartment?->name ?? 'Appartamento';

        $this->notify(
            self::TYPE_DOCUMENTS_SUBMITTED,
            $reservation,
            'Documenti da verificare',
            "{$name} · {$apt} — l'ospite ha caricato i documenti.",
        );
    }

    public function documentsApproved(Reservation $reservation): void
    {
        $this->notify(
            self::TYPE_DOCUMENTS_APPROVED,
            $reservation,
            'Documenti approvati',
            "Prenotazione {$reservation->booking_code}: puoi estrarre i dati con l'IA.",
            dedupeHours: 0,
        );
    }

    public function documentsRejected(Reservation $reservation): void
    {
        $this->notify(
            self::TYPE_DOCUMENTS_REJECTED,
            $reservation,
            'Documenti rifiutati',
            "Prenotazione {$reservation->booking_code}: l'ospite dovrà ricaricare.",
            dedupeHours: 0,
        );
    }

    public function extractionDone(Reservation $reservation, bool $success, ?string $detail = null): void
    {
        if ($success) {
            $this->notify(
                self::TYPE_EXTRACTION_DONE,
                $reservation,
                'Estrazione IA completata',
                $detail ?? "Controlla i dati e autorizza il contratto per {$reservation->booking_code}.",
                dedupeHours: 0,
            );

            return;
        }

        $this->notify(
            self::TYPE_EXTRACTION_FAILED,
            $reservation,
            'Estrazione IA non riuscita',
            $detail ?? 'Verifica i documenti o inserisci i dati manualmente.',
            dedupeHours: 0,
        );
    }

    public function contractAuthorized(Reservation $reservation): void
    {
        $this->notify(
            self::TYPE_CONTRACT_AUTHORIZED,
            $reservation,
            'Contratto autorizzato per ospite',
            "{$reservation->booking_code}: l'ospite può firmare dal link check-in.",
            dedupeHours: 0,
        );
    }

    public function bookingSynced(Reservation $reservation): void
    {
        $this->notify(
            self::TYPE_BOOKING_SYNCED,
            $reservation,
            'Nuova prenotazione Checkfront',
            "{$reservation->guestDisplayName()} · {$reservation->apartment?->name} ({$reservation->booking_code})",
            dedupeHours: 6,
        );
    }

    /**
     * Anteprima di una notifica che in produzione andrebbe al cliente (modalità costruzione).
     */
    public function clientNotificationPreview(
        Reservation $reservation,
        string $clientTitle,
        string $previewBody,
    ): AdminNotification {
        return $this->notify(
            self::TYPE_CLIENT_NOTIFICATION_PREVIEW,
            $reservation,
            '[Anteprima cliente] '.$clientTitle,
            $previewBody,
            dedupeHours: 0,
        );
    }
}
