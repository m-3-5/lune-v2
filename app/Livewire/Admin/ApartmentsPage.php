<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\EntryVideo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ApartmentsPage extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $address = '';

    public string $whatsappNumber = '';

    public string $defaultCheckinHour = '16:00';

    public string $accessCode = '';

    public int $displayOrder = 0;

    public ?int $confirmingDeleteId = null;

    public function startCreate(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $apartment = Apartment::findOrFail($id);
        $this->editingId = $apartment->id;
        $this->name = $apartment->name;
        $this->address = (string) $apartment->address;
        $this->whatsappNumber = (string) $apartment->whatsapp_number;
        $this->defaultCheckinHour = substr((string) $apartment->default_checkin_hour, 0, 5) ?: '16:00';
        $this->accessCode = (string) $apartment->access_code;
        $this->displayOrder = (int) $apartment->display_order;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:120',
            'address' => 'nullable|string|max:255',
            'whatsappNumber' => 'nullable|string|max:40',
            'defaultCheckinHour' => 'required|date_format:H:i',
            'accessCode' => 'nullable|string|max:60',
            'displayOrder' => 'integer|min:0|max:255',
        ]);

        $data = [
            'name' => $this->name,
            'address' => $this->address ?: null,
            'whatsapp_number' => $this->whatsappNumber ?: null,
            'default_checkin_hour' => $this->defaultCheckinHour,
            'access_code' => $this->accessCode ?: null,
            'display_order' => $this->displayOrder,
        ];

        if ($this->editingId) {
            Apartment::findOrFail($this->editingId)->update($data);
            session()->flash('appartamenti_message', 'Appartamento aggiornato.');
        } else {
            $data['sku'] = $this->uniqueSku($this->name);
            Apartment::create($data);
            session()->flash('appartamenti_message', 'Appartamento creato.');
        }

        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(int $id): void
    {
        $apartment = Apartment::findOrFail($id);

        foreach (EntryVideo::where('apartment_id', $apartment->id)->get() as $video) {
            if ($video->video_path) {
                Storage::disk('public')->delete($video->video_path);
            }
        }

        $apartment->delete();

        $this->confirmingDeleteId = null;
        session()->flash('appartamenti_message', 'Appartamento eliminato (con prenotazioni e video collegati).');
    }

    protected function uniqueSku(string $name): string
    {
        $base = Str::slug($name) ?: 'appartamento';
        $sku = $base;
        $i = 2;

        while (Apartment::where('sku', $sku)->exists()) {
            $sku = "{$base}-{$i}";
            $i++;
        }

        return $sku;
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->address = '';
        $this->whatsappNumber = '';
        $this->defaultCheckinHour = '16:00';
        $this->accessCode = '';
        $this->displayOrder = 0;
    }

    public function render()
    {
        return view('livewire.admin.apartments-page', [
            'apartments' => Apartment::query()
                ->withCount('reservations')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
