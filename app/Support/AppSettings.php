<?php

namespace App\Support;

use App\Models\AppSetting;

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

    public static function underConstruction(): bool
    {
        return (bool) static::get('under_construction', false);
    }

    public static function setUnderConstruction(bool $on): void
    {
        static::set('under_construction', $on);
    }

    /**
     * @return array<int, string>
     */
    public static function adminEmails(): array
    {
        $lines = static::get('admin_emails', []);

        return static::normalizeLines(is_array($lines) ? $lines : []);
    }

    /**
     * @return array<int, string>
     */
    public static function adminPhones(): array
    {
        $lines = static::get('admin_phones', []);

        return static::normalizeLines(is_array($lines) ? $lines : []);
    }

    public static function appGuide(): string
    {
        return (string) static::get('app_guide', '');
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
            return trim(is_string($line) ? $line : '');
        }, $lines)));
    }
}
