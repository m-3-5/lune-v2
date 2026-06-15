<?php

namespace App\Livewire\Admin\Notifiche;

use App\Livewire\Admin\Concerns\ConfiguresMailChannel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CanaleEmailPage extends Component
{
    use ConfiguresMailChannel;

    public function mount(): void
    {
        $this->mountMailChannel();
    }

    public function render()
    {
        return view('livewire.admin.notifiche.canale-email-page', $this->mailChannelViewData());
    }
}
