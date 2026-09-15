{{-- <x-tab-nav :items="[['label','icon','href','aktif'], ...]"> — docs/26: link route, bukan panel JS --}}
@props(['items' => []])
<nav class="flex gap-2 mb-7 overflow-x-auto pb-1" role="tablist">
    @foreach ($items as $item)
        <a href="{{ $item['href'] }}" class="tab-btn no-underline inline-flex items-center {{ $item['aktif'] ? 'active' : '' }}" role="tab" aria-selected="{{ $item['aktif'] ? 'true' : 'false' }}"><i class="fa-solid {{ $item['icon'] }} mr-[6px]"></i>{{ $item['label'] }}</a>
    @endforeach
</nav>
