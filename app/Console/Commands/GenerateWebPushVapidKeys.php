<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateWebPushVapidKeys extends Command
{
    protected $signature = 'jlune:vapid-keys';

    protected $description = 'Genera chiavi VAPID per Web Push (incolla nel .env)';

    public function handle(): int
    {
        if (! extension_loaded('openssl')) {
            $this->error('Estensione OpenSSL richiesta.');

            return self::FAILURE;
        }

        $opensslCnf = $this->resolveOpenSslConfigPath();
        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ];
        if ($opensslCnf !== null) {
            $config['config'] = $opensslCnf;
        }

        $key = openssl_pkey_new($config);
        if ($key === false) {
            $this->error('Generazione chiave fallita.');
            if ($opensslCnf === null) {
                $this->line('OpenSSL non trova openssl.cnf. Su Herd di solito esiste in:');
                $this->line('  C:\\Users\\<tu>\\.config\\herd\\openssl.cnf');
            }
            while ($msg = openssl_error_string()) {
                $this->line('OpenSSL: '.$msg);
            }

            if (class_exists(\Minishlink\WebPush\VAPID::class)) {
                $this->warn('OpenSSL nativo fallito, provo libreria web-push…');

                return $this->tryWebPushLibrary();
            }

            return self::FAILURE;
        }

        openssl_pkey_export($key, $privatePem, null, $opensslCnf ? ['config' => $opensslCnf] : []);
        $details = openssl_pkey_get_details($key);
        $publicKey = $details['key'] ?? '';

        $privateDer = $this->pemToDer($privatePem);
        $publicDer = $this->extractPublicDer($publicKey);

        $this->printEnvLines($this->base64UrlEncode($publicDer), $this->base64UrlEncode($privateDer));

        return self::SUCCESS;
    }

    protected function tryWebPushLibrary(): int
    {
        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();

        $this->printEnvLines($keys['publicKey'], $keys['privateKey']);

        return self::SUCCESS;
    }

    protected function resolveOpenSslConfigPath(): ?string
    {
        $candidates = array_filter([
            getenv('OPENSSL_CONF') ?: null,
            'C:\\Users\\'.(getenv('USERNAME') ?: getenv('USER') ?: '').'\\.config\\herd\\openssl.cnf',
            'C:\\Users\\'.(getenv('USERNAME') ?: getenv('USER') ?: '').'\\.config\\herd\\bin\\php84\\extras\\ssl\\openssl.cnf',
            php_ini_loaded_file() ? dirname((string) php_ini_loaded_file()).'\\extras\\ssl\\openssl.cnf' : null,
            'C:\\Program Files\\Common Files\\SSL\\openssl.cnf',
        ]);

        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function printEnvLines(string $publicKey, string $privateKey): void
    {
        $this->info('Aggiungi al .env:');
        $this->newLine();
        $this->line('WEBPUSH_ENABLED=true');
        $this->line('WEBPUSH_VAPID_PUBLIC_KEY='.$publicKey);
        $this->line('WEBPUSH_VAPID_PRIVATE_KEY='.$privateKey);
        $this->line('WEBPUSH_VAPID_SUBJECT=mailto:'.config('mail.from.address', 'admin@jlune.local'));
    }

    protected function pemToDer(string $pem): string
    {
        $pem = preg_replace('/\-+BEGIN.*?\-+|\-+END.*?\-+|\s+/', '', $pem);

        return base64_decode($pem ?: '');
    }

    protected function extractPublicDer(string $pemPublic): string
    {
        $pem = preg_replace('/\-+BEGIN.*?\-+|\-+END.*?\-+|\s+/', '', $pemPublic);

        return base64_decode($pem ?: '');
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
