<?php

namespace App\Livewire\Admin;

use App\Support\AppSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ProgettoPage extends Component
{
    public function render()
    {
        $entries = AppSettings::projectCostEntries();
        $base = AppSettings::projectBaseCost();
        $extra = collect($entries)->sum(fn ($e) => (float) ($e['amount'] ?? 0));

        return view('livewire.admin.progetto-page', [
            'appGuide' => AppSettings::appGuide(),
            'projectBaseCost' => $base,
            'costEntries' => $entries,
            'totalCost' => $base + $extra,
            'extraSum' => $extra,
        ]);
    }
}
