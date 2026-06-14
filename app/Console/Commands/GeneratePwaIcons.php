<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class GeneratePwaIcons extends Command
{
    protected $signature = 'jlune:pwa-icons {--check : Mostra diagnostica icone}';

    protected $description = 'Genera icone PWA Jlune (180/192/512) per admin e ospite';

    /** @var list<int> */
    protected array $sizes = [180, 192, 512];

    /** @var list<string> */
    protected array $channels = ['admin', 'guest'];

    public function handle(): int
    {
        if ($this->option('check')) {
            return $this->runCheck();
        }

        if (! extension_loaded('gd')) {
            $this->error('Estensione PHP GD richiesta.');

            return self::FAILURE;
        }

        $dir = public_path('icons');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($this->channels as $channel) {
            $source = $this->resolveSource($dir, $channel);
            if (! $source) {
                $this->warn("Sorgente non trovata per {$channel}. Metti public/icons/source/{$channel}.png");

                continue;
            }

            $this->info("Sorgente {$channel}: ".basename($source));
            foreach ($this->sizes as $size) {
                $target = $dir.'/'.$channel.'-'.$size.'.png';
                $this->buildIcon($source, $target, $size, $channel);
                $this->line('  → '.$channel.'-'.$size.'.png');
            }
        }

        $this->fixPermissions($dir);
        $this->newLine();
        $this->info('Fatto. URL test: '.url('/pwa-icons/admin-192.png'));

        return $this->runCheck();
    }

    protected function resolveSource(string $dir, string $channel): ?string
    {
        $candidates = [
            $dir.'/source/'.$channel.'.png',
            $dir.'/source/'.$channel.'.jpg',
            $dir.'/'.$channel.'-512.png',
            $dir.'/'.$channel.'-192.png',
        ];

        foreach ($candidates as $path) {
            if (is_file($path) && filesize($path) > 100) {
                return $path;
            }
        }

        return null;
    }

    protected function buildIcon(string $source, string $target, int $size, string $channel): void
    {
        $src = $this->loadImage($source);
        if (! $src) {
            return;
        }

        $bounds = $this->findContentBounds($src);
        if (! $bounds) {
            imagedestroy($src);

            return;
        }

        [$minX, $minY, $maxX, $maxY] = $bounds;
        $cropW = $maxX - $minX + 1;
        $cropH = $maxY - $minY + 1;

        $cropped = imagecreatetruecolor($cropW, $cropH);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefill($cropped, 0, 0, $transparent);
        imagecopy($cropped, $src, 0, 0, $minX, $minY, $cropW, $cropH);
        imagedestroy($src);

        $bg = $channel === 'admin'
            ? [15, 23, 42]
            : [255, 255, 255];

        $out = imagecreatetruecolor($size, $size);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        $bgColor = imagecolorallocate($out, $bg[0], $bg[1], $bg[2]);
        imagefill($out, 0, 0, $bgColor);

        $padding = (int) round($size * 0.08);
        $inner = $size - ($padding * 2);
        $scale = min($inner / $cropW, $inner / $cropH);
        $destW = (int) round($cropW * $scale);
        $destH = (int) round($cropH * $scale);
        $destX = (int) round(($size - $destW) / 2);
        $destY = (int) round(($size - $destH) / 2);

        imagecopyresampled($out, $cropped, $destX, $destY, 0, 0, $destW, $destH, $cropW, $cropH);
        imagedestroy($cropped);

        imagepng($out, $target, 6);
        imagedestroy($out);
    }

    protected function loadImage(string $path): ?\GdImage
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path) ?: null,
            'webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            default => @imagecreatefrompng($path) ?: null,
        };
    }

    /** @return array{0:int,1:int,2:int,3:int}|null */
    protected function findContentBounds(\GdImage $src): ?array
    {
        $width = imagesx($src);
        $height = imagesy($src);
        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (! $this->isContentPixel($src, $x, $y)) {
                    continue;
                }
                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX <= $minX || $maxY <= $minY) {
            return null;
        }

        return [$minX, $minY, $maxX, $maxY];
    }

    protected function isContentPixel(\GdImage $src, int $x, int $y): bool
    {
        $rgba = imagecolorat($src, $x, $y);
        $a = ($rgba >> 24) & 0x7F;
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;

        if ($a >= 100) {
            return false;
        }

        return ! ($r >= 245 && $g >= 245 && $b >= 245);
    }

    protected function runCheck(): int
    {
        $this->info('Diagnostica icone PWA');
        $rows = [];

        foreach ($this->channels as $channel) {
            foreach ($this->sizes as $size) {
                $name = $channel.'-'.$size.'.png';
                $path = public_path('icons/'.$name);
                $dim = is_file($path) ? @getimagesize($path) : false;
                $rows[] = [
                    $name,
                    $dim ? $dim[0].'×'.$dim[1] : '—',
                    is_file($path) ? number_format(filesize($path)).' B' : 'MANCANTE',
                    ($dim && $dim[0] === $size && $dim[1] === $size) ? 'OK' : 'NO',
                ];
            }
        }

        $this->table(['File', 'Pixel', 'Peso', 'Stato'], $rows);
        $this->line('Browser: '.url('/pwa-icons/admin-192.png'));
        $this->line('Route: '.(Route::has('pwa.icon') ? 'ok' : 'manca route:clear'));

        return self::SUCCESS;
    }

    protected function fixPermissions(string $dir): void
    {
        @chmod($dir, 0755);
        foreach (glob($dir.'/*.png') ?: [] as $file) {
            @chmod($file, 0644);
        }
    }
}
