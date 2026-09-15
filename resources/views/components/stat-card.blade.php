{{-- <x-stat-card color="green|red|yellow|teal" icon label value sub> — slot opsional (mis. <x-progress>) --}}
@props(['color' => 'green', 'icon', 'label', 'value', 'sub' => null])
@php
    // Kelas ditulis literal agar Tailwind JIT menghasilkannya; nilai = token docs/26.
    $kotak = ['green' => 'bg-[var(--accent-dim)]', 'red' => 'bg-[var(--danger-dim)]', 'yellow' => 'bg-[var(--warning-dim)]', 'teal' => 'bg-[rgba(0,180,216,0.12)]'][$color];
    $teks = ['green' => 'text-[var(--accent)]', 'red' => 'text-[var(--danger)]', 'yellow' => 'text-[var(--warning)]', 'teal' => 'text-[#00b4d8]'][$color];
@endphp
<div {{ $attributes->merge(['class' => "card stat-card {$color}"]) }}>
    <div class="flex items-center justify-between mb-[14px]">
        <span class="text-[0.75rem] font-semibold text-[var(--fg-muted)] uppercase tracking-[0.05em]">{{ $label }}</span>
        <div class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center {{ $kotak }}"><i class="fa-solid {{ $icon }} text-[0.85rem] {{ $teks }}"></i></div>
    </div>
    <div class="big-number text-[1.5rem] {{ $teks }}">{{ $value }}</div>
    @if ($sub)<div class="text-[0.75rem] text-[var(--fg-muted)] mt-[6px]">{{ $sub }}</div>@endif
    @if ($slot->isNotEmpty())<div class="mt-2">{{ $slot }}</div>@endif
</div>
