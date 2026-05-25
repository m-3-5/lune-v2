<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature = 'jlune:pwa-icons';

    protected $description = 'Genera icone PWA 192/512 in public/icons (richiede GD)';

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
            $this->writeIcon($dir.'/icon-'.$size.'.png', $size);
        }

        $this->info('Icone create in public/icons/');

        return self::SUCCESS;
    }

    protected function writeIcon(string $path, int $size): void
    {
        $img = imagecreatetruecolor($size, $size);
        $indigo = imagecolorallocate($img, 79, 70, 229);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $indigo);

        $font = 5;
        $text = 'J';
        $tw = imagefontwidth($font) * strlen($text);
        $th = imagefontheight($font);
        imagestring($img, $font, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), $text, $white);

        imagepng($img, $path);
        imagedestroy($img);
    }
}
