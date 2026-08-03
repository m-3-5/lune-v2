@props(['title' => 'Sezione riservata'])

<div class="md:hidden bg-amber-50 border border-amber-200 rounded-3xl p-8 text-center">
    <div class="text-4xl mb-3">💻</div>
    <p class="font-black text-amber-950">{{ $title }}</p>
    <p class="text-sm text-amber-800 mt-2">Apri questa pagina da un computer per configurarla — impostazioni tecniche, meglio non toccarle dal telefono.</p>
</div>

<div class="hidden md:block">
    {{ $slot }}
</div>
