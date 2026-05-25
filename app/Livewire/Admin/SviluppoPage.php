<?php

namespace App\Livewire\Admin;

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
        ]);
    }
}
