<x-layout.app title="Realisasi Keuangan">
@php $formEdit = $edit && ($bolehTulis) && ! $terkunci; @endphp
<div class="form-grid grid grid-cols-[400px_1fr] gap-6 items-start"
     x-data="{ pilihan: @js($pilihan->keyBy('id')->all()), sk: '{{ old('sub_kegiatan_id', $terpilih?->id ?? '') }}', get sisa() { return this.pilihan[this.sk]?.sisa ?? null; } }">
    @if ($bolehTulis && ! $terkunci)
        <x-card :title="$formEdit ? 'Ubah Realisasi' : 'Tambah Realisasi'" :icon="$formEdit ? 'fa-pen-to-square' : 'fa-file-invoice-dollar'" class="sticky top-[90px]">
            <form method="POST" action="{{ $formEdit ? route('realisasi.keuangan.update', $edit) : route('realisasi.keuangan.store') }}" enctype="multipart/form-data" autocomplete="off" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf
                @if ($formEdit) @method('PUT') <input type="hidden" name="sub_kegiatan_id" value="{{ $edit->sub_kegiatan_id }}"> @endif
                <div class="mb-4">
                    @if ($formEdit)
                        <label class="form-label">Sub Kegiatan</label>
                        <div class="form-input !bg-transparent text-[var(--fg-muted)] text-[0.85rem]">{{ $edit->subKegiatan->kode }} — {{ $edit->subKegiatan->nama }}</div>
                    @else
                        <x-form-select name="sub_kegiatan_id" label="Pilih Sub Kegiatan" x-model="sk" required>
                            <option value="">Pilih sub kegiatan</option>
                            @foreach ($pilihan as $p)<option value="{{ $p['id'] }}">{{ $p['label'] }}</option>@endforeach
                        </x-form-select>
                    @endif
                </div>
                @unless ($formEdit)
                    <div class="mb-4 p-3 bg-[rgba(0,230,138,0.04)] border border-[var(--border)] rounded-[10px]">
                        <div class="text-[0.72rem] text-[var(--fg-muted)] mb-1">Sisa anggaran:</div>
                        <div class="big-number text-[1.1rem]" :class="sisa !== null && sisa <= 0 ? 'text-[var(--danger)]' : 'text-[var(--accent)]'" x-text="sisa === null ? 'Rp 0' : 'Rp ' + Math.round(sisa).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')">Rp 0</div>
                    </div>
                @endunless
                <div class="mb-4"><x-form-input name="tanggal" label="Tanggal" type="date" :value="$edit?->tanggal?->format('Y-m-d')" required /></div>
                <div class="mb-4"><x-form-input name="jumlah" label="Jumlah (Rp)" inputmode="numeric" placeholder="25000000" :value="$edit?->jumlah" required /></div>
                <div class="mb-4"><x-form-textarea name="uraian" label="Uraian" rows="3" placeholder="Pembayaran narasumber" :value="$edit?->uraian" required /></div>
                <div class="mb-4"><x-form-input name="no_sp2d" label="No. SP2D" placeholder="Opsional" :value="$edit?->no_sp2d" /></div>
                <div class="mb-5">
                    <label class="form-label" for="lampiran">Lampiran (pdf/jpg/png ≤ 2 MB)</label>
                    <input class="form-input !py-2 {{ $errors->has('lampiran') ? 'input-error' : '' }}" type="file" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png">
                    @error('lampiran')<div class="pw-error show"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
                    @if ($formEdit && $edit->lampiran_path)<div class="text-[0.75rem] text-[var(--fg-muted)] mt-1">Lampiran saat ini: <a class="text-[var(--accent)]" href="{{ route('realisasi.lampiran.show', $edit) }}" target="_blank">lihat</a> (unggah untuk mengganti)</div>@endif
                </div>
                <div class="flex gap-[10px]">
                    @if ($formEdit)<x-btn variant="secondary" :href="route('realisasi.keuangan.index', ['sub_kegiatan' => $edit->sub_kegiatan_id])" class="flex-1 justify-center">Batal</x-btn>@endif
                    <x-btn type="submit" variant="primary" class="flex-1 justify-center" icon="fa-check-circle" x-bind:disabled="memproses"><span x-show="!memproses">{{ $formEdit ? 'Perbarui' : 'Simpan Realisasi' }}</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn>
                </div>
            </form>
        </x-card>
    @else
        <x-card title="Realisasi Keuangan" icon="fa-file-invoice-dollar" class="sticky top-[90px]">
            @if ($terkunci)<x-badge color="red">Terkunci</x-badge><p class="text-[0.85rem] text-[var(--fg-muted)] mt-3">Tahun {{ $tahun?->tahun }} terkunci — data hanya dapat dibaca.</p>@else<p class="text-[0.85rem] text-[var(--fg-muted)]">Baca saja untuk peran Anda.</p>@endif
        </x-card>
    @endif

    <x-card title="Riwayat Realisasi {{ $tahun?->tahun }}" icon="fa-receipt">
        <x-slot:aksi>
            <div class="flex items-center gap-2 flex-wrap">
                @if ($terpilih)<a href="{{ route('realisasi.keuangan.index') }}" class="btn-secondary no-underline !py-1 !px-3 !text-[0.72rem]"><i class="fa-solid fa-xmark"></i> {{ $terpilih->kode }}</a>@endif
                <x-badge color="red">{{ $daftar->count() }} transaksi</x-badge>
            </div>
        </x-slot:aksi>
        @if ($daftar->isEmpty())
            <x-empty-state icon="fa-file-circle-plus" text="Belum ada realisasi." />
        @else
            <x-data-table>
                <x-slot:head><th>No</th><th>Tanggal</th><th>Sub Kegiatan</th><th>Jumlah</th><th class="hide-mobile">Uraian</th><th class="hide-mobile">SP2D</th><th class="hide-mobile">Lampiran</th>@if ($bolehTulis && ! $terkunci)<th class="text-center hide-mobile">Aksi</th>@endif</x-slot:head>
                @foreach ($daftar as $i => $r)
                    <tr>
                        <td class="text-[var(--fg-muted)] font-semibold">{{ $i + 1 }}</td>
                        <td class="whitespace-nowrap">{{ $r->tanggal->format('d/m/Y') }}</td>
                        <td class="font-semibold"><span class="text-[var(--fg-muted)] text-[0.75rem] block">{{ $r->subKegiatan->kode }}</span>{{ $r->subKegiatan->nama }}</td>
                        <td class="big-number !text-[0.85rem] text-[var(--danger)] whitespace-nowrap">{{ rupiah($r->jumlah) }}</td>
                        <td class="hide-mobile text-[var(--fg-muted)]">{{ $r->uraian }}</td>
                        <td class="hide-mobile text-[var(--fg-muted)]">{{ $r->no_sp2d ?? '—' }}</td>
                        <td class="hide-mobile">@if ($r->lampiran_path)<a class="btn-secondary no-underline !py-1 !px-2 !text-[0.7rem]" href="{{ route('realisasi.lampiran.show', $r) }}" target="_blank"><i class="fa-solid fa-paperclip"></i></a>@else —@endif</td>
                        @if ($bolehTulis && ! $terkunci)
                            <td class="text-center hide-mobile">
                                @can('update', $r)
                                    <div class="flex gap-[6px] justify-center">
                                        <button type="button" class="btn-edit" title="Ubah" @click="$dispatch('buka-konfirmasi', { id: 'realisasi', aksi: () => window.location = '{{ route('realisasi.keuangan.index', ['edit' => $r->id, 'sub_kegiatan' => $r->sub_kegiatan_id]) }}' })"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button type="button" class="btn-danger" title="Hapus" @click="$dispatch('buka-konfirmasi', { id: 'realisasi', aksi: document.getElementById('hapus-r-{{ $r->id }}') })"><i class="fa-solid fa-trash"></i></button>
                                        <form id="hapus-r-{{ $r->id }}" method="POST" action="{{ route('realisasi.keuangan.destroy', $r) }}" hidden>@csrf @method('DELETE')</form>
                                    </div>
                                @endcan
                            </td>
                        @endif
                    </tr>
                @endforeach
                <x-slot:foot><td colspan="3">Total</td><td class="big-number !text-[0.9rem] text-[var(--danger)]">{{ rupiah($daftar->sum('jumlah')) }}</td><td colspan="{{ $bolehTulis && ! $terkunci ? 4 : 3 }}" class="hide-mobile"></td></x-slot:foot>
            </x-data-table>
        @endif
    </x-card>
</div>
@if ($bolehTulis && ! $terkunci)
    <x-modal-konfirmasi-sandi id="realisasi" title="Konfirmasi Hapus/Ubah" pesan="Masukkan kata sandi untuk mengubah atau menghapus realisasi." variant="danger" icon="fa-lock" tombol="Lanjutkan" />
@endif
</x-layout.app>
