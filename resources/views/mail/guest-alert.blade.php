{!! $alertTitle !!}
=====

{!! $alertBody !!}

@if ($actionUrl && ! str_contains($alertBody, $actionUrl))
Apri: {!! $actionUrl !!}
@endif

—
Messaggio automatico. Non rispondere a questa email.
