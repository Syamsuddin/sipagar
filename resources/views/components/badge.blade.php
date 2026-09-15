{{-- <x-badge color="green|yellow|red"> — status docs/04 --}}
@props(['color' => 'green'])
<span {{ $attributes->merge(['class' => "badge badge-{$color}"]) }}>{{ $slot }}</span>
