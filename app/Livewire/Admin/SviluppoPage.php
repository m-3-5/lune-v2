<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\Reservation;
use App\Services\TestReservationService;
use App\Support\AppSettings;
use App\Support\JluneDeveloperAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class SviluppoPage extends Component
{
    public bool $unlocked = false;

    public string $devPassword = '';

    public bool $underConstruction = false;

    public string $adminEmailsText = '';

    public string $adminPhonesText = '';

    public string $appGuide = '';

    public float $projectBaseCost = 3800;

    /** @var array<int, array{label: string, amount: string, date: string}> */
    public array $costEntries = [];

    public string $newCostLabel = '';

    public string $newCostAmount = '';

    public string $newCostDate = '';

    public int $testApartmentId = 0;

    public string $testGuestName = 'Mario';

    public string $testGuestCognome = 'Rossi';

    public string $testGuestEmail = 'test@jlune.local';

    public string $testGuestPhone = '+393331112233';

    public string $testCheckIn = '';

    public string $testCheckOut = '';

    public int $testAdults = 2;

    public int $testChildren = 0;

    public bool $testIsPaid = true;

    /** @var array<int, string> */
    public array $testExtras = [];

    public string $testNotes = '';

    public ?string $lastTestPortalUrl = null;

    public function mount(): void
    {
        $this->unlocked = JluneDeveloperAccess::isGranted();

        if ($this->unlocked) {
            $this->loadDevSettings();
        }
    }

    public function attemptUnlock(): void
    {
        $this->validate(['devPassword' => 'required|string']);

        if (! JluneDeveloperAccess::check($this->devPassword)) {
            $this->addError('devPassword', 'Password non valida.');

            return;
        }

        JluneDeveloperAccess::grant();
        $this->unlocked = true;
        $this->devPassword = '';
        $this->loadDevSettings();
    }

    public function lock(): void
    {
        JluneDeveloperAccess::revoke();
        $this->unlocked = false;
    }

    protected function loadDevSettings(): void
    {
        $this->underConstruction = AppSettings::underConstruction();
        $this->adminEmailsText = implode("\n", AppSettings::adminEmails());
        $this->adminPhonesText = implode("\n", AppSettings::adminPhones());
        $this->appGuide = AppSettings::appGuide();
        $this->projectBaseCost = AppSettings::projectBaseCost();
        $this->costEntries = collect(AppSettings::projectCostEntries())
            ->map(fn ($e) => [
                'label' => (string) ($e['label'] ?? ''),
                'amount' => (string) ($e['amount'] ?? ''),
                'date' => (string) ($e['date'] ?? ''),
            ])
            ->all();
        $this->newCostDate = now()->format('Y-m-d');
        $this->testCheckIn = now()->addDays(3)->format('Y-m-d');
        $this->testCheckOut = now()->addDays(6)->format('Y-m-d');
        $firstApt = Apartment::query()->orderBy('display_order')->orderBy('name')->first();
        $this->testApartmentId = $firstApt?->id ?? 0;
    }

    public function createTestReservation(TestReservationService $tests): void
    {
        if (! config('jlune.test_bookings_enabled')) {
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
            'testChildren' => 'integer|min:0|max:20',
            'testNotes' => 'nullable|string|max:5000',
        ]);

        $reservation = $tests->create([
            'apartment_id' => $this->testApartmentId,
            'guest_name' => $this->testGuestName,
            'guest_cognome' => $this->testGuestCognome,
            'guest_email' => $this->testGuestEmail,
            'guest_phone' => $this->testGuestPhone,
            'check_in' => $this->testCheckIn,
            'check_out' => $this->testCheckOut,
            'adults' => $this->testAdults,
            'children' => $this->testChildren,
            'is_paid' => $this->testIsPaid,
            'test_notes' => $this->testNotes,
        ], $this->testExtras);

        $this->lastTestPortalUrl = $reservation->guest_portal_url;
        session()->flash('dev_message', 'Prenotazione TEST creata. Apri il link ospite qui sotto.');
    }

    public function deleteTestReservation(int $id, TestReservationService $tests): void
    {
        $reservation = Reservation::findOrFail($id);
        $tests->delete($reservation);
        session()->flash('dev_message', 'Prenotazione TEST eliminata.');
    }

    public function toggleConstruction(): void
    {
        $this->underConstruction = ! $this->underConstruction;
        AppSettings::setUnderConstruction($this->underConstruction);
        session()->flash('dev_message', $this->underConstruction
            ? 'App in costruzione attivata.'
            : 'App in costruzione disattivata.');
    }

    public function saveContacts(): void
    {
        AppSettings::set('admin_emails', $this->linesToArray($this->adminEmailsText));
        AppSettings::set('admin_phones', $this->linesToArray($this->adminPhonesText));
        session()->flash('dev_message', 'Contatti salvati.');
    }

    public function saveGuide(): void
    {
        AppSettings::set('app_guide', $this->appGuide);
        session()->flash('dev_message', 'Guida aggiornata (visibile in Progetto).');
    }

    public function saveCosts(): void
    {
        AppSettings::set('project_base_cost', (float) $this->projectBaseCost);
        AppSettings::set('project_cost_entries', $this->normalizedCostEntries());
        session()->flash('dev_message', 'Costi aggiornati.');
    }

    public function addCostEntry(): void
    {
        $this->validate([
            'newCostLabel' => 'required|string|max:255',
            'newCostAmount' => 'required|numeric|min:0',
            'newCostDate' => 'required|date',
        ]);

        $this->costEntries[] = [
            'label' => $this->newCostLabel,
            'amount' => $this->newCostAmount,
            'date' => $this->newCostDate,
        ];

        AppSettings::set('project_cost_entries', $this->normalizedCostEntries());
        $this->newCostLabel = '';
        $this->newCostAmount = '';
        $this->newCostDate = now()->format('Y-m-d');
        session()->flash('dev_message', 'Voce costo aggiunta.');
    }

    public function removeCostEntry(int $index): void
    {
        unset($this->costEntries[$index]);
        $this->costEntries = array_values($this->costEntries);
        AppSettings::set('project_cost_entries', $this->normalizedCostEntries());
    }

    /**
     * @return array<int, array{label: string, amount: float, date: string}>
     */
    protected function normalizedCostEntries(): array
    {
        return collect($this->costEntries)
            ->filter(fn ($e) => filled($e['label'] ?? null))
            ->map(fn ($e) => [
                'label' => (string) $e['label'],
                'amount' => (float) $e['amount'],
                'date' => (string) ($e['date'] ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function linesToArray(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: [])));
    }

    public function render()
    {
        if (! $this->unlocked) {
            return view('livewire.admin.sviluppo-login');
        }

        $extra = collect($this->costEntries)->sum(fn ($e) => (float) ($e['amount'] ?? 0));

        return view('livewire.admin.sviluppo-page', [
            'totalCost' => (float) $this->projectBaseCost + $extra,
            'extraSum' => $extra,
            'testBookingsEnabled' => (bool) config('jlune.test_bookings_enabled'),
            'apartments' => Apartment::query()->orderBy('display_order')->orderBy('name')->get(),
            'availableExtras' => TestReservationService::availableExtras(),
            'testReservations' => config('jlune.test_bookings_enabled')
                ? Reservation::query()->test()->with('apartment')->orderByDesc('created_at')->limit(15)->get()
                : collect(),
        ]);
    }
}
