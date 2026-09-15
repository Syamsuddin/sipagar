<x-layout.app title="Target Triwulan">
    <x-card title="Target Triwulan {{ $tahun?->tahun }}" icon="fa-bullseye">
        @include('target._pilih')
        @if (! $tahun || $pilihan->isEmpty())
            <x-empty-state icon="fa-bullseye" text="Belum ada sub kegiatan{{ auth()->user()->isOperator() ? ' pada bidang Anda' : '' }} untuk tahun ini." />
        @else
            <x-empty-state icon="fa-hand-pointer" text="Pilih sub kegiatan untuk mengisi target." />
        @endif
    </x-card>
</x-layout.app>
