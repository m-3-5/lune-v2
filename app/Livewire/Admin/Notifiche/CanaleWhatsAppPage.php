<?php

namespace App\Livewire\Admin\Notifiche;

use App\Livewire\Admin\Concerns\ConfiguresWhatsAppChannel;
use App\Support\AppSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CanaleWhatsAppPage extends Component
{
    use ConfiguresWhatsAppChannel;

    public function mount(): void
    {
        $this->mountWhatsAppChannel();
    }

    public function render()
    {
        return view('livewire.admin.notifiche.canale-whatsapp-page', array_merge(
            $this->whatsAppChannelViewData(),
            [
                'underConstruction' => AppSettings::underConstruction(),
                'whatsappProviderLabel' => AppSettings::whatsappProvider(),
            ],
        ));
    }
}
