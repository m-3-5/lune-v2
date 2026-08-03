<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.55; margin: 28px 34px; }
        h1 { font-size: 15px; text-transform: uppercase; text-align: center; margin: 0 0 2px; }
        .subtitle { text-align: center; font-size: 9px; color: #6b7280; margin: 0 0 4px; }
        .booking { text-align: center; font-weight: bold; font-size: 12px; margin: 0 0 16px; }
        .box { border: 1px solid #d1d5db; border-radius: 6px; padding: 10px 14px; margin-bottom: 10px; }
        .box-title { font-weight: bold; color: #312e81; text-transform: uppercase; font-size: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin: 0 0 6px; }
        .box p { margin: 2px 0; }
        .guest { margin: 0 0 6px; }
        .muted { color: #6b7280; font-size: 9px; }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 9px; }
        .body-text { text-align: justify; }
        .body-text p { margin: 8px 0; }
        .body-text ul, .body-text ol { margin: 6px 0 6px 18px; padding: 0; }
        .signature { margin-top: 22px; border: 1px solid #a7f3d0; background: #ecfdf5; border-radius: 6px; padding: 10px 14px; font-size: 10px; color: #065f46; }
        .footer { margin-top: 18px; border-top: 1px solid #e5e7eb; padding-top: 6px; font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>
    @if ($locale === 'en')
        <h1>Short-Term Rental Agreement - Tourist Use</h1>
        <p class="subtitle">(pursuant to Italian Law 431/1998, Civil Code articles 1571 and following, and DL 50/2017)</p>
        <p class="booking">Booking {{ $reservation->booking_code }}</p>
    @else
        <h1>Contratto di Locazione Breve per Finalità Turistica</h1>
        <p class="subtitle">(ai sensi della Legge 431/1998, artt. 1571 e seguenti del Codice Civile e D.L. 50/2017)</p>
        <p class="booking">Prenotazione {{ $reservation->booking_code }}</p>
    @endif

    <div class="box">
        <p class="box-title">{{ $locale === 'en' ? 'Landlord' : 'Locatore' }}</p>
        <p><strong>{{ \App\Support\AppSettings::landlordName() ?: ($locale === 'en' ? '[Landlord name to configure]' : '[Nome gestore da configurare]') }}</strong></p>
        @if (\App\Support\AppSettings::landlordAddress())
            <p>{{ \App\Support\AppSettings::landlordAddress() }}</p>
        @endif
        <p class="muted">{{ \App\Support\AppSettings::landlordEmail() }} | {{ \App\Support\AppSettings::landlordPhone() }}</p>
    </div>

    <div class="box">
        <p class="box-title">{{ $locale === 'en' ? 'Guest(s)' : 'Conduttore / Ospiti' }}</p>
        @foreach ($guests as $guest)
            <div class="guest">
                <strong>{{ $locale === 'en' ? 'Guest' : 'Ospite' }} {{ $guest['slot'] }}:</strong>
                {{ trim(($guest['data']['first_name'] ?? '').' '.($guest['data']['last_name'] ?? '')) ?: '—' }}
                @if (!empty($guest['data']['birth_date']))
                    <span class="muted">({{ $locale === 'en' ? 'born on' : 'nato/a il' }} {{ $guest['data']['birth_date'] }})</span>
                @endif
                @if (!empty($guest['data']['document_number']))
                    <br><span class="muted">Doc: {{ $guest['data']['document_number'] }}</span>
                @endif
                @if (!($guest['is_foreigner'] ?? false) && !empty($guest['data']['tax_code']))
                    <br><span class="mono">CF: {{ $guest['data']['tax_code'] }}</span>
                @endif
                @if ($guest['is_foreigner'] ?? false)
                    <br><span class="muted">{{ $locale === 'en' ? 'Foreign guest (Tax code not required)' : 'Cittadino straniero (CF non richiesto)' }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="body-text">
        {!! $body !!}
    </div>

    @if ($reservation->contract_accepted)
        <div class="signature">
            @if ($locale === 'en')
                <strong>Electronically signed</strong> by the Guest on {{ optional($reservation->contract_accepted_at)->format('d/m/Y H:i') ?? '—' }} via the Jlune check-in portal (Terms &amp; Conditions and Privacy Policy accepted).
            @else
                <strong>Firmato elettronicamente</strong> dal Conduttore il {{ optional($reservation->contract_accepted_at)->format('d/m/Y H:i') ?? '—' }} tramite il portale di check-in Jlune (Termini e Condizioni e Informativa Privacy accettati).
            @endif
        </div>
    @endif

    <div class="footer">
        {{ $locale === 'en' ? 'Contract electronically generated on' : 'Contratto generato elettronicamente il' }} {{ now()->format('d/m/Y H:i') }} — Jlune App
    </div>
</body>
</html>
