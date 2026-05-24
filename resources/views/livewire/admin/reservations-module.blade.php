<div>
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl mb-6 text-sm font-bold border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    @if ($context === 'dashboard')
        {{-- Tab Oggi / Domani --}}
        <div class="flex gap-3 mb-4">
            <button wire:click="setViewMode('today')"
                class="px-5 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all duration-300 {{ $viewMode === 'today' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600' }}">
                Oggi
            </button>
            <button wire:click="setViewMode('tomorrow')"
                class="px-5 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all duration-300 {{ $viewMode === 'tomorrow' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600' }}">
                Domani
            </button>
        </div>

        {{-- In casa oggi --}}
        <button type="button" wire:click="toggleInHouse"
            class="w-full mb-4 px-4 py-3 rounded-2xl bg-slate-100 border border-slate-200 text-left flex items-center justify-between active:scale-[0.99] transition-transform">
            <span class="text-[11px] font-black uppercase tracking-widest text-slate-600">
                In casa oggi
            </span>
            <span class="text-sm font-black text-indigo-700">
                {{ $inHouseCount }} / {{ $apartmentTotal ?: 8 }}
                <span class="text-slate-400 text-xs ml-1">{{ $showInHouse ? '▲' : '▼' }}</span>
            </span>
        </button>

        @if ($showInHouse)
            <div class="mb-4 space-y-2 pl-1">
                @forelse ($inHouse as $stay)
                    <div class="text-xs font-bold text-slate-600 flex justify-between gap-2">
                        <span class="text-indigo-900">{{ $stay->guestDisplayName() }}</span>
                        <span class="text-slate-500 shrink-0">{{ $stay->apartment->name ?? '—' }}</span>
                    </div>
                @empty
                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Nessun ospite in casa</p>
                @endforelse
            </div>
        @endif

        {{-- Agenda 7 giorni (da dopodomani) --}}
        <div class="mb-5 -mx-1 overflow-x-auto pb-1">
            <div class="flex gap-2 min-w-max px-1">
                @foreach ($agendaDays as $day)
                    <button type="button" wire:click="setViewMode('{{ $day['date'] }}')"
                        class="flex-shrink-0 px-3 py-2 rounded-2xl border text-center min-w-[4.5rem] transition-all {{ $viewMode === $day['date'] ? 'bg-indigo-600 border-indigo-600 text-white shadow-md' : 'bg-white border-gray-200 text-gray-600 hover:border-indigo-300' }}">
                        <span class="block text-[9px] font-black uppercase tracking-tighter opacity-90">{{ $day['label'] }}</span>
                        <span class="block text-[10px] font-bold mt-0.5 {{ $viewMode === $day['date'] ? 'text-indigo-100' : 'text-indigo-600' }}">
                            {{ $day['arrivals'] }}A {{ $day['departures'] }}P
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-3 px-1">{{ $sectionTitle }}</p>

        <div class="space-y-3">
            @forelse ($movements as $index => $row)
                @php
                    $res = $row['reservation'];
                    $isArrival = $row['type'] === 'arrival';
                    $rowKey = $row['type'].'-'.$res->id;
                    $expanded = $expandedKey === $rowKey;
                    $extras = $res->operationalExtrasLabels();
                @endphp
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-md">
                    <div class="p-4 flex items-start gap-3 cursor-pointer" wire:click="toggleExpanded('{{ $rowKey }}')">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-lg shrink-0 {{ $isArrival ? 'bg-indigo-600' : 'bg-amber-500' }}">
                            {{ $isArrival ? 'A' : 'P' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-black text-indigo-950 leading-tight truncate">
                                {{ $res->guestDisplayName() }}
                            </h4>
                            <p class="text-xs font-bold uppercase text-indigo-500 mt-0.5">
                                {{ $res->apartment->name ?? 'Appartamento' }}
                                <span class="text-gray-400 font-normal">· {{ $isArrival ? 'Arrivo' : 'Partenza' }}</span>
                            </p>
                            <p class="text-[10px] text-gray-500 font-bold mt-1">
                                {{ $res->check_in->format('d/m/Y') }} → {{ $res->check_out->format('d/m/Y') }}
                            </p>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full text-[9px] font-black uppercase">
                                    {{ $res->guestCountDisplay() }}
                                </span>
                                @foreach (array_slice($extras, 0, 2) as $label)
                                    <span class="bg-amber-50 text-amber-800 px-2 py-0.5 rounded-full text-[9px] font-bold">{{ $label }}</span>
                                @endforeach
                                @if (count($extras) > 2)
                                    <span class="text-[9px] text-gray-400 font-bold">+{{ count($extras) - 2 }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0" wire:click.stop>
                            @include('livewire.admin.partials.reservation-status-badges', ['res' => $res])
                            <a href="{{ route('admin.arrivi.show', $res->id) }}"
                                class="text-indigo-600 font-black text-[10px] uppercase underline tracking-widest hover:text-indigo-800 mt-1">
                                Apri
                            </a>
                        </div>
                    </div>

                    @if ($expanded)
                        <div class="px-4 pb-4 pt-0 border-t border-gray-50 bg-gray-50/80 text-[11px] space-y-2">
                            <p><span class="font-black text-gray-500 uppercase text-[9px]">Codice</span> {{ $res->booking_code }} · #{{ $res->checkfront_booking_id }}</p>
                            @if ($res->guest_email)
                                <p><span class="font-black text-gray-500 uppercase text-[9px]">Email</span> {{ $res->guest_email }}</p>
                            @endif
                            @if ($res->guest_phone)
                                <p><span class="font-black text-gray-500 uppercase text-[9px]">Tel</span> {{ $res->guest_phone }}</p>
                            @endif
                            @if (count($extras) > 0)
                                <p><span class="font-black text-gray-500 uppercase text-[9px]">Extra</span> {{ implode(' · ', $extras) }}</p>
                            @endif
                            @if ($res->checkfrontField('note'))
                                <p><span class="font-black text-gray-500 uppercase text-[9px]">Note</span> {{ $res->checkfrontField('note') }}</p>
                            @endif
                            @if ($res->adults || $res->children)
                                <p class="text-gray-500">Adulti {{ $res->adults ?? 0 }} · Bambini {{ $res->children ?? 0 }}</p>
                            @endif
                        </div>
                    @else
                        <p class="px-4 pb-2 text-[9px] text-gray-400 font-bold uppercase tracking-wider">Tap per dettagli</p>
                    @endif
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 font-bold uppercase text-xs tracking-[0.2em] bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                    Nessun arrivo o partenza in questa data
                </div>
            @endforelse
        </div>

    @else
        {{-- Pagina Arrivi e documenti --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <button wire:click="setViewMode('upcoming')"
                class="px-5 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all {{ $viewMode === 'upcoming' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-400' }}">
                Future
            </button>
            <button wire:click="setViewMode('archive')"
                class="px-5 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all {{ $viewMode === 'archive' ? 'bg-slate-600 text-white shadow-md' : 'bg-gray-100 text-gray-400' }}">
                Archivio
            </button>
            <button wire:click="setViewMode('cancelled')"
                class="px-5 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all {{ $viewMode === 'cancelled' ? 'bg-red-500 text-white shadow-md' : 'bg-gray-100 text-gray-400' }}">
                Cancellate
            </button>
        </div>

        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-3">{{ $sectionTitle }}</p>

        <div class="space-y-4">
            @forelse ($reservations as $res)
                @php
                    $rowKey = 'full-'.$res->id;
                    $expanded = $expandedKey === $rowKey;
                    $extras = $res->operationalExtrasLabels();
                    $isCancelled = $viewMode === 'cancelled';
                @endphp
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden {{ $isCancelled ? 'opacity-80' : '' }}">
                    <div class="p-5 flex items-start gap-4 cursor-pointer" wire:click="toggleExpanded('{{ $rowKey }}')">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-xl shrink-0 {{ $isCancelled ? 'bg-red-400' : 'bg-indigo-600' }}">
                            {{ $isCancelled ? '✕' : substr($res->guest_name ?? '?', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-black leading-none {{ $isCancelled ? 'text-gray-500 line-through' : 'text-indigo-950' }}">
                                {{ $res->guestDisplayName() }}
                            </h4>
                            <p class="text-xs font-bold uppercase mt-1 {{ $isCancelled ? 'text-gray-400' : 'text-indigo-500' }}">
                                {{ $res->apartment->name ?? 'Appartamento' }}
                            </p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">
                                {{ $res->check_in->format('d/m/Y') }} → {{ $res->check_out->format('d/m/Y') }}
                            </p>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full text-[9px] font-black uppercase">
                                    {{ $res->guestCountDisplay() }}
                                </span>
                                @foreach (array_slice($extras, 0, 2) as $label)
                                    <span class="bg-amber-50 text-amber-800 px-2 py-0.5 rounded-full text-[9px] font-bold">{{ $label }}</span>
                                @endforeach
                            </div>
                            @if ($res->guest_email || $res->guest_phone)
                                <p class="text-[10px] text-gray-400 mt-1 truncate max-w-[240px]">
                                    {{ $res->guest_email }}@if ($res->guest_phone) · {{ $res->guest_phone }}@endif
                                </p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0" wire:click.stop>
                            @if ($isCancelled)
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">Cancellata</span>
                                <button wire:click="deleteReservation({{ $res->id }})"
                                    wire:confirm="Eliminare definitivamente questa prenotazione?"
                                    class="text-red-500 font-black text-[10px] uppercase underline">
                                    Elimina
                                </button>
                            @else
                                @include('livewire.admin.partials.reservation-status-badges', ['res' => $res])
                                <a href="{{ route('admin.arrivi.show', $res->id) }}"
                                    class="text-indigo-600 font-black text-sm uppercase underline tracking-widest">
                                    Apri
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($expanded && ! $isCancelled)
                        <div class="px-5 pb-5 border-t border-gray-50 bg-gray-50/80 text-[11px] space-y-2">
                            <p>{{ $res->booking_code }} · {{ $res->checkfront_status }}</p>
                            @if (count($extras) > 0)
                                <p><span class="font-black text-[9px] uppercase text-gray-500">Extra</span> {{ implode(' · ', $extras) }}</p>
                            @endif
                            @if ($res->checkfrontField('note'))
                                <p>{{ $res->checkfrontField('note') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 font-bold uppercase text-xs tracking-[0.2em] bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                    @if ($viewMode === 'cancelled')
                        Nessuna prenotazione cancellata
                    @elseif ($viewMode === 'archive')
                        Archivio vuoto
                    @else
                        Nessuna prenotazione futura
                    @endif
                </div>
            @endforelse
        </div>
    @endif
</div>
