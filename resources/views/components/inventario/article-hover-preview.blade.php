@props([
    'name',
    'photoUrl' => null,
])

<div
    class="relative inline-flex max-w-full items-center"
    @if($photoUrl)
        x-data="{ open: false }"
    @endif
>
    <span
        class="inline-flex items-center gap-1 font-medium text-gray-900 dark:text-gray-100"
        @if($photoUrl)
            tabindex="0"
            @mouseenter="open = true"
            @mouseleave="open = false"
            @focus="open = true"
            @blur="open = false"
            @touchstart="open = !open"
        @endif
    >
        <span class="truncate">{{ $name }}</span>
        @if($photoUrl)
            <x-heroicon-o-photo class="h-4 w-4 text-gray-400" />
        @endif
    </span>

    @if($photoUrl)
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.120ms
            class="pointer-events-none absolute left-0 top-full z-30 mt-2 w-44"
        >
            <div class="rounded-lg border border-slate-200 bg-white/95 p-2 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
                <img
                    src="{{ $photoUrl }}"
                    alt="Foto de {{ $name }}"
                    loading="lazy"
                    class="h-28 w-full rounded-md object-cover"
                />
                <p class="mt-1 text-[11px] text-slate-600 dark:text-slate-300">{{ $name }}</p>
            </div>
        </div>
    @endif
</div>
