<?php

namespace App\Support;

/**
 * Testi predefiniti del corpo del contratto (clausole), modificabili
 * dall'admin nella pagina "Testo contratto". I segnaposto tra parentesi
 * quadre vengono sostituiti con i dati reali della prenotazione.
 */
class ContractTemplates
{
    /**
     * Elenco segnaposto disponibili con descrizione (per la legenda in admin).
     *
     * @return array<string, string>
     */
    public static function placeholders(): array
    {
        return [
            '[APPARTAMENTO]' => 'Nome dell\'appartamento',
            '[CHECK_IN]' => 'Data di arrivo (gg/mm/aaaa)',
            '[CHECK_OUT]' => 'Data di partenza (gg/mm/aaaa)',
            '[NOTTI]' => 'Numero di notti',
            '[PREZZO_TOTALE]' => 'Prezzo totale (es. 1.250,00)',
            '[CAPARRA]' => 'Caparra 30% (es. 375,00)',
            '[CODICE_PRENOTAZIONE]' => 'Codice prenotazione',
        ];
    }

    public static function defaultBody(string $locale): string
    {
        return $locale === 'en' ? static::defaultBodyEn() : static::defaultBodyIt();
    }

    protected static function defaultBodyIt(): string
    {
        return <<<'HTML'
<p><strong>1. IMMOBILE:</strong> Il Conduttore prende in locazione l'appartamento denominato <strong>"[APPARTAMENTO]"</strong> sito in Lungomare Palmasera 32, Cala Gonone. Possono soggiornare esclusivamente gli ospiti indicati nella prenotazione. Eventuali variazioni devono essere comunicate in anticipo.</p>
<p><strong>2. DETTAGLI DEL SOGGIORNO:</strong></p>
<ul>
<li><strong>Date:</strong> dal [CHECK_IN] al [CHECK_OUT]</li>
<li><strong>Notti:</strong> [NOTTI]</li>
<li><strong>Prezzo totale:</strong> € [PREZZO_TOTALE]</li>
<li><strong>Incluso nel prezzo:</strong> Pulizia finale, Wi-Fi, lenzuola e asciugamani, aria condizionata, utenze.</li>
<li><strong>La tassa di soggiorno non è inclusa e deve essere corrisposta separatamente.</strong></li>
</ul>
<p><strong>3. CONFERMA PRENOTAZIONE (CAPARRA):</strong> Per confermare la prenotazione è richiesta una caparra del 30% pari a <strong>€ [CAPARRA]</strong>. La prenotazione è considerata confermata solo al ricevimento del pagamento.</p>
<p><strong>4. CANCELLAZIONE E FORZA MAGGIORE:</strong> La prenotazione è non rimborsabile. Il Locatore non è responsabile per cancellazioni o modifiche del soggiorno dovute a cause indipendenti dalla propria volontà, quali condizioni meteorologiche, scioperi, cancellazioni di voli o altri disservizi legati ai trasporti. Tali circostanze non costituiscono motivo di rimborso. Tuttavia, a discrezione del Locatore, la caparra potrà essere convertita in voucher se viene dato un preavviso di almeno 7 giorni e non si tratta di mancata presentazione (no-show). In tal caso, il voucher potrà essere utilizzato per un nuovo soggiorno entro 12 mesi, per una prenotazione di pari valore o durata. Eventuali differenze di prezzo saranno adeguate. Il voucher non è rimborsabile e non è convertibile in denaro.</p>
<p><strong>5. SALDO:</strong> Il saldo è normalmente corrisposto in contanti all'arrivo. Se si sceglie il pagamento tramite bonifico, questo dovrà essere effettuato prima dell'arrivo e risultare accreditato per intero. Eventuali commissioni bancarie sono a carico del Conduttore: il Locatore dovrà ricevere l'intero importo pattuito senza trattenute. La tassa di soggiorno deve essere sempre pagata separatamente in contanti all'arrivo e non deve essere inclusa nel bonifico.</p>
<p><strong>6. CHECK-IN E ACCESSO:</strong> Il check-in è consentito dalle ore 16:00, mentre il check-out deve avvenire entro le ore 10:00. È disponibile un sistema di self check-in per maggiore flessibilità; le relative istruzioni verranno fornite dopo il pagamento e la registrazione. Eventuali richieste di check-in anticipato o check-out posticipato gratuiti (fino alle ore 13:00) potranno essere valutate su richiesta, in base alla disponibilità e all'organizzazione delle pulizie.</p>
<p><strong>7. REGISTRAZIONE:</strong> Tutti gli ospiti (compresi i minori) devono fornire un documento di identità valido prima dell'arrivo, come tassativamente previsto dalla normativa italiana.</p>
<p><strong>8. CURA DELL'IMMOBILE:</strong> Non è richiesto alcun deposito cauzionale. Si richiede tuttavia di trattare l'appartamento con cura e di segnalare eventuali problemi entro 24 ore dall'arrivo. In caso di danni o oggetti mancanti, i relativi costi di ripristino o sostituzione potranno essere addebitati al Conduttore.</p>
<p><strong>9. REGOLE DI SOGGIORNO:</strong> È severamente vietato fumare all'interno dei locali (il fumo è consentito esclusivamente negli spazi esterni). Gli animali non sono ammessi salvo accordo preventivo scritto. È richiesto il massimo rispetto del vicinato e della quiete pubblica nelle ore canoniche.</p>
<p><strong>SICUREZZA MINORI:</strong> I genitori o gli accompagnatori sono civilmente e penalmente responsabili della supervisione dei minori durante tutto il soggiorno. È severamente vietato lasciare i bambini incustoditi su balconi, scale o in altre aree potenzialmente pericolose della struttura.</p>
<p><strong>10. OSPITI E POSTI LETTO:</strong> Il numero di ospiti occupanti deve corrispondere esattamente a quanto indicato nella prenotazione. I costi sono basati sull'effettivo utilizzo dei posti letto: l'utilizzo di letti aggiuntivi, anche se non richiesti in anticipo, può comportare costi supplementari. I bambini fino a 3 anni non pagano solo se dormono nel letto con i genitori; l'utilizzo di un letto extra o di una culla comporta un costo aggiuntivo.</p>
<p><strong>11. PARTENZA ANTICIPATA:</strong> In caso di partenza anticipata del Conduttore, l'intero importo del soggiorno prenotato resta dovuto e non verranno effettuati rimborsi o compensazioni per le notti non usufruite.</p>
<p><strong>12. POSIZIONE DELL'IMMOBILE:</strong> L'immobile si trova sul lungomare, in una zona centrale, vicino a ristoranti e alla passeggiata serale. Pur essendo dotato di infissi insonorizzati ad alta efficienza, con la sottoscrizione del presente contratto il Conduttore prende atto che il silenzio assoluto non è garantito.</p>
<p><strong>13. RESPONSABILITÀ:</strong> Il Locatore non si assume alcuna responsabilità per il furto, il deterioramento o lo smarrimento di oggetti personali lasciati incustoditi all'interno o all'esterno dell'immobile.</p>
<p><strong>14. FORO COMPETENTE:</strong> Per qualsiasi controversia legale derivante dall'interpretazione o dall'esecuzione del presente contratto, è competente in via esclusiva il Foro di Nuoro (Italia).</p>
HTML;
    }

    protected static function defaultBodyEn(): string
    {
        return <<<'HTML'
<p><strong>1. PROPERTY:</strong> The Guest rents the apartment located at Lungomare Palmasera 32, Cala Gonone, named <strong>"[APPARTAMENTO]"</strong>. Only registered guests are allowed to stay. If anything changes, please inform us in advance.</p>
<p><strong>2. STAY DETAILS:</strong></p>
<ul>
<li><strong>Dates:</strong> from [CHECK_IN] to [CHECK_OUT]</li>
<li><strong>Nights:</strong> [NOTTI]</li>
<li><strong>Total price:</strong> € [PREZZO_TOTALE]</li>
<li><strong>Included in the price:</strong> Final cleaning, Wi-Fi, bed linen & towels, air conditioning, utilities.</li>
<li><strong>Tourist tax is not included and must be paid separately.</strong></li>
</ul>
<p><strong>3. BOOKING CONFIRMATION (DEPOSIT):</strong> A 30% deposit equal to <strong>€ [CAPARRA]</strong> is required to confirm the booking. The reservation is confirmed once the payment is received.</p>
<p><strong>4. CANCELLATION POLICY AND FORCE MAJEURE:</strong> The reservation is non-refundable. The Landlord shall not be held responsible for cancellations or changes to the stay due to circumstances beyond their control, such as weather conditions, transport strikes, flight cancellations, or similar events. Such circumstances do not constitute grounds for a refund. However, at the Landlord's discretion, the deposit may be converted into a voucher if: the Guest provides at least 7 days' notice before arrival and the reservation is not a no-show. If these conditions are met, the deposit can be used for a new stay within 12 months, for a reservation of similar value or duration. Any price difference for new dates will be adjusted accordingly. The voucher is non-refundable and cannot be converted into cash.</p>
<p><strong>5. BALANCE PAYMENT:</strong> The balance is normally paid in cash upon arrival. If you prefer to pay by bank transfer, the payment must be completed before arrival and must be received in full. Any bank or transfer fees are at the Guest's expense. The Landlord must receive the full agreed amount without deductions. The tourist tax must always be paid separately in cash upon arrival and should not be included in the bank transfer.</p>
<p><strong>6. CHECK-IN & ACCESS:</strong> Check-in is from 4:00 PM, while check-out must be by 10:00 AM. We offer a self check-in system for flexibility; instructions will be shared after payment and registration. Early check-in or late check-out (up to 1:00 PM) may be available upon request, subject to availability and cleaning schedule.</p>
<p><strong>7. REGISTRATION:</strong> All guests (including children) are required to provide ID documents/Passports before arrival, as required by Italian law.</p>
<p><strong>8. CARE OF THE PROPERTY:</strong> No security deposit is required. We kindly ask you to treat the apartment with care and inform us if anything needs attention within 24 hours of arrival. In case of damage or missing items, the corresponding costs may be charged.</p>
<p><strong>9. HOUSE RULES:</strong> No smoking inside (outdoor areas may be used). Pets are not allowed unless agreed in advance. Please respect neighbors and quiet hours.</p>
<p><strong>CHILD SAFETY:</strong> Parents or guardians are responsible for supervising children at all times during the stay. Children must not be left unattended on balconies, staircases, or in any potentially hazardous areas.</p>
<p><strong>10. OCCUPANCY AND EXTRA BEDS:</strong> The number of guests must match the reservation. Charges are based on the actual use of sleeping spaces. The use of extra beds, even if not previously requested, may result in additional charges. Children up to 3 years old stay free of charge when sharing a bed with their parents. If a separate bed or cot is used, additional charges may apply. If anything changes, please inform us in advance so we can arrange it properly.</p>
<p><strong>11. EARLY DEPARTURE:</strong> If the stay is shortened for any reason, the total agreed amount remains due and no refund will be provided for unused nights.</p>
<p><strong>12. LOCATION:</strong> The property is located on the seafront, in a central and lively area, close to restaurants and the evening promenade. Although equipped with sound-insulated windows, by booking the Guest acknowledges that absolute silence cannot be guaranteed.</p>
<p><strong>13. LIABILITY:</strong> The Landlord is not responsible for loss or theft of personal belongings.</p>
<p><strong>14. JURISDICTION:</strong> Any dispute is subject to the exclusive jurisdiction of the Court of Nuoro (Italy).</p>
HTML;
    }
}
