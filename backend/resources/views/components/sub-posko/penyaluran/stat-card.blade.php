@props([
    'title',
    'value',
    'bgIcon' => 'bg-amber-50',
    'textIcon' => 'text-amber-600',
    'textValue' => 'text-gray-800'
])

<div class="bg-white p-3 sm:p-5 rounded-2xl border border-gray-200 shadow-2xs flex items-center space-x-3 sm:space-x-4 font-sans">
    <div class="p-2 sm:p-3 rounded-xl shrink-0 {{ $bgIcon }} {{ $textIcon }}">
        {{ $slot }}
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider truncate">{{ $title }}</p>
        <h4 class="text-xs sm:text-lg font-extrabold {{ $textValue }} leading-tight mt-0.5 truncate">{{ $value }}</h4>
    </div>
</div>