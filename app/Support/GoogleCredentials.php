<?php

namespace App\Support;

class GoogleCredentials
{
    public static function resolvePath(?string $path = null): ?string
    {
        $path = $path ?? config('google.application_credentials');

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path, " \t\n\r\0\x0B\"'");

        if (! self::isAbsolutePath($path)) {
            $path = base_path($path);
        }

        $real = realpath($path);

        return $real !== false ? $real : $path;
    }

    public static function isReadable(): bool
    {
        $path = self::resolvePath();

        return $path !== null && is_file($path) && is_readable($path);
    }

    public static function diagnostics(): array
    {
        $configured = config('google.application_credentials');
        $resolved = self::resolvePath();
        $exists = $resolved !== null && is_file($resolved);
        $readable = $exists && is_readable($resolved);

        return [
            'configured' => $configured,
            'resolved' => $resolved,
            'exists' => $exists,
            'readable' => $readable,
            'project_id' => config('google.project_id'),
            'processor_id' => config('google.document_ai.processor_id'),
            'location' => config('google.document_ai.location'),
        ];
    }

    protected static function isAbsolutePath(string $path): bool
    {
        if (str_starts_with($path, '/')) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
