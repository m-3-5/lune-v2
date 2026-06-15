@props([
    'label' => '',
    'hint' => null,
    'color' => 'emerald',
])

@php
    $trackOn = match ($color) {
        'sky' => 'peer-checked:bg-sky-600',
        'indigo' => 'peer-checked:bg-indigo-600',
        'violet' => 'peer-checked:bg-violet-600',
        default => 'peer-checked:bg-emerald-600',
    };
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'flex items-center justify-between gap-4 py-1.5']) }}>
    <div class="min-w-0 flex-1">
        @if ($label !== '')
            <p class="font-bold text-sm text-gray-900">{{ $label }}</p>
        @endif
        @if ($hint)
            <p class="text-xs text-gray-500 mt-0.5">{{ $hint }}</p>
        @endif
    </div>
    <label class="relative inline-flex shrink-0 cursor-pointer items-center">
        <input type="checkbox" {{ $attributes->except('class') }} class="sr-only peer" />
        <span
            class="relative h-7 w-12 rounded-full bg-gray-300 transition-colors duration-200 ease-in-out peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-indigo-500 {{ $trackOn }} after:absolute after:start-[3px] after:top-[3px] after:h-[22px] after:w-[22px] after:rounded-full after:bg-white after:shadow-sm after:transition-transform after:duration-200 after:ease-in-out peer-checked:after:translate-x-5"
            aria-hidden="true"
        ></span>
    </label>
</div>
