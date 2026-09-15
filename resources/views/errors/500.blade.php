<x-layout.app title="Terjadi kesalahan">
    <x-card>
        <x-empty-state icon="fa-triangle-exclamation" text="500 · Terjadi kesalahan, hubungi Admin." />
        <p class="text-center text-[0.78rem] text-[var(--fg-muted)]">Kode referensi: <span class="big-number !text-[0.8rem]">{{ request()->attributes->get('request_id', '—') }}</span></p>
    </x-card>
</x-layout.app>
