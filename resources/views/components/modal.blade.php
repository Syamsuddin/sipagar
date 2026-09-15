{{-- <x-modal id title icon variant="danger|warning|accent" pesan> — dibuka lewat $dispatch('buka-modal', id) --}}
@props(['id', 'title', 'icon' => 'fa-lock', 'variant' => 'danger', 'pesan' => null, 'terbuka' => false])
<div class="modal-overlay" :class="{ show: terbuka }" x-data="modal({{ $terbuka ? 'true' : 'false' }})" x-on:buka-modal.window="if ($event.detail === '{{ $id }}') buka()" x-on:keydown.escape.window="tutup()" @click.self="tutup()" id="modal-{{ $id }}">
    <div class="modal-box">
        <div class="text-center mb-5">
            @if ($variant === 'accent')
                <div class="modal-icon-lock bg-[var(--accent-dim)]"><i class="fa-solid {{ $icon }} text-[1.4rem] text-[var(--accent)]"></i></div>
            @else
                <div class="modal-icon-lock {{ $variant }}"><i class="fa-solid {{ $icon }} text-[1.4rem]"></i></div>
            @endif
            <h3 class="font-bold text-[1.1rem] mb-2">{{ $title }}</h3>
            @if ($pesan)<p class="text-[0.85rem] text-[var(--fg-muted)]">{{ $pesan }}</p>@endif
        </div>
        {{ $slot }}
    </div>
</div>
