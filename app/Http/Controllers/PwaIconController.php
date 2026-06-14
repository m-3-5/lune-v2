<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PwaIconController extends Controller
{
    public function show(string $filename): Response
    {
        if (! preg_match('/^[a-z0-9\-]+\.png$/', $filename)) {
            abort(404);
        }

        $path = public_path('icons/'.$filename);

        if (! is_file($path) || filesize($path) < 32) {
            abort(404);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            abort(404);
        }

        return response($contents, 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => (string) strlen($contents),
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}
