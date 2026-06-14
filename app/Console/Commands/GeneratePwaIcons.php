<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeneratePwaIcons extends Command
{
    protected $signature = 'jlune:pwa-icons {--check : Mostra dove sono (o mancano) le icone}';

    protected $description = 'Genera icone PWA admin/guest in public/icons (richiede GD)';

    /** @var list<string> */
    protected array $files = [
        'admin-192.png',
        'admin-512.png',
        'guest-192.png',
        'guest-512.png',
        'icon-192.png',
        'icon-512.png',
    ];

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

        $this->importMisplacedIcons($dir);

        foreach ([192, 512] as $size) {
            $this->writeAdminIcon($dir.'/admin-'.$size.'.png', $size);
            $this->writeGuestIcon($dir.'/guest-'.$size.'.png', $size);
            $this->writeAdminIcon($dir.'/icon-'.$size.'.png', $size);
        }

        $this->newLine();
        $this->info('Icone scritte in: '.$dir);
        $this->line('URL test: '.url('/icons/admin-192.png'));
        $this->runCheck();

        return self::SUCCESS;
    }

    protected function runCheck(): int
    {
        $this->info('Diagnostica icone PWA');
        $this->line('public_path(): '.public_path());
        $this->line('base_path(): '.base_path());
        $this->newLine();

        $rows = [];
        foreach ($this->files as $name) {
            $path = public_path('icons/'.$name);
            $rows[] = [
                $name,
                $path,
                is_file($path) ? number_format(filesize($path)).' B' : 'MANCANTE',
                is_file($path) && filesize($path) > 32 ? 'OK' : 'NO',
            ];
        }

        $this->table(['File', 'Percorso', 'Dimensione', 'Stato'], $rows);
        $this->line('Apri nel browser: '.url('/icons/admin-192.png'));

        return self::SUCCESS;
    }

    protected function importMisplacedIcons(string $targetDir): void
    {
        $candidates = array_unique(array_filter([
            base_path('icons'),
            base_path('public/icons'),
            dirname(public_path()).'/icons',
        ]));

        foreach ($candidates as $source) {
            if (! is_dir($source) || realpath($source) === realpath($targetDir)) {
                continue;
            }

            foreach (glob($source.'/*.png') ?: [] as $file) {
                $name = basename($file);
                if (! in_array($name, $this->files, true)) {
                    continue;
                }
                $dest = $targetDir.'/'.$name;
                if (! is_file($dest) || filesize($dest) < filesize($file)) {
                    File::copy($file, $dest);
                    $this->warn('Copiato da '.$source.' → '.$name);
                }
            }
        }
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

        $this->drawLetter($img, 'J', $size, $white);

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

        $this->drawLetter($img, 'J', $size, imagecolorallocate($img, 15, 23, 42));

        imagepng($img, $path);
        imagedestroy($img);
    }

    protected function drawLetter(\GdImage $img, string $letter, int $size, int $color): void
    {
        $font = 5;
        $tw = imagefontwidth($font) * strlen($letter);
        $th = imagefontheight($font);
        imagestring($img, $font, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), $letter, $color);
    }
}
