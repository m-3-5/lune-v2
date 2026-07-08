Jlune
=====

{!! $alertTitle !!}

{!! $alertBody !!}

@if ($actionUrl && ! str_contains($alertBody, $actionUrl))
Apri: {!! $actionUrl !!}
@endif

—
Messaggio automatico da Jlune. Non rispondere a questa email.
