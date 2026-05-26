<?php

namespace App\Services;

use App\Models\Reservation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestDataExportService
{
    public function download(Reservation $reservation, string $format = 'json'): StreamedResponse
    {
        $guests = $reservation->extracted_guests ?? [];

        if (empty($guests)) {
            abort(404, 'Nessun dato estratto per questa prenotazione.');
        }

        $code = $reservation->booking_code ?? (string) $reservation->id;
        $filename = 'ospiti-'.$code.'-'.now()->format('Y-m-d');

        if ($format === 'xml') {
            $xml = new \DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = true;
            $root = $xml->createElement('jlune_export');
            $root->setAttribute('booking_code', (string) $code);
            $root->setAttribute('reservation_id', (string) $reservation->id);
            $root->setAttribute('extracted_at', $reservation->contract_extracted_at?->toIso8601String() ?? '');
            $xml->appendChild($root);

            $guestsEl = $xml->createElement('guests');
            $root->appendChild($guestsEl);

            foreach ($guests as $g) {
                $d = $g['data'] ?? [];
                $guestEl = $xml->createElement('guest');
                $guestEl->setAttribute('slot', (string) ($g['slot'] ?? ''));
                $guestEl->setAttribute('straniero', ($g['is_foreigner'] ?? false) ? 'true' : 'false');

                foreach ([
                    'cognome' => $d['last_name'] ?? '',
                    'nome' => $d['first_name'] ?? '',
                    'data_nascita' => $d['birth_date'] ?? '',
                    'codice_fiscale' => $d['tax_code'] ?? '',
                    'numero_documento' => $d['document_number'] ?? '',
                ] as $tag => $value) {
                    $node = $xml->createElement($tag);
                    $node->appendChild($xml->createTextNode((string) $value));
                    $guestEl->appendChild($node);
                }

                $guestsEl->appendChild($guestEl);
            }

            return response()->streamDownload(
                fn () => print($xml->saveXML()),
                "{$filename}.xml",
                ['Content-Type' => 'application/xml; charset=UTF-8']
            );
        }

        if ($format === 'csv') {
            $rows = [['slot', 'cognome', 'nome', 'data_nascita', 'codice_fiscale', 'numero_documento', 'straniero']];
            foreach ($guests as $g) {
                $d = $g['data'] ?? [];
                $rows[] = [
                    $g['slot'] ?? '',
                    $d['last_name'] ?? '',
                    $d['first_name'] ?? '',
                    $d['birth_date'] ?? '',
                    $d['tax_code'] ?? '',
                    $d['document_number'] ?? '',
                    ($g['is_foreigner'] ?? false) ? 'sì' : 'no',
                ];
            }

            $csv = '';
            foreach ($rows as $row) {
                $csv .= implode(';', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row))."\n";
            }

            return response()->streamDownload(
                fn () => print($csv),
                "{$filename}.csv",
                ['Content-Type' => 'text/csv; charset=UTF-8']
            );
        }

        $payload = [
            'booking_code' => $reservation->booking_code,
            'reservation_id' => $reservation->id,
            'extracted_at' => $reservation->contract_extracted_at?->toIso8601String(),
            'guests' => $guests,
            'documents' => $reservation->guestDocuments->map(fn ($doc) => [
                'id' => $doc->id,
                'type' => $doc->document_type,
                'ai' => $doc->ai_raw_response,
            ])->values(),
        ];

        return response()->streamDownload(
            fn () => print(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            "{$filename}.json",
            ['Content-Type' => 'application/json']
        );
    }
}
