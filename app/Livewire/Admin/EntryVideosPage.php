<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\EntryVideo;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class EntryVideosPage extends Component
{
    use WithFileUploads;

    public int $apartmentId = 0;

    public string $category = 'ingresso';

    public string $newTitle = '';

    public string $newMode = 'file';

    public $newVideo;

    public string $newVideoUrl = '';

    public function mount(): void
    {
        $first = Apartment::query()->orderBy('display_order')->orderBy('name')->first();
        $this->apartmentId = $first?->id ?? 0;
    }

    public function addVideo(): void
    {
        $rules = [
            'apartmentId' => 'required|exists:apartments,id',
            'newTitle' => 'required|string|max:120',
        ];

        $rules = $this->newMode === 'file'
            ? $rules + ['newVideo' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm|max:51200']
            : $rules + ['newVideoUrl' => 'required|url|max:500'];

        $this->validate($rules);

        $path = $this->newMode === 'file' ? $this->newVideo->store('entry-videos', 'public') : null;
        $nextOrder = (int) EntryVideo::where('apartment_id', $this->apartmentId)
            ->where('category', $this->category)
            ->max('step_order') + 1;

        EntryVideo::create([
            'apartment_id' => $this->apartmentId,
            'category' => $this->category,
            'step_order' => $nextOrder,
            'title' => $this->newTitle,
            'video_path' => $path,
            'video_url' => $this->newMode === 'url' ? $this->newVideoUrl : null,
            'qr_token' => Str::random(24),
        ]);

        $this->newTitle = '';
        $this->newVideo = null;
        $this->newVideoUrl = '';
        session()->flash('video_message', 'Video aggiunto.');
    }

    public function moveUp(int $id): void
    {
        $this->swapOrder($id, -1);
    }

    public function moveDown(int $id): void
    {
        $this->swapOrder($id, 1);
    }

    protected function swapOrder(int $id, int $direction): void
    {
        $video = EntryVideo::findOrFail($id);
        $neighbor = EntryVideo::where('apartment_id', $video->apartment_id)
            ->where('category', $video->category)
            ->where('step_order', $direction < 0 ? '<' : '>', $video->step_order)
            ->orderBy('step_order', $direction < 0 ? 'desc' : 'asc')
            ->first();

        if (! $neighbor) {
            return;
        }

        [$a, $b] = [$video->step_order, $neighbor->step_order];
        $video->update(['step_order' => $b]);
        $neighbor->update(['step_order' => $a]);
    }

    public function deleteVideo(int $id): void
    {
        $video = EntryVideo::findOrFail($id);
        if ($video->video_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($video->video_path);
        }
        $video->delete();
        session()->flash('video_message', 'Video eliminato.');
    }

    public function render()
    {
        return view('livewire.admin.entry-videos-page', [
            'apartments' => Apartment::query()->orderBy('display_order')->orderBy('name')->get(),
            'videos' => EntryVideo::where('apartment_id', $this->apartmentId)
                ->where('category', $this->category)
                ->orderBy('step_order')
                ->get(),
        ]);
    }
}
