@php
    $openQuestions = \Database\Seeders\AppGuideSeeder::openQuestionsForClient();
@endphp
<div class="bg-amber-50 border border-amber-200 rounded-[2rem] p-5 shadow-sm">
    <h2 class="text-sm font-black text-amber-950 uppercase tracking-tight mb-2">Domande per il cliente</h2>
    <p class="text-xs text-amber-900/80 mb-3">
        Stesse voci in <a href="{{ route('admin.progetto') }}" class="font-bold underline">Progetto → task</a>
        (filtro «Per il cliente» / domande aperte). Rispondi sotto ogni task.
    </p>
    <ul class="space-y-2 text-xs text-amber-950">
        @foreach ($openQuestions as $i => $q)
            <li class="flex gap-2">
                <span class="font-black text-amber-600">{{ $i + 1 }}.</span>
                <span>{{ $q['title'] }}</span>
            </li>
        @endforeach
    </ul>
</div>
