@php
    $underConstruction = \App\Support\AppSettings::underConstruction();
@endphp
@if ($underConstruction)
    <div class="bg-amber-500 text-amber-950 text-center text-xs sm:text-sm font-bold px-4 py-2 shadow-md z-[60] relative">
        🚧 <span class="uppercase tracking-wide">Work in progress — App Jlune</span>
        <span class="font-normal hidden sm:inline">
            · Stiamo completando l'app: ai clienti non partono messaggi automatici, tranne le prenotazioni segnate come prova notifiche.
        </span>
    </div>
@endif
