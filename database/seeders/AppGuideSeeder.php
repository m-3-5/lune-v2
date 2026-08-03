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

        foreach (self::openQuestionsForClient() as $question) {
            DevelopmentItem::firstOrCreate(
                [
                    'type' => DevelopmentItem::TYPE_QUESTION_FOR_CLIENT,
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
                'type' => DevelopmentItem::TYPE_CLIENT_REQUEST,
                'title' => 'Link pagamento Checkfront reale (esempio per cliente)',
            ],
            [
                'status' => DevelopmentItem::STATUS_OPEN,
                'body' => <<<'BODY'
Ciao,

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
# Guida Jlune

La guida dettagliata è in **Progetto e task** → riquadri apribili (giugno 2026).

Ogni argomento ha il pulsante **«Chiedi info su questo argomento»** per aprire un ticket al team (notifica Telegram/push admin).

_Contenuto aggiornato nel codice (`ProjectGuide`) — rieseguire `php artisan db:seed --class=AppGuideSeeder` solo per i task iniziali._
GUIDE;
    }

    /**
     * @return array<int, array{title: string, body: string}>
     */
    public static function openQuestionsForClient(): array
    {
        return [
            [
                'title' => 'Logo app: quale icona sulla home del telefono?',
                'body' => <<<'BODY'
Ciao,

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
                'title' => 'Ospiti: Telegram è attivo — va bene così?',
                'body' => <<<'BODY'
**Aggiornamento giugno 2026:** gli ospiti possono collegare Telegram dal check-in (pulsante «Collega Telegram» → bot `@jlune_notifiche_bot`).

Restano disponibili anche:
- campanella **dentro l’app**,
- **push** se installano la PWA e attivano notifiche.

**WhatsApp ospiti** arriverà quando attiverete Twilio Business (a pagamento).

Ci serve solo un OK operativo:
1. Vuoi che accendiamo «Telegram ospiti» in Progetto per andare live?
2. Preferisci comunicarlo tu agli ospiti («collega Telegram per i promemoria») o lo lasciamo opzionale?

Risposta libera.
BODY,
            ],
            [
                'title' => 'Export Polizia: usi Alloggiati Web o altro formato?',
                'body' => <<<'BODY'
Ciao,

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
                'title' => 'Vuoi notifiche anche su Telegram?',
                'body' => <<<'BODY'
Oggi le notifiche operative per te arrivano tramite **app installata + Attiva notifiche** in Progetto.

Il team riceve anche **Telegram** (@jlune_notifiche_bot).

Vuoi essere aggiunta anche su Telegram (oltre alla PWA), o ti basta il telefono con l’app Jlune Admin?

Rispondi: «solo app» oppure «anche Telegram» (e avvia il bot se scegli Telegram).
BODY,
            ],
        ];
    }
}
