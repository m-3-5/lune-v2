{!! $alertBody !!}

@if ($actionUrl && ! str_contains($alertBody, $actionUrl))
---
Apri in admin: {!! $actionUrl !!}
@endif

— Il team di M 3.5 S.R.L.
