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
