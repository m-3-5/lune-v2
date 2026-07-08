<?php

namespace App\Livewire;

use App\Models\FaqEntry;
use Livewire\Component;

class FaqWidget extends Component
{
    public string $audience = 'admin';

    public ?string $reservationToken = null;

    public string $position = 'right';

    public bool $open = false;

    public string $query = '';

    public ?int $selectedId = null;

    public function mount(string $audience, ?string $reservationToken = null, string $position = 'right'): void
    {
        $this->audience = $audience;
        $this->reservationToken = $reservationToken;
        $this->position = $position;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function select(int $id): void
    {
        $this->selectedId = $this->selectedId === $id ? null : $id;
    }

    public function updatedQuery(): void
    {
        $this->selectedId = null;
    }

    public function resolveLink(FaqEntry $entry): ?string
    {
        if (! $entry->link) {
            return null;
        }

        if (! str_starts_with($entry->link, ':')) {
            return $entry->link;
        }

        return match ($entry->link) {
            ':documents' => $this->reservationToken ? route('checkin.documents', $this->reservationToken) : null,
            ':contract' => $this->reservationToken ? route('checkin.contract', $this->reservationToken) : null,
            ':assistenza' => route('assistenza'),
            default => null,
        };
    }

    public function render()
    {
        $entries = FaqEntry::query()->audience($this->audience)->orderBy('position')->get();

        $typed = trim($this->query);

        if ($typed === '') {
            $suggestions = $entries->take(6);
        } else {
            $suggestions = $entries
                ->map(fn ($e) => [$e, $e->relevanceFor($typed)])
                ->filter(fn ($pair) => $pair[1] > 0)
                ->sortByDesc(fn ($pair) => $pair[1])
                ->take(6)
                ->map(fn ($pair) => $pair[0]);
        }

        return view('livewire.faq-widget', [
            'suggestions' => $suggestions,
            'selected' => $this->selectedId ? $entries->firstWhere('id', $this->selectedId) : null,
        ]);
    }
}
