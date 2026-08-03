<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_entries', function (Blueprint $table) {
            $table->id();
            $table->string('audience', 16); // 'admin' o 'guest'
            $table->string('question');
            $table->string('keywords')->nullable(); // parole extra per il match, separate da spazio
            $table->text('answer');
            // Per l'admin: percorso statico (es. /admin/arrivi). Per l'ospite: parola chiave
            // risolta a runtime con il token della prenotazione (es. ":documents", ":contract", ":assistenza").
            $table->string('link')->nullable();
            $table->string('link_label')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('audience');
        });

        $now = now();

        $admin = [
            ['Come approvo i documenti di un ospite?', 'approvare validare documenti carta identità', 'Vai su Arrivi e Documenti, apri la prenotazione, e usa i pulsanti Approva/Rifiuta su ogni documento caricato.', '/admin/arrivi', 'Vai ad Arrivi e Documenti'],
            ['Cosa succede se rifiuto un documento?', 'rifiutare documento sbagliato', "L'ospite riceve una notifica e può ricaricare il documento dalla sua pagina di check-in.", null, null],
            ['Come estraggo i dati con l\'IA?', 'estrazione document ai ocr', 'Dopo aver approvato tutti i documenti, apri la sezione Contratto della prenotazione e clicca "Estrai dati".', null, null],
            ['Come invio il contratto all\'ospite per la firma?', 'invio contratto firma lingua', 'Dopo l\'estrazione dati, scegli la lingua italiano/inglese e clicca "Contratto pronto — invia per la firma" nella sezione Contratto.', null, null],
            ['Come vedo se l\'ospite ha firmato il contratto?', 'firma contratto pdf', 'Ricevi una notifica con il PDF allegato appena firma, oppure lo trovi nella sezione Contratto della prenotazione o in Admin → Contratti.', '/admin/contratti', 'Vai a Contratti'],
            ['Dove trovo i ticket di assistenza da parte del cliente?', 'ticket assistenza domande cliente', 'Nella bacheca "Task e avanzamenti", raggiungibile da Progetto o da Sviluppo.', '/admin/progetto#task-board', 'Vai alla bacheca'],
            ['Come rispondo a un ticket di assistenza?', 'rispondere ticket cliente', 'Apri la voce nella bacheca task, scrivi la risposta nel campo dedicato e invia: chi ha scritto il ticket riceve un\'email con la tua risposta.', '/admin/progetto#task-board', 'Vai alla bacheca'],
            ['Come creo una prenotazione di prova?', 'prova flusso test finta prenotazione', 'Vai su Admin → Prova flusso, attiva l\'interruttore "Attiva prove", compila i dati e crea la prenotazione.', '/admin/prova', 'Vai a Prova flusso'],
            ['Come cambio quali email ricevono le notifiche?', 'email notifiche destinatari contatti', 'Vai su Progetto → Notifiche email e WhatsApp, e scrivi un indirizzo email per riga nel campo EMAIL.', '/admin/progetto', 'Vai a Progetto'],
            ['Perché non ricevo le notifiche via email?', 'email non arriva problema notifiche', 'Controlla che "Invio notifiche attivo" ed "Email" siano entrambi attivati in Progetto, e che l\'SMTP sia configurato in Notifiche → Email.', '/admin/progetto', 'Vai a Progetto'],
            ['Come collego le notifiche Telegram?', 'telegram bot collegare notifiche', 'Vai su Admin → Notifiche → Telegram e segui le istruzioni per collegare il bot al tuo account.', '/admin/notifiche/telegram', 'Vai a Notifiche Telegram'],
            ['Dove vedo i costi del progetto?', 'costi prezzo preventivo', 'I costi sono modificabili in Sviluppo → Modifica costi. Non sono più mostrati nella pagina Progetto in attesa di un nuovo accordo.', '/admin/sviluppo', 'Vai a Sviluppo'],
            ['Come faccio a sapere quando un ospite sta per uscire?', 'checkout uscita pulizie', 'Ogni mattina alle 8:00 arriva una notifica automatica con gli ospiti che fanno check-out quel giorno.', null, null],
            ['Come elimino una voce vecchia dalla bacheca task?', 'eliminare cancellare task vecchio', 'Da Sviluppo, apri la voce e usa il pulsante rosso "Elimina" (disponibile solo lì, non nella vista di Progetto).', '/admin/sviluppo', 'Vai a Sviluppo'],
            ['Come do a qualcuno del team l\'accesso a Sviluppo?', 'password sviluppo team accesso', 'Condividi la stessa password che usi tu per entrare in Sviluppo — è unica per tutto il team.', '/admin/sviluppo', 'Vai a Sviluppo'],
        ];

        $guest = [
            ['Come carico i miei documenti?', 'caricare documenti carta identità tessera sanitaria', 'Apri la sezione "Documenti" dal menu e carica la tua carta d\'identità (e tessera sanitaria se richiesta).', ':documents', 'Vai a Documenti'],
            ['Perché non riesco a caricare i documenti?', 'documenti bloccati non funziona pagamento', 'Il caricamento si sblocca solo dopo aver completato il pagamento dell\'acconto richiesto.', null, null],
            ['Dove trovo il link per pagare?', 'pagare acconto saldo checkfront link', 'Nella pagina principale del tuo check-in trovi il pulsante "Paga su Checkfront".', null, null],
            ['Quando posso firmare il contratto?', 'firma contratto quando', 'Puoi aprire la sezione contratto in qualsiasi momento, ma per firmare serve prima l\'approvazione dei tuoi documenti.', ':contract', 'Vai al Contratto'],
            ['Ho sbagliato a caricare un documento, come lo correggo?', 'documento sbagliato rifiutato ricaricare', 'Se un documento viene rifiutato ricevi una notifica: torna nella sezione Documenti e ricaricalo.', ':documents', 'Vai a Documenti'],
            ['A che ora posso entrare nell\'appartamento?', 'orario check-in ingresso quando', 'Puoi entrare dopo l\'orario di check-in indicato nella pagina principale, e solo dopo aver firmato il contratto.', null, null],
            ['Cosa succede dopo aver firmato il contratto?', 'dopo firma cosa succede pdf', 'Ricevi subito una copia del contratto firmato in PDF via email, e vedrai sbloccarsi le informazioni per l\'ingresso all\'orario previsto.', null, null],
            ['Devo inserire il codice fiscale?', 'codice fiscale italiano obbligatorio', 'Sì, se sei ospite italiano e non è già presente nei tuoi documenti: te lo chiediamo nella sezione contratto prima della firma.', ':contract', 'Vai al Contratto'],
            ['Come faccio a sapere se i documenti sono stati approvati?', 'documenti approvati stato verifica', 'Nella pagina principale del check-in vedrai comparire il messaggio "Documenti approvati" appena verificati.', null, null],
            ['Ho un problema che non trovo qui, cosa faccio?', 'assistenza aiuto contatto problema', 'Scrivici un ticket di assistenza: lo riceviamo subito e ti rispondiamo il prima possibile via email.', ':assistenza', 'Scrivi un ticket'],
        ];

        $rows = [];
        foreach ($admin as $i => [$question, $keywords, $answer, $link, $linkLabel]) {
            $rows[] = [
                'audience' => 'admin',
                'question' => $question,
                'keywords' => $keywords,
                'answer' => $answer,
                'link' => $link,
                'link_label' => $linkLabel,
                'position' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach ($guest as $i => [$question, $keywords, $answer, $link, $linkLabel]) {
            $rows[] = [
                'audience' => 'guest',
                'question' => $question,
                'keywords' => $keywords,
                'answer' => $answer,
                'link' => $link,
                'link_label' => $linkLabel,
                'position' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('faq_entries')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_entries');
    }
};
