<?php

namespace App\Livewire;

use App\Models\Reservation;
use App\Services\ContractRenderService;
use Livewire\Component;
use Livewire\WithFileUploads;

class GuestContract extends Component
{
    use WithFileUploads;

    public Reservation $reservation;

    /** @var array<int, string> */
    public array $taxCodes = [];

    public ?int $taxUploadSlot = null;

    public $taxUploadFile;

    public function mount(Reservation $reservation): void
    {
        $this->reservation = $reservation->load('apartment');
        foreach ($this->reservation->contractGuests() as $guest) {
            if ($guest['is_foreigner'] ?? false) {
                continue;
            }
            $slot = (int) ($guest['slot'] ?? 0);
            $this->taxCodes[$slot] = $guest['data']['tax_code'] ?? '';
        }
    }

    public function saveTaxCodes(): void
    {
        foreach ($this->taxCodes as $slot => $code) {
            $code = trim((string) $code);
            if ($code === '') {
                continue;
            }
            if (! preg_match('/^[A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z]$/i', $code)) {
                $this->addError("taxCodes.{$slot}", 'Codice fiscale non valido.');

                return;
            }
            $this->reservation->setGuestTaxCode((int) $slot, $code);
        }

        $this->reservation->refresh();
        session()->flash('success', 'Codice fiscale salvato.');
    }

    public function updatedTaxUploadFile(): void
    {
        if ($this->taxUploadSlot === null || ! $this->taxUploadFile) {
            return;
        }

        $path = $this->taxUploadFile->store('documents', 'public');
        $this->reservation->guestDocuments()->create([
            'guest_slot' => $this->taxUploadSlot,
            'guest_name' => collect($this->reservation->contractGuests())->firstWhere('slot', $this->taxUploadSlot)['name'] ?? "Ospite {$this->taxUploadSlot}",
            'is_foreigner' => false,
            'document_type' => 'tax_front',
            'file_path' => $path,
            'status' => 'pending',
        ]);

        $this->taxUploadFile = null;
        $this->taxUploadSlot = null;
        session()->flash('success', 'Tessera sanitaria caricata. Il gestore potrà verificarla.');
    }

    public function render(ContractRenderService $contracts)
    {
        $this->reservation->refresh();

        return view('livewire.guest-contract', [
            'missingTax' => $this->reservation->italianGuestsMissingTaxCode(),
            'contractHtml' => $this->reservation->contract_ready_for_guest
                ? $contracts->html($this->reservation)
                : null,
            'previews' => $this->reservation->extracted_guests
                ? $contracts->htmlBoth($this->reservation)
                : null,
        ]);
    }
}
