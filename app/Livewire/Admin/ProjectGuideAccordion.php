<?php

namespace App\Livewire\Admin;

use App\Models\DevelopmentItem;
use App\Services\DevelopmentTaskNotifier;
use App\Support\ProjectGuide;
use Livewire\Component;

class ProjectGuideAccordion extends Component
{
    public ?string $expandedId = null;

    public ?string $askingSectionId = null;

    public string $askQuestion = '';

    public function toggleSection(string $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;

        if ($this->expandedId !== $this->askingSectionId) {
            $this->askingSectionId = null;
            $this->askQuestion = '';
        }
    }

    public function startAsk(string $sectionId): void
    {
        $section = ProjectGuide::findSection($sectionId);

        if ($section === null) {
            return;
        }

        $this->expandedId = $sectionId;
        $this->askingSectionId = $sectionId;
        $this->askQuestion = '';
    }

    public function cancelAsk(): void
    {
        $this->askingSectionId = null;
        $this->askQuestion = '';
    }

    public function submitAsk(): void
    {
        $this->validate([
            'askQuestion' => 'required|string|min:10|max:5000',
            'askingSectionId' => 'required|string',
        ]);

        $section = ProjectGuide::findSection($this->askingSectionId);

        if ($section === null) {
            return;
        }

        $body = "Argomento guida: {$section['title']} (id: {$section['id']})\n\n"
            ."Domanda di Serenella:\n{$this->askQuestion}";

        $item = DevelopmentItem::create([
            'type' => DevelopmentItem::TYPE_SERENELLA_REQUEST,
            'status' => DevelopmentItem::STATUS_OPEN,
            'title' => 'Guida: '.$section['title'],
            'body' => $body,
            'author' => 'serenella',
        ]);

        app(DevelopmentTaskNotifier::class)->itemCreated($item);

        $this->askingSectionId = null;
        $this->askQuestion = '';

        session()->flash('guide_message', 'Domanda inviata — il team riceve notifica su Telegram e push admin.');
        $this->dispatch('guide-ticket-created', itemId: $item->id);
    }

    public function render()
    {
        return view('livewire.admin.project-guide-accordion', [
            'sections' => ProjectGuide::sections(),
        ]);
    }
}
