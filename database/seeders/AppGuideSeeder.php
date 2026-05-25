<?php

namespace Database\Seeders;

use App\Models\DevelopmentItem;
use App\Support\AppSettings;
use Illuminate\Database\Seeder;

class AppGuideSeeder extends Seeder
{
    public function run(): void
    {
        AppSettings::set('app_guide', self::guideText());

        DevelopmentItem::firstOrCreate(
            [
                'type' => DevelopmentItem::TYPE_SERENELLA_REQUEST,
                'title' => 'Link pagamento Checkfront reale (esempio per cliente)',
            ],
            [
                'status' => DevelopmentItem::STATUS_OPEN,
                'body' => <<<'BODY'
Ciao Serenella,

nell’app il pulsante «Paga su Checkfront» per l’ospite a volte non porta alla pagina giusta (URL da verificare in produzione).

Per sistemarlo ci serve un esempio reale:
1. Apri Checkfront e copia il link di pagamento che mandate normalmente a un cliente (email o messaggio WhatsApp).
2. Incollalo qui sotto in una risposta, oppure indica codice prenotazione + se il link inizia con https://jlune.checkfront.com/reserve/...

Così replichiamo il formato corretto nell’app.

Grazie!
BODY,
                'author' => 'team',
            ]
        );

        $this->command?->info('Guida progetto e task iniziale aggiornati.');
    }

    public static function guideText(): string
    {
        return <<<'GUIDE'
# Jlune App — Cosa è pronto oggi (maggio 2026)

Questa guida è per Serenella e per il team di sviluppo. Descrive cosa funziona **adesso**, cosa ci aspettiamo che funzioni in uso normale, e cosa manca ancora.

---

## In sintesi

Jlune collega **Checkfront** (prenotazioni) con:
- l’**area ospite** (link personale `/checkin/…` sul telefono),
- il **pannello admin** (agenda, documenti, contratto).

L’app è in **fase avanzata** su documenti e contratto; altre parti sono ancora da completare (video, QR, email/WhatsApp automatici, login admin).

---

## Cosa è già pronto e utilizzabile

### Prenotazioni e date (Checkfront)
- Webhook Checkfront aggiorna le prenotazioni quando qualcosa cambia online.
- Comandi tecnici (solo sviluppo/server): import dal log, sync appartamenti, verifica date (`jlune:status`).
- Agenda admin: **Oggi**, **Domani**, striscia 7 giorni, **In casa**, arrivi/partenze con icone.
- Date check-in 16:00 e check-out 10:00 in fuso **Europe/Rome** (allineate ai timestamp Checkfront).

### Area ospite (link con token)
- Home check-in con nome ospite, appartamento, stato pagamento.
- **Documenti**: upload CI (e CF se italiano), invio a Serenella, notifica «in verifica».
- **Contratto**: dopo approvazione documenti e invio da admin, firma con checkbox (IT o EN).
- **Campanella notifiche** in-app (pagamento, documenti, contratto, promemoria).
- Menu con voci future (video ingresso, QR elettrodomestici, check-out) — visibili ma **non ancora collegate** a pagine vere.

### Pannello admin (Serenella)
- **Dashboard** con agenda giornaliera.
- **Arrivi e documenti**: elenco Future / Archivio / Cancellate; dettaglio per ogni prenotazione.
- Nel dettaglio: anteprima file, approva/rifiuta documenti, **Estrai dati (Document AI)** se configurato sul server, modifica CF, scegli IT/EN, **«Contratto pronto — invia per la firma»**.
- Dati Checkfront in scheda (ospiti, letti, note, totali pagati).
- **Notifiche** (campanella): nuovi documenti, prenotazioni, ecc.
- Pagina **Progetto e task** (questa guida, costi, richieste e avanzamenti).

### Contratto
- Modelli IT e EN con dati ospiti e appartamento.
- Salvataggio firma e snapshot HTML lato server.
- Flusso: pagamento → documenti → approvazione → estrazione (consigliata) → invio contratto → firma ospite.

### Modalità «App in costruzione» (solo team sviluppo)
- Banner visibile su tutte le pagine.
- Le notifiche **non** vanno all’ospite: in admin compare un’**anteprima** («questo avrebbe ricevuto il cliente»).
- Utile per testare senza disturbare i clienti.

---

## Cosa ci aspettiamo che funzioni (uso normale)

| Passo | Comportamento atteso |
|-------|----------------------|
| 1 | Checkfront crea/aggiorna prenotazione → Jlune riceve webhook |
| 2 | Ospite apre link check-in (token univoco) |
| 3 | Paga acconto/saldo su **Checkfront** (link dalla app) |
| 4 | Carica documenti per tutti gli ospiti |
| 5 | Serenella approva → estrae dati → invia contratto |
| 6 | Ospite firma contratto |
| 7 | (Futuro) Video ingresso, QR, istruzioni uscita |

---

## Cosa non è ancora pronto (o solo abbozzo)

| Funzione | Stato |
|----------|--------|
| **Link pagamento Checkfront** | Da verificare/correggere in produzione (formato URL `…/reserve/?code=…`). **Serve esempio reale da Serenella** (vedi task in Progetto). |
| **Email automatiche** agli ospiti | Non inviate; solo notifiche in-app |
| **WhatsApp automatici** | Non implementati |
| **Video ingresso** (admin + ospite) | Pagina admin placeholder; menu ospite senza link |
| **QR elettrodomestici** | Tabelle DB presenti, nessuna pagina ospite |
| **Archivio contratti** admin | Pagina vuota (placeholder) |
| **PDF contratto firmato** con prova legale | Previsto, non fatto |
| **Controllo antifrode documenti** (Gemini) | Codice di test, non attivo all’upload |
| **Login / password** pannello admin | Chi conosce l’URL entra; solo `/admin/sviluppo` ha password |
| **Invio email** a Serenella su eventi | Solo campanella in-app |

---

## Comandi utili (solo chi gestisce il server)

- `php artisan checkfront:import-log` — allinea prenotazioni dal log webhook
- `php artisan checkfront:sync-items` — allinea appartamenti Checkfront
- `php artisan jlune:status` — controllo arrivi e date
- `php artisan migrate --force` — dopo aggiornamenti app (Plesk)

---

## A oggi — cosa manca e stima tempi di completamento

Stima **indicativa** per portare l’MVP «comunicazioni cliente» a produzione stabile (esclusi costi API terze parti):

| Blocco | Contenuto | Stima |
|--------|-----------|--------|
| A | Correzione link pagamento + test con link reale Serenella | 0,5–1 giorno |
| B | Login admin (Serenella + team) | 1–2 giorni |
| C | Video ingresso: caricamento admin + pagina ospite | 2–3 giorni |
| D | QR elettrodomestici per appartamento | 1–2 giorni |
| E | Email automatiche (ospiti + admin) configurazione SMTP | 2–3 giorni |
| F | WhatsApp (API provider, template messaggi) | 3–5 giorni |
| G | Archivio contratti firmati + export PDF | 1–2 giorni |
| H | Affinamenti UX da feedback Serenella | 2–3 giorni |
| I | Test produzione, documentazione, deploy | 1–2 giorni |

**Totale stimato: circa 15–22 giorni lavorativi** (3–4 settimane a ritmo part-time, oppure 2–3 settimane full-time).

Priorità consigliata: **A → B → C/D → E** (pagamento e sicurezza prima; messaggistica dopo).

---

## Costi progetto

Vedi riepilogo in fondo a questa pagina (base + voci extra). Aggiornato dal team in area Sviluppo.

---

_Ultimo aggiornamento guida: maggio 2026 — aggiornare quando si rilasciano nuove funzioni._
GUIDE;
    }
}
