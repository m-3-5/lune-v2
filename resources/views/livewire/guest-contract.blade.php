<div class="space-y-6">
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-2xl text-sm">{{ session('success') }}</div>
    @endif

    @if (! $reservation->extracted_guests)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
            <p class="text-amber-900 font-bold">Contratto in preparazione</p>
            <p class="text-sm text-amber-800 mt-2">Serenella sta completando i dati dalla tua prenotazione. Riceverai una notifica quando potrai firmare.</p>
        </div>
    @else
        @if (count($missingTax) > 0)
            <div class="bg-white rounded-2xl border border-indigo-200 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-indigo-950 mb-2">Codice fiscale per il contratto</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Per gli ospiti italiani il codice fiscale è necessario solo per firmare il contratto.
                    Puoi scriverlo qui o caricare la tessera sanitaria.
                </p>

                <form wire:submit="saveTaxCodes" class="space-y-4">
                    @foreach ($missingTax as $guest)
                        @php $slot = (int) ($guest['slot'] ?? 0); @endphp
                        <div class="border border-gray-100 rounded-2xl p-4">
                            <label class="block text-sm font-bold text-gray-800 mb-2">
                                {{ $guest['name'] ?? "Ospite {$slot}" }}
                            </label>
                            <input type="text" wire:model="taxCodes.{{ $slot }}"
                                placeholder="RSSMRA80A01H501U"
                                class="w-full uppercase font-mono border-gray-300 rounded-lg text-sm"
                                maxlength="16">
                            @error("taxCodes.{$slot}")
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <div class="mt-3">
                                <button type="button" wire:click="$set('taxUploadSlot', {{ $slot }})"
                                    class="text-xs font-bold text-indigo-600 underline">
                                    Oppure carica foto/PDF tessera
                                </button>
                                @if ($taxUploadSlot === $slot)
                                    <input type="file" wire:model="taxUploadFile" accept="image/*,.pdf"
                                        class="mt-2 text-xs w-full">
                                    <div wire:loading wire:target="taxUploadFile" class="text-xs text-gray-500 mt-1">Caricamento…</div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <button type="submit"
                        class="w-full py-3 bg-indigo-600 text-white font-bold rounded-2xl text-sm">
                        Salva codice fiscale
                    </button>
                </form>
            </div>
        @endif

        @if (! $reservation->contract_ready_for_guest)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
                <p class="text-amber-900 font-bold">In attesa di invio da Serenella</p>
                <p class="text-sm text-amber-800 mt-2">
                    Lingua prevista:
                    <strong>{{ $reservation->contract_locale === 'en' ? 'English' : 'Italiano' }}</strong>.
                    Ti avviseremo quando il contratto sarà pronto per la firma.
                </p>
            </div>
        @elseif (! $reservation->requiresTaxCodeForContract())
            <div class="bg-white rounded-2xl border-t-4 border-green-600 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <p class="text-sm text-gray-600">
                        Lingua contratto:
                        <strong>{{ $reservation->contract_locale === 'en' ? 'English' : 'Italiano' }}</strong>
                    </p>
                </div>
                @php
                    $guestsForContract = collect($reservation->extracted_guests)->map(fn ($g) => [
                        'name' => $g['name'] ?? '',
                        'is_foreigner' => $g['is_foreigner'] ?? false,
                        'data' => $g['data'] ?? [],
                    ])->all();
                @endphp
                <livewire:contract-manager :reservation="$reservation" :guests="$guestsForContract" :key="'contract-'.$reservation->id" />
            </div>
        @endif
    @endif
</div>
