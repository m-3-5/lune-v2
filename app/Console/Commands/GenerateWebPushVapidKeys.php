<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateWebPushVapidKeys extends Command
{
    protected $signature = 'jlune:vapid-keys';

    protected $description = 'Genera chiavi VAPID per Web Push (formato corretto per browser e minishlink/web-push)';

    public function handle(): int
    {
        $opensslCnf = $this->applyOpenSslConfig();

        if (class_exists(\Minishlink\WebPush\VAPID::class)) {
            try {
                return $this->outputFromWebPushLibrary();
            } catch (\Throwable $e) {
                $this->warn('Libreria web-push: '.$e->getMessage());
            }
        }

        if (! extension_loaded('openssl')) {
            $this->error('Estensione OpenSSL richiesta.');

            return self::FAILURE;
        }

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
            while ($msg = openssl_error_string()) {
                $this->line('OpenSSL: '.$msg);
            }

            return self::FAILURE;
        }

        $details = openssl_pkey_get_details($key);
        if (! isset($details['ec']['x'], $details['ec']['y'], $details['ec']['d'])) {
            $this->error('Impossibile estrarre chiavi EC (serve PHP OpenSSL con curve P-256).');

            return self::FAILURE;
        }

        $publicRaw = "\x04".$details['ec']['x'].$details['ec']['y'];
        $privateRaw = str_pad($details['ec']['d'], 32, "\x00", STR_PAD_LEFT);

        if (strlen($publicRaw) !== 65 || strlen($privateRaw) !== 32) {
            $this->error('Lunghezza chiavi non valida per Web Push.');

            return self::FAILURE;
        }

        $this->printEnvLines(
            $this->base64UrlEncode($publicRaw),
            $this->base64UrlEncode($privateRaw),
        );

        return self::SUCCESS;
    }

    protected function applyOpenSslConfig(): ?string
    {
        $path = $this->resolveOpenSslConfigPath();
        if ($path !== null) {
            putenv('OPENSSL_CONF='.$path);
        }

        return $path;
    }

    protected function outputFromWebPushLibrary(): int
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
            '/etc/ssl/openssl.cnf',
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
        $pubLen = strlen(base64_decode(strtr($publicKey, '-_', '+/').str_repeat('=', (4 - strlen($publicKey) % 4) % 4)));
        $privLen = strlen(base64_decode(strtr($privateKey, '-_', '+/').str_repeat('=', (4 - strlen($privateKey) % 4) % 4)));

        $this->info('Aggiungi al .env (sostituisci le chiavi vecchie se presenti):');
        $this->newLine();
        $this->line('WEBPUSH_ENABLED=true');
        $this->line('WEBPUSH_VAPID_PUBLIC_KEY='.$publicKey);
        $this->line('WEBPUSH_VAPID_PRIVATE_KEY='.$privateKey);
        $this->line('WEBPUSH_VAPID_SUBJECT=mailto:'.config('mail.from.address', 'admin@jlune.local'));
        $this->newLine();
        $this->comment("Verifica: chiave pubblica decodificata = {$pubLen} byte (atteso 65), privata = {$privLen} byte (atteso 32).");
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
