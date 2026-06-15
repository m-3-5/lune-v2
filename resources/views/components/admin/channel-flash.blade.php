@if (session()->has('channel_message'))
    <div class="bg-green-50 text-green-800 p-3 rounded-xl text-sm font-bold border border-green-200">
        {{ session('channel_message') }}
    </div>
@endif
