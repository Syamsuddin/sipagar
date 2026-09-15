{{-- <x-data-table> — slot `head` (isi <tr> thead), default (isi tbody), `foot` (isi tfoot) --}}
@props(['head', 'foot' => null])
<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'data-table']) }}>
        <thead><tr>{{ $head }}</tr></thead>
        <tbody>{{ $slot }}</tbody>
        @if ($foot)<tfoot><tr>{{ $foot }}</tr></tfoot>@endif
    </table>
</div>
