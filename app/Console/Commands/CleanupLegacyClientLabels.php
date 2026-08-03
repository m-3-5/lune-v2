<?php

namespace App\Console\Commands;

use App\Models\DevelopmentItem;
use App\Models\DevelopmentReply;
use App\Support\AppSettings;
use Illuminate\Console\Command;

/**
 * Comando una tantum: aggiorna i dati già salvati in DB che usavano ancora le vecchie
 * etichette/enum legati al vecchio nome del cliente (rinominati nel codice, ma non
 * retroattivamente nei dati già scritti prima della migrazione a etichette generiche).
 * Idempotente — rieseguirlo dopo che i dati sono già stati sistemati non fa nulla.
 */
class CleanupLegacyClientLabels extends Command
{
    protected $signature = 'jlune:cleanup-legacy-client-labels';

    protected $description = 'Aggiorna una tantum i dati salvati con le vecchie etichette legate al nome del vecchio cliente';

    public function handle(): int
    {
        $updated = 0;

        $updated += DevelopmentItem::where('type', 'serenella_request')->update(['type' => DevelopmentItem::TYPE_CLIENT_REQUEST]);
        $updated += DevelopmentItem::where('type', 'question_for_serenella')->update(['type' => DevelopmentItem::TYPE_QUESTION_FOR_CLIENT]);
        $updated += DevelopmentItem::where('author', 'serenella')->update(['author' => 'client']);
        $updated += DevelopmentReply::where('author', 'serenella')->update(['author' => 'client']);

        $guide = AppSettings::get('app_guide', '');
        if (is_string($guide) && str_contains($guide, 'Serenella')) {
            $replacements = [
                'a Serenella' => 'al gestore',
                'di Serenella' => 'del gestore',
                'da Serenella' => 'dal gestore',
                'per Serenella' => 'per il gestore',
                'con Serenella' => 'con il gestore',
                'Serenella' => 'il gestore',
            ];
            AppSettings::set('app_guide', strtr($guide, $replacements));
            $updated++;
        }

        DevelopmentItem::where('body', 'like', '%Ciao Serenella,%')->get()->each(function (DevelopmentItem $item) use (&$updated) {
            $item->update(['body' => str_replace('Ciao Serenella,', 'Ciao,', $item->body)]);
            $updated++;
        });

        DevelopmentItem::where('title', 'like', 'Serenella:%')->get()->each(function (DevelopmentItem $item) use (&$updated) {
            $item->update(['title' => ucfirst(trim(str_replace('Serenella:', '', $item->title)))]);
            $updated++;
        });

        if (AppSettings::get('mail_from_name') === 'Jlune') {
            AppSettings::set('mail_from_name', 'Gestione Appartamenti');
            $updated++;
        }

        $guideAfterFirstPass = AppSettings::get('app_guide', '');
        if (is_string($guideAfterFirstPass) && str_contains($guideAfterFirstPass, 'Jlune')) {
            $jluneReplacements = [
                'Jlune App' => 'l\'app',
                'Jlune Check-in' => 'Check-in Ospiti',
                'Jlune Admin' => 'Admin',
                'Jlune collega' => 'L\'app collega',
                'cosa fa Jlune' => 'cosa fa l\'app',
                'aggiorna Jlune' => 'aggiorna l\'app',
                'Jlune sul telefono' => 'App sul telefono',
                'Jlune genera' => 'l\'app genera',
                'Jlune' => 'l\'app',
            ];
            AppSettings::set('app_guide', strtr($guideAfterFirstPass, $jluneReplacements));
            $updated++;
        }

        $this->info($updated > 0 ? "Aggiornati {$updated} elementi." : 'Nessun dato legacy trovato — niente da fare.');

        return self::SUCCESS;
    }
}
