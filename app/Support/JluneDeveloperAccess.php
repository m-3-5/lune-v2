<?php

namespace App\Support;

class JluneDeveloperAccess
{
    public const SESSION_KEY = 'jlune_developer';

    public static function check(string $password): bool
    {
        $expected = (string) config('jlune.dev_password');

        if ($expected === '') {
            return app()->environment('local');
        }

        return hash_equals($expected, $password);
    }

    public static function grant(): void
    {
        session([self::SESSION_KEY => true]);
    }

    public static function revoke(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function isGranted(): bool
    {
        if (session(self::SESSION_KEY) === true) {
            return true;
        }

        return config('jlune.dev_password') === '' && app()->environment('local');
    }
}
