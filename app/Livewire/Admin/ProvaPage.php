<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\Reservation;
use App\Services\TestReservationService;
use App\Support\AppSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ProvaPage extends Component
{
    public bool $enabled = false;

    public int $testApartmentId = 0;

    public string $testGuestName = 'Mario';

    public string $testGuestCognome = 'Rossi';

    public string $testGuestEmail = '';

    public string $testGuestPhone = '';

    public string $testCheckIn = '';

    public string $testCheckOut = '';

    public int $testAdults = 2;

    public bool $testIsPaid = true;

    public ?string $lastTestPortalUrl = null;

    public function mount(): void
    {
        $this->enabled = AppSettings::testBookingsAdminEnabled();
        $this->testCheckIn = now()->addDays(3)->format('Y-m-d');
        $this->testCheckOut = now()->addDays(6)->format('Y-m-d');
        $firstApt = Apartment::query()->orderBy('display_order')->orderBy('name')->first();
        $this->testApartmentId = $firstApt?->id ?? 0;
    }

    public function toggleEnabled(): void
    {
        $this->enabled = ! $this->enabled;
        AppSettings::setTestBookingsAdminEnabled($this->enabled);
        session()->flash('prova_message', $this->enabled
            ? 'Creazione prove attivata. Ricorda di disattivarla quando hai finito.'
            : 'Creazione prove disattivata.');
    }

    public function createTestReservation(TestReservationService $tests): void
    {
        if (! $this->enabled) {
            session()->flash('prova_error', 'Attiva prima «Prova flusso» con l\'interruttore in alto.');

            return;
        }

        $this->validate([
            'testApartmentId' => 'required|exists:apartments,id',
            'testGuestName' => 'required|string|max:120',
            'testGuestCognome' => 'nullable|string|max:120',
            'testGuestEmail' => 'nullable|email|max:255',
            'testGuestPhone' => 'nullable|string|max:40',
            'testCheckIn' => 'required|date',
            'testCheckOut' => 'required|date|after:testCheckIn',
            'testAdults' => 'required|integer|min:1|max:20',
        ]);

        $reservation = $tests->create([
            'apartment_id' => $this->testApartmentId,
            'guest_name' => $this->testGuestName,
            'guest_cognome' => $this->testGuestCognome,
            'guest_email' => $this->testGuestEmail ?: null,
            'guest_phone' => $this->testGuestPhone ?: null,
            'check_in' => $this->testCheckIn,
            'check_out' => $this->testCheckOut,
            'adults' => $this->testAdults,
            'children' => 0,
            'is_paid' => $this->testIsPaid,
            'test_notes' => 'Creata da Prova flusso (admin).',
        ]);

        $this->lastTestPortalUrl = $reservation->guest_portal_url;
        session()->flash('prova_message', 'Prenotazione di prova creata. Apri il link ospite qui sotto.');
    }

    public function deleteTestReservation(int $id, TestReservationService $tests): void
    {
        if (! $this->enabled) {
            return;
        }

        $reservation = Reservation::findOrFail($id);
        $tests->delete($reservation);
        session()->flash('prova_message', 'Prenotazione di prova eliminata.');
    }

    public function render()
    {
        return view('livewire.admin.prova-page', [
            'apartments' => Apartment::query()->orderBy('display_order')->orderBy('name')->get(),
            'testReservations' => Reservation::query()
                ->test()
                ->with('apartment')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(),
        ]);
    }
}
