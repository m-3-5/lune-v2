<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Services\TestReservationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ManualReservationPage extends Component
{
    public int $apartmentId = 0;

    public string $guestName = '';

    public string $guestCognome = '';

    public string $guestEmail = '';

    public string $guestPhone = '';

    public string $checkIn = '';

    public string $checkOut = '';

    public int $adults = 1;

    public int $children = 0;

    public string $totalPrice = '';

    public bool $isPaid = true;

    public string $notes = '';

    public ?string $lastPortalUrl = null;

    public function mount(): void
    {
        $this->checkIn = now()->addDay()->format('Y-m-d');
        $this->checkOut = now()->addDays(4)->format('Y-m-d');
        $firstApartment = Apartment::query()->orderBy('display_order')->orderBy('name')->first();
        $this->apartmentId = $firstApartment?->id ?? 0;
    }

    public function create(TestReservationService $service): void
    {
        $this->validate([
            'apartmentId' => 'required|exists:apartments,id',
            'guestName' => 'required|string|max:120',
            'guestCognome' => 'nullable|string|max:120',
            'guestEmail' => 'nullable|email|max:255',
            'guestPhone' => 'nullable|string|max:40',
            'checkIn' => 'required|date',
            'checkOut' => 'required|date|after:checkIn',
            'adults' => 'required|integer|min:1|max:20',
            'children' => 'integer|min:0|max:20',
            'totalPrice' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $reservation = $service->create([
            'apartment_id' => $this->apartmentId,
            'guest_name' => $this->guestName,
            'guest_cognome' => $this->guestCognome,
            'guest_email' => $this->guestEmail,
            'guest_phone' => $this->guestPhone,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'adults' => $this->adults,
            'children' => $this->children,
            'is_paid' => $this->isPaid,
            'total_price' => $this->totalPrice !== '' ? $this->totalPrice : 100,
            'test_notes' => $this->notes,
        ], isTest: false);

        $this->lastPortalUrl = $reservation->guest_portal_url;
        $this->reset(['guestName', 'guestCognome', 'guestEmail', 'guestPhone', 'notes']);
        session()->flash('manual_message', 'Prenotazione creata. Condividi il link ospite qui sotto.');
    }

    public function render()
    {
        return view('livewire.admin.manual-reservation-page', [
            'apartments' => Apartment::query()->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }
}
