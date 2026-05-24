@if ($res->is_paid)
    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
        {{ $res->paymentLabel() }}
    </span>
@else
    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
        Non pagato
    </span>
@endif
@if ($res->documents_validated)
    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
        Documenti OK
    </span>
@elseif ($res->hasDocumentsPendingReview())
    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
        Doc. da verificare
    </span>
@elseif ($res->is_paid)
    <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
        Attesa documenti
    </span>
@endif
