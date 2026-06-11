<?php

namespace App\Services;

use App\Models\GuestNotification;
use App\Models\Reservation;

class GuestNotificationService
{
    public const TYPE_PAYMENT_REQUIRED = 'payment_required';

    public const TYPE_DOCUMENTS_REQUIRED = 'documents_required';

    public const TYPE_DOCUMENTS_RECEIVED = 'documents_received';

    public const TYPE_DOCUMENTS_UNDER_REVIEW = 'documents_under_review';

    public const TYPE_DOCUMENTS_APPROVED = 'documents_approved';

    public const TYPE_DOCUMENTS_REJECTED = 'documents_rejected';

    public const TYPE_CONTRACT_READY = 'contract_ready';

    public const TYPE_CONTRACT_PENDING = 'contract_pending';

    public const TYPE_TAX_CODE_REQUIRED = 'tax_code_required';

    public const TYPE_CHECKOUT_REMINDER = 'checkout_reminder';

    public const TYPE_ARRIVAL_REMINDER = 'arrival_reminder';

    public function notify(
        Reservation $reservation,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        int $dedupeHours = 24
    ): ?GuestNotification {
        return app(ClientOutboundNotificationService::class)->deliver(
            $reservation,
            $type,
            $title,
            $body,
            $actionUrl,
            $dedupeHours
        );
    }

    /**
     * Creazione notifica in-app (solo se non in modalità costruzione).
     */
    public function createInApp(
        Reservation $reservation,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        int $dedupeHours = 24
    ): GuestNotification {
        if ($dedupeHours > 0) {
            $existing = GuestNotification::query()
                ->forReservation($reservation->id)
                ->where('type', $type)
                ->where('created_at', '>=', now()->subHours($dedupeHours))
                ->latest()
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return GuestNotification::create([
            'reservation_id' => $reservation->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl ?? $this->portalUrl($reservation),
        ]);
    }

    /**
     * Crea promemoria contestuali in base allo stato attuale (senza duplicare eventi recenti).
     */
    public function syncStatusNotifications(Reservation $reservation): void
    {
        $reservation->loadMissing('apartment');
        $token = $reservation->token;
        $docsUrl = route('checkin.documents', ['token' => $token]);
        $homeUrl = route('checkin.show', ['token' => $token]);

        if (! $reservation->is_paid) {
            $this->notify(
                $reservation,
                self::TYPE_PAYMENT_REQUIRED,
                'Completa il pagamento',
                'Per caricare i documenti e proseguire con il check-in, paga l\'acconto su Checkfront.',
                $reservation->checkfront_payment_url ?: $homeUrl,
                dedupeHours: 48,
            );

            return;
        }

        $hasDocuments = $reservation->guestDocuments()->exists();

        if (! $hasDocuments && $reservation->documents_submitted_at === null) {
            $this->notify(
                $reservation,
                self::TYPE_DOCUMENTS_REQUIRED,
                'Carica i documenti',
                'Inserisci documento d\'identità e codice fiscale (se italiano) per tutti gli ospiti.',
                $docsUrl,
                dedupeHours: 48,
            );

            return;
        }

        if ($reservation->hasDocumentsPendingReview()) {
            $this->notify(
                $reservation,
                self::TYPE_DOCUMENTS_UNDER_REVIEW,
                'Documenti in verifica',
                'Abbiamo ricevuto i tuoi file. Serenella li controllerà a breve.',
                $homeUrl,
                dedupeHours: 72,
            );

            return;
        }

        if ($reservation->documents_validated && ! $reservation->contract_ready_for_guest) {
            $this->notify(
                $reservation,
                self::TYPE_CONTRACT_PENDING,
                'Contratto in preparazione',
                'I documenti sono ok. Il contratto sarà disponibile dopo la verifica finale.',
                $homeUrl,
                dedupeHours: 48,
            );
        }

        if ($reservation->documents_validated && $reservation->contract_ready_for_guest && ! $reservation->contract_accepted) {
            if ($reservation->requiresTaxCodeForContract()) {
                $this->notify(
                    $reservation,
                    self::TYPE_TAX_CODE_REQUIRED,
                    'Inserisci il codice fiscale',
                    'Per firmare il contratto manca il codice fiscale di uno o più ospiti italiani. Inseriscilo nella pagina del contratto.',
                    route('checkin.contract', ['token' => $reservation->token]),
                    dedupeHours: 24,
                );
            } else {
                $this->notify(
                    $reservation,
                    self::TYPE_CONTRACT_READY,
                    'Firma il contratto',
                    'Il contratto è pronto: apri la sezione Firma Contratto dal menu.',
                    route('checkin.contract', ['token' => $reservation->token]),
                    dedupeHours: 24,
                );
            }
        }

        if ($reservation->check_out && now()->lessThan($reservation->check_out)) {
            $hoursToCheckout = now()->diffInHours($reservation->check_out, false);
            if ($hoursToCheckout <= 24 && $hoursToCheckout >= 0) {
                $this->notify(
                    $reservation,
                    self::TYPE_CHECKOUT_REMINDER,
                    'Promemoria check-out',
                    'Partenza prevista il '.$reservation->check_out->format('d/m/Y').' entro le 10:00. Consulta le istruzioni di uscita.',
                    $homeUrl,
                    dedupeHours: 24,
                );
            }
        }

        if ($reservation->check_in && now()->lessThan($reservation->check_in)) {
            $daysToArrival = now()->startOfDay()->diffInDays($reservation->check_in->startOfDay(), false);
            if ($daysToArrival >= 1 && $daysToArrival <= 3) {
                $this->notify(
                    $reservation,
                    self::TYPE_ARRIVAL_REMINDER,
                    'Il tuo arrivo si avvicina',
                    'Check-in il '.$reservation->check_in->format('d/m/Y').' dalle 16:00. Completa documenti e pagamento se non l\'hai già fatto.',
                    $homeUrl,
                    dedupeHours: 48,
                );
            }
        }
    }

    public function documentsReceived(Reservation $reservation): void
    {
        $this->notify(
            $reservation,
            self::TYPE_DOCUMENTS_RECEIVED,
            'Documenti inviati',
            'Ricevuto! Riceverai una notifica quando potrai firmare il contratto.',
            route('checkin.show', ['token' => $reservation->token]),
            dedupeHours: 0,
        );

        $this->notify(
            $reservation,
            self::TYPE_DOCUMENTS_UNDER_REVIEW,
            'Documenti in verifica',
            'Serenella sta controllando i file caricati.',
            route('checkin.show', ['token' => $reservation->token]),
            dedupeHours: 0,
        );
    }

    public function documentsApproved(Reservation $reservation): void
    {
        $this->notify(
            $reservation,
            self::TYPE_DOCUMENTS_APPROVED,
            'Documenti approvati',
            'Ottimo! Attendi che il contratto sia reso disponibile per la firma.',
            route('checkin.show', ['token' => $reservation->token]),
            dedupeHours: 0,
        );
    }

    public function documentsRejected(Reservation $reservation): void
    {
        $this->notify(
            $reservation,
            self::TYPE_DOCUMENTS_REJECTED,
            'Documenti da ricaricare',
            'Alcuni file non sono stati accettati. Carica di nuovo documenti leggibili.',
            route('checkin.documents', ['token' => $reservation->token]),
            dedupeHours: 0,
        );
    }

    public function contractReady(Reservation $reservation): void
    {
        $this->notify(
            $reservation,
            self::TYPE_CONTRACT_READY,
            'Contratto pronto per la firma',
            'Apri il menu e vai su Firma Contratto per completare il check-in.',
            route('checkin.contract', ['token' => $reservation->token]),
            dedupeHours: 0,
        );
    }

    public function paymentReminder(Reservation $reservation): void
    {
        $this->notify(
            $reservation,
            self::TYPE_PAYMENT_REQUIRED,
            'Pagamento in sospeso',
            'Ricorda di versare l\'acconto per sbloccare il caricamento documenti.',
            $reservation->checkfront_payment_url ?: route('checkin.show', ['token' => $reservation->token]),
            dedupeHours: 48,
        );
    }

    protected function portalUrl(Reservation $reservation): string
    {
        return route('checkin.show', ['token' => $reservation->token]);
    }
}
