{{-- <x-card title icon> — .card prototipe; slot `aksi` = elemen kanan judul (badge hitung, filter) --}}
@props(['title' => null, 'icon' => null, 'aksi' => null])
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title)
        <div class="font-bold text-[0.95rem] mb-5 flex items-center {{ $aksi ? 'justify-between flex-wrap gap-3' : 'gap-2' }}">
            <span class="flex items-center gap-2">@if ($icon)<i class="fa-solid {{ $icon }} text-[var(--accent)] text-[0.85rem]"></i>@endif{{ $title }}</span>
            @if ($aksi){{ $aksi }}@endif
        </div>
    @endif
    {{ $slot }}
</div>
