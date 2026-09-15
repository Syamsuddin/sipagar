{{-- <x-progress :value="72" color="linear-gradient(90deg,#00b4d8,#00e68a)|var(--accent)|…"> — lebar inline = satu-satunya inline style yang diizinkan (docs/26) --}}
@props(['value' => 0, 'color' => 'linear-gradient(90deg,#00b4d8,#00e68a)'])
<div class="progress-track"><div class="progress-fill" style="width:{{ min(max((float) $value, 0), 100) }}%;background:{{ $color }};"></div></div>
