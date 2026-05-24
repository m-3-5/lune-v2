<div class="relative" wire:click.outside="$set('open', false)">
    <button type="button" wire:click="toggle"
        class="relative w-10 h-10 rounded-full bg-indigo-600 border-2 border-indigo-400 flex items-center justify-center text-white hover:bg-indigo-500 transition-colors"
        aria-label="Notifiche">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-amber-400 text-indigo-950 text-[9px] font-black flex items-center justify-center">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 top-12 w-[min(100vw-2rem,22rem)] bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-900">Notifiche</span>
                @if ($unreadCount > 0)
                    <button type="button" wire:click="markAllRead"
                        class="text-[9px] font-bold uppercase text-indigo-600 underline">
                        Segna tutte lette
                    </button>
                @endif
            </div>

            <ul class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                @forelse ($notifications as $notification)
                    @php
                        $isUnread = $notification->read_at === null;
                        $url = $notification->reservation_id
                            ? route('admin.arrivi.show', $notification->reservation_id)
                            : route('admin.arrivi');
                    @endphp
                    <li class="{{ $isUnread ? 'bg-indigo-50/50' : '' }}">
                        <a href="{{ $url }}" wire:click="markRead({{ $notification->id }})"
                            class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                            <p class="text-xs font-black text-indigo-950 leading-tight">{{ $notification->title }}</p>
                            @if ($notification->body)
                                <p class="text-[10px] text-gray-500 mt-1 line-clamp-2">{{ $notification->body }}</p>
                            @endif
                            <p class="text-[9px] text-gray-400 mt-1 font-bold uppercase">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-[10px] font-bold uppercase text-gray-400 tracking-wider">
                        Nessuna notifica
                    </li>
                @endforelse
            </ul>

            <div class="px-4 py-2 border-t bg-gray-50 text-center">
                <a href="{{ route('admin.arrivi') }}" class="text-[10px] font-black uppercase text-indigo-600 underline">
                    Vai ad arrivi e documenti
                </a>
            </div>
        </div>
    @endif
</div>
