<x-layout.app title="Pengaturan Laporan">
    <div class="form-grid grid grid-cols-[400px_1fr] gap-6 items-start">
        <x-card title="Pengaturan Laporan" icon="fa-gear" class="sticky top-[90px]">
            <p class="text-[0.85rem] text-[var(--fg-muted)]">Kop instansi dan blok tanda tangan yang dicetak pada PDF laporan (docs/26 §PDF).</p>
        </x-card>

        <x-card title="Kop & Penandatangan" icon="fa-file-signature">
            <form method="POST" action="{{ route('master.pengaturan.update') }}" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4 mb-4 form-grid">
                    <div class="col-span-2"><x-form-input name="kop_nama_instansi" label="Nama Instansi (kop)" :value="$pengaturan['kop_nama_instansi']" required /></div>
                    <div class="col-span-2"><x-form-textarea name="kop_alamat" label="Alamat Instansi" :value="$pengaturan['kop_alamat']" rows="2" /></div>
                    <div><x-form-input name="ttd_nama" label="Nama Penandatangan" :value="$pengaturan['ttd_nama']" /></div>
                    <div><x-form-input name="ttd_nip" label="NIP" :value="$pengaturan['ttd_nip']" /></div>
                    <div><x-form-input name="ttd_jabatan" label="Jabatan" :value="$pengaturan['ttd_jabatan']" /></div>
                    <div><x-form-input name="ttd_kota" label="Kota (tanggal surat)" :value="$pengaturan['ttd_kota']" /></div>
                </div>
                <x-btn type="submit" variant="primary" icon="fa-check" x-bind:disabled="memproses"><span x-show="!memproses">Simpan Pengaturan</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn>
            </form>
        </x-card>
    </div>
</x-layout.app>
