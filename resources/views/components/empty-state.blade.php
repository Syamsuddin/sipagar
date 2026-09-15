{{-- <x-empty-state icon text> --}}
@props(['icon' => 'fa-inbox', 'text' => 'Belum ada data.'])
<div {{ $attributes->merge(['class' => 'empty-state']) }}><i class="fa-solid {{ $icon }}"></i><p>{{ $text }}</p></div>
