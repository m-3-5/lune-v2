@props(['video'])

@if ($video->videoUrl())
    <video src="{{ $video->videoUrl() }}" controls playsinline class="w-full"></video>
@elseif ($video->embedUrl())
    <div class="aspect-video">
        <iframe src="{{ $video->embedUrl() }}" class="w-full h-full" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
@elseif ($video->video_url)
    <a href="{{ $video->video_url }}" target="_blank" rel="noopener"
        class="flex items-center justify-center gap-2 bg-gray-900 text-white py-6 font-black text-sm uppercase">
        ▶️ Guarda il video
    </a>
@else
    <div class="bg-gray-100 text-gray-400 text-center py-8 text-sm">Video non disponibile</div>
@endif
