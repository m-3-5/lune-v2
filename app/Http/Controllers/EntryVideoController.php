<?php

namespace App\Http\Controllers;

use App\Models\EntryVideo;
use Illuminate\View\View;

class EntryVideoController extends Controller
{
    /** Pagina pubblica raggiunta scansionando il QR fisico vicino al passaggio di ingresso. */
    public function show(string $token): View
    {
        $video = EntryVideo::where('qr_token', $token)->with('apartment')->firstOrFail();
        $allSteps = EntryVideo::where('apartment_id', $video->apartment_id)->orderBy('step_order')->get();

        return view('entry-video-show', compact('video', 'allSteps'));
    }
}
