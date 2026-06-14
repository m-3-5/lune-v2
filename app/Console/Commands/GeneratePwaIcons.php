<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature = 'jlune:pwa-icons';

    protected $description = 'Genera icone PWA admin/guest in public/icons (richiede GD)';

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('Estensione PHP GD richiesta.');

            return self::FAILURE;
        }

        $dir = public_path('icons');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ([192, 512] as $size) {
            $this->writeAdminIcon($dir.'/admin-'.$size.'.png', $size);
            $this->writeGuestIcon($dir.'/guest-'.$size.'.png', $size);
            $this->writeAdminIcon($dir.'/icon-'.$size.'.png', $size);
        }

        $this->info('Icone create in public/icons/ (admin-*, guest-*, icon-*)');

        return self::SUCCESS;
    }

    protected function writeAdminIcon(string $path, int $size): void
    {
        $img = imagecreatetruecolor($size, $size);
        imagealphablending($img, true);

        $bg = imagecolorallocate($img, 15, 23, 42);
        imagefill($img, 0, 0, $bg);

        $teal = imagecolorallocate($img, 94, 234, 212);
        $white = imagecolorallocate($img, 255, 255, 255);
        $r = (int) ($size * 0.18);
        imagefilledellipse($img, (int) ($size * 0.35), (int) ($size * 0.38), $r * 2, $r * 2, $teal);
        imagefilledellipse($img, (int) ($size * 0.62), (int) ($size * 0.28), (int) ($r * 1.4), (int) ($r * 1.4), $teal);

        $this->drawLetter($img, 'J', $size, $white, 0.42);

        imagepng($img, $path);
        imagedestroy($img);
    }

    protected function writeGuestIcon(string $path, int $size): void
    {
        $img = imagecreatetruecolor($size, $size);
        imagealphablending($img, true);

        $bg = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bg);

        $teal = imagecolorallocate($img, 20, 184, 166);
        $light = imagecolorallocate($img, 153, 246, 228);
        $r = (int) ($size * 0.2);
        imagefilledellipse($img, (int) ($size * 0.32), (int) ($size * 0.36), $r * 2, $r * 2, $teal);
        imagefilledellipse($img, (int) ($size * 0.68), (int) ($size * 0.3), (int) ($r * 1.5), (int) ($r * 1.5), $light);

        $this->drawLetter($img, 'J', $size, imagecolorallocate($img, 15, 23, 42), 0.42);

        imagepng($img, $path);
        imagedestroy($img);
    }

    protected function drawLetter(\GdImage $img, string $letter, int $size, int $color, float $scale): void
    {
        $font = 5;
        $tw = imagefontwidth($font) * strlen($letter);
        $th = imagefontheight($font);
        $x = (int) (($size - $tw * $scale) / 2);
        $y = (int) (($size - $th * $scale) / 2);

        if ($scale >= 1) {
            imagestring($img, $font, $x, $y, $letter, $color);

            return;
        }

        imagestring($img, $font, $x, $y, $letter, $color);
    }
}
