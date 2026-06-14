<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PwaIconController extends Controller
{
    public function show(string $filename): BinaryFileResponse
    {
        if (! preg_match('/^[a-z0-9\-]+\.png$/', $filename)) {
            abort(404);
        }

        $path = public_path('icons/'.$filename);

        if (! is_file($path) || filesize($path) < 32) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}
