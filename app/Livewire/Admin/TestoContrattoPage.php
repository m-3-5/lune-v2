<?php

namespace App\Livewire\Admin;

use App\Models\Reservation;
use App\Services\ContractRenderService;
use App\Support\AppSettings;
use App\Support\ContractTemplates;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class TestoContrattoPage extends Component
{
    public string $locale = 'it';

    public string $bodyHtml = '';

    public bool $isCustom = false;

    public bool $showPreview = false;

    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->loadBody();
    }

    public function switchLocale(string $locale): void
    {
        if (! in_array($locale, ['it', 'en'], true)) {
            return;
        }

        $this->locale = $locale;
        $this->statusMessage = null;
        $this->loadBody();
        $this->dispatch('contract-body-loaded', html: $this->bodyHtml);
    }

    public function save(string $html): void
    {
        AppSettings::setContractBody($this->locale, $html);
        $this->loadBody();
        $this->statusMessage = 'Testo salvato: i prossimi contratti ('.strtoupper($this->locale).') useranno questa versione.';
    }

    public function restoreDefault(): void
    {
        AppSettings::setContractBody($this->locale, '');
        $this->loadBody();
        $this->dispatch('contract-body-loaded', html: $this->bodyHtml);
        $this->statusMessage = 'Ripristinato il testo predefinito ('.strtoupper($this->locale).').';
    }

    protected function loadBody(): void
    {
        $custom = AppSettings::contractBody($this->locale);
        $this->isCustom = $custom !== null;
        $this->bodyHtml = $custom ?? ContractTemplates::defaultBody($this->locale);
    }

    public function render(ContractRenderService $contracts)
    {
        $previewHtml = null;
        if ($this->showPreview) {
            $reservation = Reservation::whereNotNull('extracted_guests')->latest('id')->first()
                ?? Reservation::latest('id')->first();
            $previewHtml = $reservation ? $contracts->html($reservation, $this->locale) : null;
        }

        return view('livewire.admin.testo-contratto-page', [
            'previewHtml' => $previewHtml,
            'placeholders' => ContractTemplates::placeholders(),
        ])->title('Testo contratto');
    }
}
