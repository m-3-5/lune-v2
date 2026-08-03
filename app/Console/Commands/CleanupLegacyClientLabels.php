<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\DevelopmentItem;
use App\Models\DevelopmentReply;
use App\Models\FaqEntry;
use App\Support\AppSettings;
use Illuminate\Console\Command;

/**
 * Comando una tantum: aggiorna i dati già salvati in DB che usavano ancora le vecchie
 * etichette/enum legati al vecchio nome del cliente e al vecchio branding, rinominati
 * nel codice ma non retroattivamente nei dati già scritti. Rimuove anche la guida
 * interna di sviluppo (funzionalità eliminata, non più letta da nessuna vista).
 * Idempotente — rieseguirlo dopo che i dati sono già stati sistemati non fa nulla.
 */
class CleanupLegacyClientLabels extends Command
{
    protected $signature = 'jlune:cleanup-legacy-client-labels';

    protected $description = 'Aggiorna una tantum i dati salvati con le vecchie etichette legate al vecchio cliente/branding';

    public function handle(): int
    {
        $updated = 0;

        $updated += DevelopmentItem::where('type', 'serenella_request')->update(['type' => DevelopmentItem::TYPE_CLIENT_REQUEST]);
        $updated += DevelopmentItem::where('type', 'question_for_serenella')->update(['type' => DevelopmentItem::TYPE_QUESTION_FOR_CLIENT]);
        $updated += DevelopmentItem::where('author', 'serenella')->update(['author' => 'client']);
        $updated += DevelopmentReply::where('author', 'serenella')->update(['author' => 'client']);

        DevelopmentItem::where('body', 'like', '%Ciao Serenella,%')->get()->each(function (DevelopmentItem $item) use (&$updated) {
            $item->update(['body' => str_replace('Ciao Serenella,', 'Ciao,', $item->body)]);
            $updated++;
        });

        DevelopmentItem::where('title', 'like', 'Serenella:%')->get()->each(function (DevelopmentItem $item) use (&$updated) {
            $item->update(['title' => ucfirst(trim(str_replace('Serenella:', '', $item->title)))]);
            $updated++;
        });

        if (in_array(AppSettings::get('mail_from_name'), ['Jlune', 'Gestione Appartamenti'], true)) {
            AppSetting::where('key', 'mail_from_name')->delete();
            AppSettings::clearCache();
            $updated++;
        }

        // Guida interna di sviluppo eliminata: la sua impostazione non serve più.
        $updated += AppSetting::where('key', 'app_guide')->delete();

        // "App in costruzione" / "Sito in manutenzione" eliminati: impostazione e FAQ collegate non servono più.
        $updated += AppSetting::where('key', 'under_construction')->delete();
        $updated += FaqEntry::where('question', 'like', '%App in costruzione%')
            ->orWhere('question', 'like', '%sito in manutenzione%')
            ->orWhere('question', 'like', '%vedo il sito vero durante la manutenzione%')
            ->orWhere('question', 'like', '%etichetta "auto" in Prova flusso%')
            ->delete();

        $this->info($updated > 0 ? "Aggiornati {$updated} elementi." : 'Nessun dato legacy trovato — niente da fare.');

        return self::SUCCESS;
    }
}
