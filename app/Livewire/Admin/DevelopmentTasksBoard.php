<?php

namespace App\Livewire\Admin;

use App\Models\DevelopmentItem;
use App\Models\DevelopmentReply;
use App\Services\DevelopmentTaskNotifier;
use Livewire\Component;

class DevelopmentTasksBoard extends Component
{
    public bool $developerMode = false;

    public string $filter = 'active';

    public ?int $expandedId = null;

    public string $newTitle = '';

    public string $newBody = '';

    public string $newType = DevelopmentItem::TYPE_SERENELLA_REQUEST;

    public string $replyBody = '';

    public string $testInstructions = '';

    public function mount(bool $developerMode = false): void
    {
        $this->developerMode = $developerMode;
        if ($developerMode) {
            $this->newType = DevelopmentItem::TYPE_PRODUCTION_TASK;
        }
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->expandedId = null;
    }

    public function toggleExpanded(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
        $this->replyBody = '';
    }

    public function createItem(): void
    {
        $this->validate([
            'newTitle' => 'required|string|max:255',
            'newBody' => 'nullable|string|max:5000',
        ]);

        $type = $this->developerMode
            ? $this->newType
            : DevelopmentItem::TYPE_SERENELLA_REQUEST;

        if (! $this->developerMode && $type !== DevelopmentItem::TYPE_SERENELLA_REQUEST) {
            return;
        }

        $item = DevelopmentItem::create([
            'type' => $type,
            'status' => DevelopmentItem::STATUS_OPEN,
            'title' => $this->newTitle,
            'body' => $this->newBody,
            'author' => $this->developerMode ? 'team' : 'serenella',
        ]);

        app(DevelopmentTaskNotifier::class)->itemCreated($item);

        $this->newTitle = '';
        $this->newBody = '';
        session()->flash('tasks_message', 'Voce aggiunta.');
    }

    public function setStatus(int $id, string $status): void
    {
        if (! $this->developerMode) {
            return;
        }

        if (! in_array($status, [
            DevelopmentItem::STATUS_OPEN,
            DevelopmentItem::STATUS_IN_PROGRESS,
            DevelopmentItem::STATUS_DONE,
        ], true)) {
            return;
        }

        if ($status === DevelopmentItem::STATUS_DONE) {
            $this->completeItem($id);

            return;
        }

        $item = DevelopmentItem::findOrFail($id);
        $previous = $item->status;
        $item->update(['status' => $status]);
        app(DevelopmentTaskNotifier::class)->statusChanged($item->fresh(), $previous);
    }

    public function deleteItem(int $id): void
    {
        if (! $this->developerMode) {
            return;
        }

        $item = DevelopmentItem::findOrFail($id);
        $item->replies()->delete();
        $item->delete();

        if ($this->expandedId === $id) {
            $this->expandedId = null;
        }

        session()->flash('tasks_message', 'Voce eliminata.');
    }

    public function completeItem(int $id): void
    {
        if (! $this->developerMode) {
            return;
        }

        $item = DevelopmentItem::findOrFail($id);
        $previous = $item->status;
        $item->update([
            'status' => DevelopmentItem::STATUS_DONE,
            'test_instructions' => $this->testInstructions ?: $item->test_instructions,
        ]);
        $this->testInstructions = '';
        app(DevelopmentTaskNotifier::class)->statusChanged($item->fresh(), $previous);
        session()->flash('tasks_message', 'Completata — notifica inviata.');
    }

    public function addReply(int $itemId): void
    {
        $this->validate(['replyBody' => 'required|string|max:5000']);

        $author = $this->developerMode ? 'team' : 'serenella';
        DevelopmentReply::create([
            'development_item_id' => $itemId,
            'author' => $author,
            'body' => $this->replyBody,
        ]);

        $item = DevelopmentItem::findOrFail($itemId);
        app(DevelopmentTaskNotifier::class)->replyAdded($item, $author, $this->replyBody);

        $this->replyBody = '';
        session()->flash('tasks_message', 'Risposta inviata.');
    }

    public function render()
    {
        $query = DevelopmentItem::query()->with('replies')->orderByDesc('updated_at');

        if ($this->filter === 'active') {
            $query->whereIn('status', [
                DevelopmentItem::STATUS_OPEN,
                DevelopmentItem::STATUS_IN_PROGRESS,
            ]);
        } elseif ($this->filter === 'done') {
            $query->where('status', DevelopmentItem::STATUS_DONE);
        } elseif ($this->filter === 'serenella') {
            $query->whereIn('type', [
                DevelopmentItem::TYPE_SERENELLA_REQUEST,
                DevelopmentItem::TYPE_QUESTION_FOR_SERENELLA,
            ]);
        } elseif ($this->filter === 'production') {
            $query->where('type', DevelopmentItem::TYPE_PRODUCTION_TASK);
        }

        return view('livewire.admin.development-tasks-board', [
            'items' => $query->get(),
        ]);
    }
}
