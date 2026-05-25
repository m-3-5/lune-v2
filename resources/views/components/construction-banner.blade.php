@php
    $underConstruction = \App\Support\AppSettings::underConstruction();
@endphp
@if ($underConstruction)
    <div class="bg-amber-500 text-amber-950 text-center text-xs sm:text-sm font-bold px-4 py-2 shadow-md z-[60] relative">
        🚧 Jlune — <span class="uppercase tracking-wide">App in costruzione</span>
        <span class="font-normal hidden sm:inline"> · Le notifiche ai clienti sono disattivate; in admin vedi le anteprime.</span>
    </div>
@endif
