<x-layout.app title="Realisasi Fisik">
    <x-card title="Realisasi Fisik {{ $tahun?->tahun }}" icon="fa-percent">
        @include('realisasi._pilih')
        @if (! $tahun || $pilihan->isEmpty())
            <x-empty-state icon="fa-percent" text="Belum ada sub kegiatan{{ auth()->user()->isOperator() ? ' pada bidang Anda' : '' }} untuk tahun ini." />
        @else
            <x-empty-state icon="fa-hand-pointer" text="Pilih sub kegiatan untuk mengisi realisasi fisik." />
        @endif
    </x-card>
</x-layout.app>
