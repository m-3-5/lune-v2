<div class="contract-document prose prose-sm max-w-none text-gray-800">
    <div class="text-center mb-6">
        <p class="text-lg font-black uppercase tracking-wide">Contratto di Locazione Breve per Finalità Turistica</p>
        <p class="text-xs text-gray-500">(ai sensi della Legge 431/1998, artt. 1571 e seguenti del Codice Civile e D.L. 50/2017)</p>
        <p class="text-sm font-semibold mt-2">Prenotazione {{ $reservation->booking_code }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4 not-prose text-sm">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <p class="font-bold text-indigo-900 uppercase border-b pb-1 mb-2">Locatore</p>
            <p class="font-semibold">Serenella Marongiu</p>
            <p>Lungomare Palmasera 32 - Cala Gonone (Italia)</p>
            <p class="text-xs text-gray-600 mt-1">info@appartamentijlune.com | +39 349 5377378</p>
        </div>
        
        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
            <p class="font-bold text-indigo-900 uppercase border-b pb-1 mb-2">Conduttore / Ospiti</p>
            <ul class="space-y-2">
                @foreach($guests as $guest)
                    <li>
                        <strong>Ospite {{ $guest['slot'] }}:</strong> 
                        {{ $guest['data']['first_name'] ?? '' }} {{ $guest['data']['last_name'] ?? '' }}
                        @if(!empty($guest['data']['birth_date']))
                            <span class="text-gray-600 text-xs">(nato/a il {{ $guest['data']['birth_date'] }})</span>
                        @endif
                        <br>
                        @if($guest['is_foreigner'])
                            <span class="text-amber-800 text-xs font-semibold">Cittadino straniero (CF non richiesto)</span>
                        @endif
                        @if(!empty($guest['data']['document_number']))
                            <span class="text-gray-600 text-xs">Doc: {{ $guest['data']['document_number'] }}</span>
                        @endif
                        @if(!$guest['is_foreigner'] && !empty($guest['data']['tax_code']))
                            <span class="font-mono text-xs block">CF: {{ $guest['data']['tax_code'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="space-y-4 text-justify leading-relaxed text-sm">
        <p>
            <strong>1. IMMOBILE:</strong> Il Conduttore prende in locazione l'appartamento denominato 
            <strong>"{{ $apartment->name ?? 'Alloggio' }}"</strong> sito in Lungomare Palmasera 32, Cala Gonone. 
            Possono soggiornare esclusivamente gli ospiti indicati nella prenotazione. Eventuali variazioni devono essere comunicate in anticipo.
        </p>

        <p>
            <strong>2. DETTAGLI DEL SOGGIORNO:</strong><br>
            • <strong>Date:</strong> dal {{ $reservation->check_in->format('d/m/Y') }} al {{ $reservation->check_out->format('d/m/Y') }}<br>
            • <strong>Notti:</strong> {{ (int) $reservation->check_in->diffInDays($reservation->check_out) }}<br>
            • <strong>Prezzo totale:</strong> € {{ number_format($reservation->total_price, 2, ',', '.') }}<br>
            • <strong>Incluso nel prezzo:</strong> Pulizia finale, Wi-Fi, lenzuola e asciugamani, aria condizionata, utenze.<br>
            <span class="text-amber-700 font-medium">La tassa di soggiorno non è inclusa e deve essere corrisposta separatamente.</span>
        </p>

        <p>
            <strong>3. CONFERMA PRENOTAZIONE (CAPARRA):</strong> Per confermare la prenotazione è richiesta una caparra del 30% pari a 
            <strong>€ {{ number_format($reservation->total_price * 0.30, 2, ',', '.') }}</strong>. 
            La prenotazione è considerata confermata solo al ricevimento del pagamento.
        </p>

        <p>
            <strong>4. CANCELLAZIONE E FORZA MAGGIORE:</strong> La prenotazione è non rimborsabile. Il Locatore non è responsabile per cancellazioni o modifiche del soggiorno dovute a cause indipendenti dalla propria volontà, quali condizioni meteorologiche, scioperi, cancellazioni di voli o altri disservizi legati ai trasporti. Tali circostanze non costituiscono motivo di rimborso. Tuttavia, a discrezione del Locatore, la caparra potrà essere convertita in voucher se viene dato un preavviso di almeno 7 giorni e non si tratta di mancata presentazione (no-show). In tal caso, il voucher potrà essere utilizzato per un nuovo soggiorno entro 12 mesi, per una prenotazione di pari valore o durata. Eventuali differenze di prezzo saranno adeguate. Il voucher non è rimborsabile e non è convertibile in denaro.
        </p>

        <p>
            <strong>5. SALDO:</strong> Il saldo è normalmente corrisposto in contanti all'arrivo. Se si sceglie il pagamento tramite bonifico, questo dovrà essere effettuato prima dell'arrivo e risultare accreditato per intero. Eventuali commissioni bancarie sono a carico del Conduttore: il Locatore dovrà ricevere l'intero importo pattuito senza trattenute. La tassa di soggiorno deve essere sempre pagata separatamente in contanti all'arrivo e non deve essere inclusa nel bonifico.
        </p>

        <p>
            <strong>6. CHECK-IN E ACCESSO:</strong> Il check-in è consentito dalle ore 16:00, mentre il check-out deve avvenire entro le ore 10:00. È disponibile un sistema di self check-in per maggiore flessibilità; le relative istruzioni verranno fornite dopo il pagamento e la registrazione. Eventuali richieste di check-in anticipato o check-out posticipato gratuiti (fino alle ore 13:00) potranno essere valutate su richiesta, in base alla disponibilità e all'organizzazione delle pulizie.
        </p>

        <p>
            <strong>7. REGISTRAZIONE:</strong> Tutti gli ospiti (compresi i minori) devono fornire un documento di identità valido prima dell'arrivo, come tassativamente previsto dalla normativa italiana.
        </p>

        <p>
            <strong>8. CURA DELL'IMMOBILE:</strong> Non è richiesto alcun deposito cauzionale. Si richiede tuttavia di trattare l'appartamento con cura e di segnalare eventuali problemi entro 24 ore dall'arrivo. In caso di danni o oggetti mancanti, i relativi costi di ripristino o sostituzione potranno essere addebitati al Conduttore.
        </p>

        <p>
            <strong>9. REGOLE DI SOGGIORNO:</strong> È severamente vietato fumare all'interno dei locali (il fumo è consentito esclusivamente negli spazi esterni). Gli animali non sono ammessi salvo accordo preventivo scritto. È richiesto il massimo rispetto del vicinato e della quiete pubblica nelle ore canoniche.
        </p>

        <p>
            <strong>SICUREZZA MINORI:</strong> I genitori o gli accompagnatori sono civilmente e penalmente responsabili della supervisione dei minori durante tutto il soggiorno. È severamente vietato lasciare i bambini incustoditi su balconi, scale o in altre aree potenzialmente pericolose della struttura.
        </p>

        <p>
            <strong>10. OSPITI E POSTI LETTO:</strong> Il numero di ospiti occupanti deve corrispondere esattamente a quanto indicato nella prenotazione. I costi sono basati sull'effettivo utilizzo dei posti letto: l'utilizzo di letti aggiuntivi, anche se non richiesti in anticipo, può comportare costi supplementari. I bambini fino a 3 anni non pagano solo se dormono nel letto con i genitori; l'utilizzo di un letto extra o di una culla comporta un costo aggiuntivo.
        </p>

        <p>
            <strong>11. PARTENZA ANTICIPATA:</strong> In caso di partenza anticipata del Conduttore, l'intero importo del soggiorno prenotato resta dovuto e non verranno effettuati rimborsi o compensazioni per le notti non usufruite.
        </p>

        <p>
            <strong>12. POSIZIONE DELL'IMMOBILE:</strong> L'immobile si trova sul lungomare, in una zona centrale, vicino a ristoranti e alla passeggiata serale. Pur essendo dotato di infissi insonorizzati ad alta efficienza, con la sottoscrizione del presente contratto il Conduttore prende atto che il silenzio assoluto non è garantito.
        </p>

        <p>
            <strong>13. RESPONSABILITÀ:</strong> Il Locatore non si assume alcuna responsabilità per il furto, il deterioramento o lo smarrimento di oggetti personali lasciati incustoditi all'interno o all'esterno dell'immobile.
        </p>

        <p>
            <strong>14. FORO COMPETENTE:</strong> Per qualsiasi controversia legale derivante dall'interpretazione o dall'esecuzione del presente contratto, è competente in via esclusiva il Foro di Nuoro (Italia).
        </p>
    </div>

    <div class="mt-8 border-t pt-4 grid grid-cols-2 text-xs text-gray-500">
        <div>Contratto generato elettronicamente il {{ now()->format('d/m/Y H:i') }}</div>
        <div class="text-right">JLune Management System</div>
    </div>
</div>