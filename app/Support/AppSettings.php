<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;

class AppSettings
{
    protected static ?array $cache = null;

    public static function clearCache(): void
    {
        static::$cache = null;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        AppSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        static::clearCache();
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (static::$cache === null) {
            static::$cache = AppSetting::query()
                ->pluck('value', 'key')
                ->all();
        }

        return static::$cache;
    }

    /** Nome del prodotto/struttura mostrato in tutta l'app — l'unico posto da cambiare per rinominare tutto. */
    public static function appName(): string
    {
        return (string) static::get('app_name', 'Gestione Appartamenti');
    }

    public static function setAppName(string $name): void
    {
        static::set('app_name', trim($name) ?: 'Gestione Appartamenti');
    }

    public static function testBookingsAdminEnabled(): bool
    {
        return (bool) static::get('test_bookings_admin_enabled', false);
    }

    public static function setTestBookingsAdminEnabled(bool $on): void
    {
        static::set('test_bookings_admin_enabled', $on);
    }

    public static function adminNotificationsEnabled(): bool
    {
        return (bool) static::get('admin_notifications_enabled', false);
    }

    public static function setAdminNotificationsEnabled(bool $on): void
    {
        static::set('admin_notifications_enabled', $on);
    }

    public static function adminEmailNotificationsEnabled(): bool
    {
        return (bool) static::get('admin_email_notifications_enabled', false);
    }

    public static function setAdminEmailNotificationsEnabled(bool $on): void
    {
        static::set('admin_email_notifications_enabled', $on);
    }

    public static function adminWhatsAppNotificationsEnabled(): bool
    {
        return (bool) static::get('admin_whatsapp_notifications_enabled', false);
    }

    public static function setAdminWhatsAppNotificationsEnabled(bool $on): void
    {
        static::set('admin_whatsapp_notifications_enabled', $on);
    }

    public static function guestNotificationsEnabled(): bool
    {
        return (bool) static::get('guest_notifications_enabled', false);
    }

    public static function setGuestNotificationsEnabled(bool $on): void
    {
        static::set('guest_notifications_enabled', $on);
    }

    public static function guestEmailNotificationsEnabled(): bool
    {
        return (bool) static::get('guest_email_notifications_enabled', false);
    }

    public static function setGuestEmailNotificationsEnabled(bool $on): void
    {
        static::set('guest_email_notifications_enabled', $on);
    }

    public static function guestWhatsAppNotificationsEnabled(): bool
    {
        return (bool) static::get('guest_whatsapp_notifications_enabled', false);
    }

    public static function setGuestWhatsAppNotificationsEnabled(bool $on): void
    {
        static::set('guest_whatsapp_notifications_enabled', $on);
    }

    public static function guestPushNotificationsEnabled(): bool
    {
        return (bool) static::get('guest_push_notifications_enabled', false);
    }

    public static function setGuestPushNotificationsEnabled(bool $on): void
    {
        static::set('guest_push_notifications_enabled', $on);
    }

    public static function guestTelegramNotificationsEnabled(): bool
    {
        return (bool) static::get('guest_telegram_notifications_enabled', false);
    }

    public static function setGuestTelegramNotificationsEnabled(bool $on): void
    {
        static::set('guest_telegram_notifications_enabled', $on);
    }

    /** Notifiche esterne (email/WhatsApp/Telegram/push) verso l'ospite consentite? */
    public static function guestOutboundEnabled(): bool
    {
        return static::guestNotificationsEnabled()
            && (static::guestEmailNotificationsEnabled()
                || static::guestWhatsAppNotificationsEnabled()
                || static::guestTelegramNotificationsEnabled()
                || static::guestPushNotificationsEnabled());
    }

    public static function mailSmtpEnabled(): bool
    {
        return (bool) static::get('mail_smtp_enabled', false);
    }

    public static function setMailSmtpEnabled(bool $on): void
    {
        static::set('mail_smtp_enabled', $on);
    }

    public static function mailSmtpHost(): string
    {
        return (string) static::get('mail_smtp_host', 'out.postassl.it');
    }

    public static function mailSmtpPort(): int
    {
        return (int) static::get('mail_smtp_port', 465);
    }

    public static function mailSmtpEncryption(): string
    {
        return (string) static::get('mail_smtp_encryption', 'ssl');
    }

    public static function mailSmtpScheme(): ?string
    {
        return match (static::mailSmtpEncryption()) {
            'ssl' => 'smtps',
            'tls' => null,
            default => null,
        };
    }

    public static function mailSmtpUsername(): string
    {
        return (string) static::get('mail_smtp_username', '');
    }

    public static function mailFromAddress(): string
    {
        return (string) static::get('mail_from_address', 'appjlune@inm35.net');
    }

    public static function mailFromName(): string
    {
        return (string) static::get('mail_from_name', static::appName());
    }

    public static function mailPasswordIsSet(): bool
    {
        return filled(static::get('mail_smtp_password'));
    }

    public static function mailSmtpPassword(): ?string
    {
        $encrypted = static::get('mail_smtp_password');

        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function setMailSmtpPassword(?string $plain): void
    {
        if ($plain === null || $plain === '') {
            return;
        }

        static::set('mail_smtp_password', Crypt::encryptString($plain));
    }

    public static function mailSmtpReady(): bool
    {
        return static::mailSmtpEnabled()
            && static::mailPasswordIsSet()
            && static::mailSmtpHost() !== ''
            && static::mailSmtpUsername() !== '';
    }

    public static function mailSmtpVerifySsl(): bool
    {
        if (config('app.env') !== 'production') {
            return (bool) static::get('mail_smtp_verify_ssl', false);
        }

        return (bool) static::get('mail_smtp_verify_ssl', true);
    }

    public static function whatsappProvider(): string
    {
        $fromDb = static::get('whatsapp_provider');

        if (is_string($fromDb) && $fromDb !== '') {
            return $fromDb;
        }

        return (string) config('jlune.whatsapp_provider', 'log');
    }

    public static function setWhatsAppProvider(string $provider): void
    {
        static::set('whatsapp_provider', $provider);
    }

    /**
     * @return array<int, string>
     */
    public static function whatsappCallMeBotKeys(): array
    {
        $fromDb = static::get('whatsapp_callmebot_keys', []);

        if (is_array($fromDb) && $fromDb !== []) {
            return array_values(array_filter(array_map('trim', $fromDb)));
        }

        return config('jlune.whatsapp_callmebot_keys', []);
    }

    /**
     * @param  array<int, string>  $keys
     */
    public static function setWhatsAppCallMeBotKeys(array $keys): void
    {
        static::set('whatsapp_callmebot_keys', array_values(array_filter(array_map('trim', $keys))));
    }

    public static function twilioAccountSid(): string
    {
        return (string) static::get('twilio_account_sid', '');
    }

    public static function twilioWhatsAppFrom(): string
    {
        return (string) static::get('twilio_whatsapp_from', '+14155238886');
    }

    public static function twilioWhatsAppMode(): string
    {
        $mode = (string) static::get('twilio_whatsapp_mode', 'sandbox');

        return in_array($mode, ['sandbox', 'business'], true) ? $mode : 'sandbox';
    }

    public static function setTwilioWhatsAppMode(string $mode): void
    {
        static::set('twilio_whatsapp_mode', in_array($mode, ['sandbox', 'business'], true) ? $mode : 'sandbox');
    }

    public static function twilioContentTemplateSid(): string
    {
        return (string) static::get('twilio_content_template_sid', '');
    }

    public static function twilioMetaBusinessId(): string
    {
        return (string) static::get('twilio_meta_business_id', '');
    }

    public static function twilioAuthTokenIsSet(): bool
    {
        return filled(static::get('twilio_auth_token'));
    }

    public static function twilioAuthToken(): ?string
    {
        $encrypted = static::get('twilio_auth_token');

        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function setTwilioAuthToken(?string $plain): void
    {
        if ($plain === null || $plain === '') {
            return;
        }

        static::set('twilio_auth_token', Crypt::encryptString($plain));
    }

    public static function twilioReady(): bool
    {
        return static::twilioAccountSid() !== ''
            && static::twilioAuthTokenIsSet()
            && static::twilioWhatsAppFrom() !== '';
    }

    public static function whatsAppChannelReady(): bool
    {
        return match (static::whatsappProvider()) {
            'twilio' => static::twilioReady(),
            'callmebot' => static::whatsappCallMeBotKeys() !== [],
            default => false,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function adminEmails(): array
    {
        $lines = static::get('admin_emails', []);

        return static::parseEmails(is_array($lines) ? $lines : []);
    }

    /**
     * @return array<int, string>
     */
    public static function adminPhones(): array
    {
        $lines = static::get('admin_phones', []);

        return static::parsePhones(is_array($lines) ? $lines : []);
    }

    /**
     * @param  array<int, string|mixed>  $lines
     * @return array<int, string>
     */
    protected static function parseEmails(array $lines): array
    {
        $emails = [];

        foreach (static::normalizeLines($lines) as $line) {
            if (preg_match('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $line, $m)) {
                $emails[] = strtolower($m[0]);
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * @param  array<int, string|mixed>  $lines
     * @return array<int, string>
     */
    protected static function parsePhones(array $lines): array
    {
        $phones = [];

        foreach (static::normalizeLines($lines) as $line) {
            if (preg_match('/(\+?\d[\d\s\-().]{7,}\d)/', $line, $m)) {
                $normalized = preg_replace('/[^\d+]/', '', $m[1]);
                if (strlen($normalized) >= 10) {
                    $phones[] = $normalized;
                }
            }
        }

        return array_values(array_unique($phones));
    }

    /** Dati del locatore mostrati sui contratti generati — da configurare in Progetto → Contatti. */
    public static function landlordName(): string
    {
        return (string) static::get('landlord_name', '');
    }

    public static function landlordAddress(): string
    {
        return (string) static::get('landlord_address', '');
    }

    public static function landlordEmail(): string
    {
        return (string) static::get('landlord_email', '');
    }

    public static function landlordPhone(): string
    {
        return (string) static::get('landlord_phone', '');
    }

    public static function setLandlordDetails(string $name, string $address, string $email, string $phone): void
    {
        static::set('landlord_name', trim($name));
        static::set('landlord_address', trim($address));
        static::set('landlord_email', trim($email));
        static::set('landlord_phone', trim($phone));
    }

    /**
     * Un'unica password "team" che sblocca /admin/sviluppo (JluneDeveloperAccess).
     * Se JLUNE_DEV_PASSWORD è configurata (produzione) è quella; altrimenti (locale)
     * ne genera una e la salva nel DB.
     */
    public static function maintenanceAccessPassword(): string
    {
        $envPassword = (string) config('jlune.dev_password');

        if ($envPassword !== '') {
            return $envPassword;
        }

        $password = static::get('maintenance_access_password');

        if (is_string($password) && $password !== '') {
            return $password;
        }

        $password = 'jlune'.random_int(1000, 9999);
        static::set('maintenance_access_password', $password);

        return $password;
    }

    /** Corpo del contratto personalizzato (null = usa testo predefinito). */
    public static function contractBody(string $locale): ?string
    {
        $html = trim((string) static::get("contract_body_{$locale}", ''));

        return $html !== '' ? $html : null;
    }

    public static function setContractBody(string $locale, ?string $html): void
    {
        static::set("contract_body_{$locale}", trim((string) $html));
    }

    public static function projectBaseCost(): float
    {
        return (float) static::get('project_base_cost', 0);
    }

    /**
     * @return array<int, array{label: string, amount: float|int, date?: string}>
     */
    public static function projectCostEntries(): array
    {
        $entries = static::get('project_cost_entries', []);

        return is_array($entries) ? $entries : [];
    }

    public static function projectTotalCost(): float
    {
        $extra = collect(static::projectCostEntries())->sum(fn ($e) => (float) ($e['amount'] ?? 0));

        return static::projectBaseCost() + $extra;
    }

    /**
     * @param  array<int, string|mixed>  $lines
     * @return array<int, string>
     */
    protected static function normalizeLines(array $lines): array
    {
        return array_values(array_filter(array_map(function ($line) {
            $line = trim(is_string($line) ? $line : '');
            // Rimuove commenti tipo "email@x.it — Nome"
            if (str_contains($line, '—')) {
                $line = trim(explode('—', $line, 2)[0]);
            }
            if (str_contains($line, ' - ') && ! str_contains($line, '@')) {
                $line = trim(explode(' - ', $line, 2)[0]);
            }

            return $line;
        }, $lines)));
    }
}
