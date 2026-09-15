{{-- <x-badge-sumber :sumber="$sumberDana"> — $sumber punya `css_class` & `kode` (tabel sumber_dana, docs/07) --}}
@props(['sumber'])
<span class="badge {{ data_get($sumber, 'css_class', 'sd-lainnya') }} !text-[0.65rem]">{{ data_get($sumber, 'kode') }}</span>
