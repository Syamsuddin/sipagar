<x-layout.app title="Tahun anggaran terkunci">
    <x-card>
        <x-empty-state icon="fa-lock" :text="($pesan ?? 'Tahun anggaran ini terkunci') . '. Data tahun terkunci hanya dapat dibaca.'" />
        <div class="text-center"><x-btn variant="secondary" :href="url()->previous()" icon="fa-arrow-left">Kembali</x-btn></div>
    </x-card>
</x-layout.app>
