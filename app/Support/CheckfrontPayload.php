<?php

namespace App\Support;

/**
 * Helper per normalizzare valori dai payload XML/JSON di Checkfront.
 */
class CheckfrontPayload
{
    public static function scalar(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    public static function float(mixed $value): float
    {
        if (is_string($value)) {
            return (float) str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    /**
     * @param  array<string, mixed>  $taxesNode
     * @return array<int, array<string, string>>
     */
    public static function normalizeTaxes(array $taxesNode): array
    {
        $tax = $taxesNode['tax'] ?? [];
        if (isset($tax['name'])) {
            $tax = [$tax];
        }

        $out = [];
        foreach ($tax as $row) {
            if (! is_array($row)) {
                continue;
            }
            $attrs = $row['@attributes'] ?? [];
            $out[] = [
                'tax_id' => (string) ($attrs['tax_id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'amount' => (string) ($row['amount'] ?? '0'),
            ];
        }

        return $out;
    }
}
