<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value');
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            'under_construction' => false,
            'admin_emails' => [
                'tuaemail@esempio.it — da aggiornare',
            ],
            'admin_phones' => [
                '+39 … — da aggiornare',
            ],
            'app_guide' => $this->defaultGuide(),
            'project_base_cost' => 3800,
            'project_cost_entries' => [
                [
                    'label' => 'Modifiche importanti (agenda, sync Checkfront, notifiche, contratto IT/EN)',
                    'amount' => 250,
                    'date' => '2026-05-23',
                ],
            ],
        ];

        foreach ($defaults as $key => $value) {
            DB::table('app_settings')->insert([
                'key' => $key,
                'value' => json_encode($value),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }

    protected function defaultGuide(): string
    {
        return <<<'GUIDE'
# Guida App (bozza)

## Cosa fa l'app
Collega le prenotazioni Checkfront con l'area ospite e il pannello admin.

### Area ospite (link con token)
- Pagamento / promemoria Checkfront
- Caricamento documenti (identità, CF se italiano)
- Firma contratto di locazione turistica (IT o EN)
- Video e istruzioni dell'appartamento
- Notifiche in-app (campanella)

### Pannello admin
- Agenda arrivi/partenze (Oggi, Domani, prossimi 7 giorni)
- Verifica documenti, estrazione dati (IA in sviluppo)
- Contratto: anteprima e «invia per firma»
- Notifiche per nuovi documenti e prenotazioni

## Modalità «App in costruzione»
Quando attiva (solo sviluppo): banner visibile, nessun messaggio reale a clienti (email/WhatsApp/app ospite). Le notifiche che avrebbero ricevuto gli ospiti compaiono in admin come anteprima.

## Sync Checkfront
- Webhook + `php artisan checkfront:import-log` per allineare prenotazioni
- `php artisan checkfront:sync-items` per gli appartamenti
- `php artisan jlune:status` per verificare arrivi e date

_Aggiornare questo testo man mano che aggiungiamo funzioni._
GUIDE;
    }
};
