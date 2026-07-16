<?php

namespace App\Models;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EntryVideo extends Model
{
    protected $guarded = [];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function videoUrl(): ?string
    {
        return $this->video_path ? Storage::disk('public')->url($this->video_path) : null;
    }

    /** Se il link (YouTube/Vimeo) può essere incorporato in un iframe, l'URL pronto per l'embed. */
    public function embedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        if (preg_match('#(?:youtu\.be/|youtube\.com/watch\?v=|youtube\.com/shorts/)([\w-]+)#', $this->video_url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}";
        }

        if (preg_match('#vimeo\.com/(\d+)#', $this->video_url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }

        return null;
    }

    public function qrUrl(): string
    {
        return route('qr.show', $this->qr_token);
    }

    public function qrDataUri(): string
    {
        $result = (new Builder())->build(
            writer: new SvgWriter(),
            data: $this->qrUrl(),
            size: 220,
            margin: 8,
        );

        return $result->getDataUri();
    }
}
