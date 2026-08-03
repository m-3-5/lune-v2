<?php

namespace App\Support;

class ProjectGuide
{
    /**
     * @return array<int, array{id: string, icon: string, title: string, badge: string, body: string}>
     */
    public static function sections(): array
    {
        return [
            [
                'id' => 'sintesi',
                'icon' => '📋',
                'title' => 'In sintesi — cosa fa Jlune oggi',
                'badge' => 'Aggiornato giugno 2026',
                'body' => <<<'MD'
Jlune collega **Checkfront** (prenotazioni) con:
- l’**area ospite** (link personale `/checkin/…` sul telefono),
- il **pannello admin** (agenda, documenti, contratto, notifiche).

**Già operativo:** documenti, contratto con PDF, Document AI, promemoria automatici, PWA admin/ospite, Telegram ospiti, hub notifiche per canale.

**In attesa o da completare:** WhatsApp Business (costo Twilio), login admin, video ingresso, QR elettrodomestici, link pagamento Checkfront da verificare.
MD,
            ],
            [
                'id' => 'ospite-notifiche',
                'icon' => '📲',
                'title' => 'Esperienza ospite — come attiva le notifiche',
                'badge' => 'Guida passo passo',
                'body' => <<<'MD'
L’ospite apre il **link check-in** (email/SMS da Checkfront). Non serve password: il link è personale e sicuro.

### 1. Campanella nell’app (sempre disponibile)
In alto a destra c’è la **campanella**: promemoria su pagamento, documenti, contratto, firma. Funziona **dentro il browser** anche senza installare nulla.

### 2. App sul telefono + push (consigliato)
1. Compare il banner **«Jlune sul telefono»** (o voce menu **Scarica l’app**).
2. **Android (Chrome):** «Installa app» / Aggiungi a schermata Home.
3. **iPhone (Safari):** Condividi → **Aggiungi a schermata Home** (Apple non mostra il pulsante automatico).
4. Apre l’icona **Jlune Check-in** → **Attiva notifiche** → Consenti.
5. Riceve **vibrazione/suono** per promemoria importanti (se il team ha acceso push in Progetto).

### 3. Telegram (opzionale, molto comodo)
1. Nella home check-in compare il riquadro **«Ricevi avvisi su Telegram»**.
2. Preme **Collega Telegram** → si apre il bot `@jlune_notifiche_bot`.
3. Preme **Avvia** (Start) in Telegram.
4. Il bot collega Telegram **a quella prenotazione** — compare ✅ «Telegram collegato».
5. I promemoria arrivano su Telegram **solo se** il team ha acceso «Telegram ospiti» in Progetto.

**Nota:** ogni prenotazione va collegata una volta; se cambia telefono, ripete «Collega Telegram».

### 4. Email
Se attiva dal team: messaggi all’indirizzo **Checkfront** della prenotazione (conferme, PDF contratto, promemoria). Nessuna azione extra per l’ospite.

### 5. WhatsApp
**Non ancora attivo** in produzione (serve account Twilio + Meta a pagamento). Quando sarà pronto: messaggi al **cellulare Checkfront**, senza installare app. Per ora usare campanella, push o Telegram.

### Modalità «App in costruzione»
Se attiva (solo fase test): l’ospite **non riceve** email/WhatsApp/Telegram/push reali — vede solo la campanella in-app. Il team vede l’anteprima in admin.
MD,
            ],
            [
                'id' => 'checkfront',
                'icon' => '🔄',
                'title' => 'Prenotazioni Checkfront e date',
                'badge' => 'Fatto',
                'body' => <<<'MD'
- Webhook aggiorna Jlune quando cambia una prenotazione online.
- Agenda admin: **Oggi**, **Domani**, striscia 7 giorni, **In casa**, arrivi/partenze.
- Check-in **16:00** e check-out **10:00** (Europe/Rome).
MD,
            ],
            [
                'id' => 'documenti-contratto',
                'icon' => '📄',
                'title' => 'Documenti, contratto e PDF',
                'badge' => 'Fatto',
                'body' => <<<'MD'
- Upload CI/CF, approvazione admin, **Document AI** per estrazione dati.
- Contratto IT/EN, testo modificabile in **Testo contratto**, CF obbligatorio per italiani.
- Firma → **PDF archiviato**, email ospite con allegato, notifica admin.
- **Admin → Contratti:** download PDF firmati.
- Export JSON / CSV / XML dal dettaglio prenotazione.
MD,
            ],
            [
                'id' => 'notifiche-admin',
                'icon' => '🔔',
                'title' => 'Notifiche team',
                'badge' => 'Hub per canale',
                'body' => <<<'MD'
**Admin → Notifiche** — panoramica con card per ogni canale:

| Canale | Pagina | Costo |
|--------|--------|-------|
| Email | `/admin/notifiche/email` | Incluso casella |
| WhatsApp | `/admin/notifiche/whatsapp` | Twilio ~5–15 €/mese (in attesa) |
| Telegram | `/admin/notifiche/telegram` | Gratis |
| Push PWA | `/admin/notifiche/push` | Gratis |

Toggle on/off con **interruttori** in Progetto o nella pagina WhatsApp. **Di default tutto spento** finché non testate.

**Team:** Telegram `@jlune_notifiche_bot` + PWA admin + «Attiva notifiche» in Progetto.
MD,
            ],
            [
                'id' => 'whatsapp-attesa',
                'icon' => '💬',
                'title' => 'WhatsApp — in attesa (Twilio a pagamento)',
                'badge' => 'Da attivare',
                'body' => <<<'MD'
La configurazione è pronta in **Notifiche → WhatsApp** (credenziali, interruttori, test).

**Perché aspettiamo:** passare da sandbox a **numero Business ufficiale** richiede account Twilio + Meta approvato (costo per messaggio).

**Quando attiverete:** inserite SID, token e numero From ufficiale → test interno → accendete ospiti/admin.

Fino ad allora: **Telegram + push + email** coprono i promemoria ospite.
MD,
            ],
            [
                'id' => 'cron',
                'icon' => '⏰',
                'title' => 'Promemoria automatici (server)',
                'badge' => 'Fatto',
                'body' => <<<'MD'
- **10:00** — promemoria ospiti (pagamento, documenti, CF, firma) per arrivi entro 14 giorni.
- **03:30** — cancellazione documenti identità dopo check-out (privacy).

Richiede cron Laravel attivo su Plesk. Rispetta work in progress e toggle ospite.
MD,
            ],
            [
                'id' => 'document-ai',
                'icon' => '🔍',
                'title' => 'Google Document AI',
                'badge' => 'Attivo — account da trasferire',
                'body' => <<<'MD'
Estrae dati da CI e tessera sanitaria. **Non era nel preventivo base** — offerto dal team in avvio.

**Prossimo passo:** progetto Google Cloud del gestore con pagamento proprio. Istruzioni PDF in **Progetto → Scarica istruzioni Document AI**.

Export Polizia / Alloggiati: formato dedicato **da definire** (vedi ticket in Task).
MD,
            ],
            [
                'id' => 'accordo',
                'icon' => '🤝',
                'title' => 'Stato lavori e costi',
                'badge' => 'In attesa di nuovo accordo',
                'body' => <<<'MD'
Stato avanzamento e costi sono in aggiornamento — ne parliamo insieme e li rimettiamo qui appena ridefiniti.

Per qualsiasi domanda nel frattempo: usa **«Chiedi info su questo argomento»** in fondo al riquadro.
MD,
            ],
        ];
    }

    public static function findSection(string $id): ?array
    {
        foreach (self::sections() as $section) {
            if ($section['id'] === $id) {
                return $section;
            }
        }

        return null;
    }
}
