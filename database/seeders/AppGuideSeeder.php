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

        foreach (self::openQuestionsForSerenella() as $question) {
            DevelopmentItem::firstOrCreate(
                [
                    'type' => DevelopmentItem::TYPE_QUESTION_FOR_SERENELLA,
                    'title' => $question['title'],
                ],
                [
                    'status' => DevelopmentItem::STATUS_OPEN,
                    'body' => $question['body'],
                    'author' => 'team',
                ]
            );
        }

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
  - Il **codice fiscale è obbligatorio** per gli ospiti italiani: senza CF la firma è bloccata e l'ospite riceve una notifica dedicata.
  - Alla firma l'ospite riceve **email con il contratto PDF allegato** (se le notifiche email ospite sono attive).
- **Campanella notifiche** in-app (pagamento, documenti, contratto, promemoria).
- Menu con voci future (video ingresso, QR elettrodomestici, check-out) — visibili ma **non ancora collegate** a pagine vere.

### Pannello admin (Serenella)
- **Dashboard** con agenda (**Oggi / Domani**, striscia 7 giorni, switch **Arrivi imminenti** 14 giorni), card domande aperte.
- **Arrivi e documenti**: elenco Future / Archivio / Cancellate; dettaglio per ogni prenotazione.
- **Prova flusso** (`/admin/prova`): crea prenotazioni **TEST** senza Checkfront (link ospite, documenti, contratto). Attiva/disattiva con l'interruttore in pagina — **non sono prenotazioni reali** (badge TEST).
- Nel dettaglio: anteprima file, approva/rifiuta documenti, **Estrai dati (Document AI)** — **attivo** (maggio 2026), modifica CF, anteprima OCR per documento, export **JSON / CSV / XML**, scegli IT/EN, **«Contratto pronto — invia per la firma»**.
- Dati Checkfront in scheda (ospiti, letti, note, totali pagati).
- **Notifiche in-app** (campanella in alto): nuovi documenti, prenotazioni, **contratti firmati**, anteprime in costruzione.
- **Contratti** (`/admin/contratti`): archivio dei contratti firmati con data/ora firma e **download PDF** (o rigenerazione).
- **Testo contratto** (`/admin/testo-contratto`): editor stile Word per modificare le clausole del contratto (IT e EN) senza toccare il codice. I **segnaposto** tipo `[CHECK_IN]`, `[PREZZO_TOTALE]`, `[APPARTAMENTO]` vengono sostituiti automaticamente con i dati di ogni prenotazione. Pulsanti: Salva, Ripristina testo predefinito, Anteprima con dati reali.
- **Notifiche** (`/admin/notifiche`): panoramica semplice — cosa è attivo, checklist per andare live, tabella «chi riceve cosa», link a Canali e Progetto.
- Pagina **Progetto e task** (questa guida, costi, richieste e avanzamenti).

### Notifiche sul telefono (attive — maggio 2026)

Due **app separate** da installare (icona sulla home):
- **Jlune Gestione** — da `/admin` (Serenella e team).
- **Jlune Check-in** — dal link personale dell’ospite (`/checkin/…`).

#### Serenella (consigliato)
1. Apri **Progetto e task** (o Dashboard) sul telefono.
2. **Aggiungi a schermata Home** (Safari: Condividi → Aggiungi a Home; Android: menu → Installa app).
3. Apri l’icona Jlune Admin → scorri **Attiva notifiche** → consenti.
4. Riceverai **vibrazione/push** su task, documenti da verificare, contratti, ecc. (senza Telegram, salvo decisione futura).

#### Max (team)
- Come Serenella per la **PWA admin** + **Attiva notifiche**.
- In più **Telegram**: cerca `@jlune_notifiche_bot` → **Avvia** → ricevi gli stessi avvisi anche su Telegram.

#### Ospite (cliente)
1. Apre il link check-in dal messaggio/email.
2. Compare il banner **«Jlune sul telefono»**: installa app + attiva notifiche (se la modalità costruzione è **spenta**).
3. **Campanella** in alto nell’app: promemoria pagamento, documenti, contratto.
4. Con **app in costruzione attiva** (solo test): l’ospite **non** riceve nulla; il team vede l’anteprima in admin/Telegram.

#### Cosa non è ancora attivo
- Telegram per gli ospiti (da decidere con Serenella).
- WhatsApp diretto agli ospiti (serve provider business Meta/Twilio; per ora solo log).

#### Notifiche email / WhatsApp (Progetto)
**Di default tutto è disattivato** (admin e ospiti) per evitare invii accidentali.

**Team admin** — sezione verde in Progetto:
- Contatti preimpostati (modificabili): email e cellulari, uno per riga.
- Attiva invio + Email e/o WhatsApp quando avete configurato SMTP/CallMeBot.
- **Invia test admin** funziona anche con toggle spenti (prova configurazione).
- Prenotazioni TEST: prefisso `[TEST]` negli avvisi admin.

**Ospiti** — sezione azzurra in Progetto:
- Master «Notifiche ospite attive» + Email / WhatsApp / Push PWA.
- Destinatari = email/telefono Checkfront della prenotazione.
- Prenotazioni TEST: **mai** email/WhatsApp reali (solo pulsante test con tua email).
- Con **app in costruzione** attiva: anteprima solo agli admin, nulla all'ospite.

**Configurazione server**
- **Admin → Canali di invio**: SMTP casella `appjlune@inm35.net` (host `out.postassl.it`, porta 465 SSL), password casella, test email.
- **Admin → Canali di invio**: WhatsApp via **Twilio** (consigliato) o CallMeBot; credenziali modificabili senza .env.
- **Admin → Progetto**: destinatari admin e toggle on/off notifiche.
- Test CLI: `php artisan jlune:notify-test --force`

### Estrazione documenti con Google Document AI (attivo — maggio 2026)

**Cosa fa oggi**
- Legge foto di **carta d’identità** (fronte/retro) e **tessera sanitaria** (CF).
- Compila automaticamente cognome, nome, data di nascita e codice fiscale nel contratto.
- Sotto ogni documento: riquadro verde con dati estratti + **Testo OCR** (come nel log tecnico).
- Export dati ospiti: **JSON**, **CSV (Excel)**, **XML** (dal dettaglio prenotazione).

**Fatturazione e account Google (importante)**
- Il servizio **non era previsto nel preventivo iniziale**; il team lo include **a titolo di favore** per avviare il cliente nuovo.
- **Oggi** la fatturazione Google Cloud è sull’account del team di sviluppo (uso temporaneo in test/produzione iniziale).
- **Prossimo passo consigliato:** creare un **progetto Google Cloud di Serenella** (o della società) con **metodo di pagamento proprio**, così i costi Document AI restano trasparenti e sotto il vostro controllo.
- In pagina **Progetto** c’è il pulsante **«Scarica istruzioni Document AI»** (passo passo + cosa inviarci).

**Formato Polizia di Stato / Alloggiati Web**
- L’XML che esportiamo oggi è un **export Jlune** (cognome, nome, CF, ecc.) — utile per archivio e Excel.
- Il file **ufficiale per la questura** ha formato diverso; **lo chiederemo a Serenella** se lo usa già e in quale modalità (portale, file, software).
- Se serve integrazione dedicata, potrà essere una **voce extra** da preventivare (da confermare insieme).

### Contratto
- Modelli IT e EN con dati ospiti e appartamento; **Locatore e Ospiti uno sotto l'altro**.
- **Testo modificabile** dall'admin nella pagina «Testo contratto» (editor visuale + segnaposto automatici).
- **CF obbligatorio** per gli ospiti italiani prima della firma (con notifica all'ospite se manca).
- Alla firma: data/ora registrate, **PDF generato e archiviato**, **notifica admin**, **email all'ospite con PDF allegato**.
- Archivio in **Admin → Contratti** con download PDF.
- Flusso: pagamento → documenti → approvazione → estrazione Document AI → invio contratto → firma ospite → PDF + archivio.

### Automatismi pianificati (cron)
- **Promemoria ospiti** (ogni giorno alle 10:00): per le prenotazioni con arrivo entro 14 giorni l'ospite riceve promemoria su pagamento mancante, documenti da caricare, CF mancante e firma contratto. Niente spam: ogni tipo ha una finestra anti-duplicato (24–72 ore).
- **Pulizia documenti** (ogni notte alle 03:30): dopo il check-out i **documenti d'identità vengono cancellati automaticamente** (file e dati) per privacy/GDPR. Il contratto firmato in PDF resta archiviato.
- Richiede il **cron di Laravel attivo sul server** (`php artisan schedule:run` ogni minuto su Plesk).

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
| **Email automatiche** agli ospiti | Implementate; **disattivate di default** in Progetto |
| **WhatsApp automatici** ospiti | Solo log (provider business da integrare) |
| **Video ingresso** (admin + ospite) | Pagina admin placeholder; menu ospite senza link |
| **QR elettrodomestici** | Tabelle DB presenti, nessuna pagina ospite |
| **Archivio contratti** admin | **Attivo** — pagina Contratti con PDF scaricabili |
| **PDF contratto firmato** | **Attivo** — generato alla firma, inviato via email all'ospite |
| **Google Document AI** (estrazione CI/CF) | **Attivo** — account Google team per ora; passaggio a account Serenella consigliato |
| **Export XML Polizia (Alloggiati)** | Da definire con Serenella; export XML Jlune già disponibile |
| **Controllo antifrode documenti** (Gemini) | Codice di test, non attivo all’upload |
| **Login / password** pannello admin | Chi conosce l’URL entra; solo `/admin/sviluppo` ha password |
| **Invio email/WhatsApp** team admin | Implementato; **disattivato di default**; SMTP + CallMeBot opzionale |
| **PWA + Web Push** | Attivo (admin e ospite); vedi sezione Notifiche |
| **Telegram team** | Attivo per Max; Serenella solo push fino a configurazione |

---

## Comandi utili (solo chi gestisce il server)

- `php artisan checkfront:import-log` — allinea prenotazioni dal log webhook
- `php artisan checkfront:sync-items` — allinea appartamenti Checkfront
- `php artisan jlune:status` — controllo arrivi e date
- `php artisan jlune:google-check` — verifica credenziali Document AI sul server
- `php artisan jlune:test-extraction {id}` — test estrazione su una prenotazione (solo tecnico)
- `php artisan jlune:guest-reminders` — promemoria ospiti (eseguito in automatico alle 10:00)
- `php artisan jlune:cleanup-documents --dry-run` — anteprima pulizia documenti post check-out (senza `--dry-run` cancella davvero; in automatico alle 03:30)
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
| G | ~~Archivio contratti firmati + export PDF~~ | **Fatto (giugno 2026)** |
| H | Affinamenti UX da feedback Serenella | 2–3 giorni |
| I | Test produzione, documentazione, deploy | 1–2 giorni |

**Totale stimato: circa 15–22 giorni lavorativi** (3–4 settimane a ritmo part-time, oppure 2–3 settimane full-time).

Priorità consigliata: **A → B → C/D → E** (pagamento e sicurezza prima; messaggistica dopo).

---

## Costi progetto

Vedi riepilogo in fondo a questa pagina (base + voci extra). Aggiornato dal team in area Sviluppo.

**Nota Document AI:** uso API Google a consumo (pagine OCR). Non incluso nel preventivo base; in fase avvio è offerto dal team; in produzione stabile andrà su **fatturazione Google del cliente** (vedi istruzioni scaricabili sopra).

---

_Ultimo aggiornamento guida: giugno 2026 — Testo contratto modificabile, PDF firmati con archivio, promemoria automatici, pulizia documenti post check-out, CF obbligatorio per la firma._
GUIDE;
    }

    /**
     * @return array<int, array{title: string, body: string}>
     */
    public static function openQuestionsForSerenella(): array
    {
        return [
            [
                'title' => 'Logo app: quale icona sulla home del telefono?',
                'body' => <<<'BODY'
Ciao Serenella,

per le due app (gestione admin e check-in ospite) serve un’icona/logo da mettere sulla home del telefono.

Puoi indicare:
- un file immagine che preferisci (quadrato, sfondo pieno), oppure
- «va bene quello attuale provvisorio» finché non ne scegliamo uno definitivo.

Rispondi qui sotto con la tua preferenza.
BODY,
            ],
            [
                'title' => 'Agenda: cosa intendi per «arrivi imminenti»?',
                'body' => <<<'BODY'
In Dashboard abbiamo due viste:
- **Per giorno** (oggi, domani, 7 giorni con arrivi A e partenze P)
- **Arrivi imminenti** (lista prossimi 14 giorni, solo arrivi)

Per adattarla al tuo lavoro, ci serve sapere:
1. Quanti giorni avanti vuoi vedere? (7, 14, 30?)
2. Ti servono solo gli **arrivi** o anche le **partenze** nella lista «imminenti»?
3. «Imminente» per te parte da dopodomani o include sempre oggi/domani?

Grazie — rispondi con numeri/esempi.
BODY,
            ],
            [
                'title' => 'Ospiti: promemoria anche su Telegram?',
                'body' => <<<'BODY'
Oggi l’ospite può ricevere:
- notifiche **dentro l’app** (campanella),
- **push sul telefono** se installa la PWA e attiva notifiche.

**Telegram per i clienti** è possibile ma diverso da WhatsApp: l’ospite deve aprire un bot e fare Avvia.

Vuoi che in futuro offriamo anche Telegram agli ospiti, o preferisci solo app + (più avanti) email/WhatsApp?

Risposta libera: sì / no / solo per alcuni casi.
BODY,
            ],
            [
                'title' => 'Export Polizia: usi Alloggiati Web o altro formato?',
                'body' => <<<'BODY'
Ciao Serenella,

l’app ora estrae i dati dai documenti e permette di scaricare **JSON, CSV e XML** (dati ospiti).

Per la **Polizia di Stato** / registrazione ospiti, il file ufficiale ha spesso un formato dedicato (es. portale **Alloggiati Web**).

Ci serve sapere:
1. Lo fai già? Con quale software o portale?
2. Hai un file XML di esempio che usi oggi?
3. Ti serve che Jlune generi **quel** formato, o ti basta l’export attuale + copia manuale?

Se serve integrazione specifica, la valutiamo come **voce extra** (tempi e costo da confermare).

Grazie!
BODY,
            ],
            [
                'title' => 'Serenella: vuoi notifiche anche su Telegram?',
                'body' => <<<'BODY'
Oggi le notifiche operative per te arrivano tramite **app installata + Attiva notifiche** in Progetto.

Max riceve anche **Telegram** (@jlune_notifiche_bot).

Vuoi essere aggiunta anche su Telegram (oltre alla PWA), o ti basta il telefono con l’app Jlune Admin?

Rispondi: «solo app» oppure «anche Telegram» (e avvia il bot se scegli Telegram).
BODY,
            ],
        ];
    }
}
