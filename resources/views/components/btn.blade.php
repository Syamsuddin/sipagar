{{-- <x-btn variant="primary|secondary|danger|edit|pw-settings|logout" type href icon> --}}
@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'icon' => null])
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "btn-{$variant} no-underline"]) }}>@if ($icon)<i class="fa-solid {{ $icon }}"></i>@endif{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "btn-{$variant}"]) }}>@if ($icon)<i class="fa-solid {{ $icon }}"></i>@endif{{ $slot }}</button>
@endif
