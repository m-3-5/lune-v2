@props(['content' => ''])

<div {{ $attributes->merge(['class' => 'prose prose-sm max-w-none text-gray-700']) }}>
    {!! \Illuminate\Support\Str::markdown($content) !!}
</div>
