<?php

namespace App\Livewire\Admin;

use App\Models\Reservation;
use App\Services\ContractRenderService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ContrattiPage extends Component
{
    public ?string $statusMessage = null;

    public function regeneratePdf(int $reservationId, ContractRenderService $contracts): void
    {
        $reservation = Reservation::findOrFail($reservationId);

        try {
            $contracts->savePdfSnapshot($reservation);
            $this->statusMessage = "PDF rigenerato per {$reservation->booking_code}.";
        } catch (\Throwable $e) {
            $this->statusMessage = "Errore nella generazione del PDF: {$e->getMessage()}";
        }
    }

    public function render()
    {
        $signed = Reservation::query()
            ->where('contract_accepted', true)
            ->with('apartment')
            ->orderByDesc('contract_accepted_at')
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.contratti-page', [
            'signed' => $signed,
        ])->title('Contratti firmati');
    }
}
