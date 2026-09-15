<x-layout.app title="Anggaran">
@php
    $bolehTulis = auth()->user()->isAdmin() && $tahun && ! $terkunci;
    $formAwal = $errors->any() && old('_form') ? json_decode(old('_form'), true) : null;
    if ($formAwal) { $formAwal['nilai'] = old(); }
@endphp
<div x-data="anggaranForm(@js($formAwal))">
    <x-card>
        <x-slot:title>Struktur Anggaran {{ $tahun?->tahun }}</x-slot:title>
        <x-slot:icon>fa-folder-tree</x-slot:icon>
        <x-slot:aksi>
            <div class="flex items-center gap-3 flex-wrap">
                @if ($terkunci)<x-badge color="red">Terkunci</x-badge>@elseif ($tahun)<x-badge :color="$tahun->status->warnaBadge()">{{ $tahun->status->label() }}</x-badge>@endif
                <form x-data method="GET" action="{{ route('anggaran.index') }}" class="flex items-center gap-2">
                    <label class="form-label !mb-0" for="tahun">Tahun:</label>
                    <select class="form-input !w-[130px] !py-2 !px-3" id="tahun" name="tahun" @change="$el.form.submit()">
                        @forelse ($daftarTahun as $t)
                            <option value="{{ $t->tahun }}" @selected($tahun && $t->id === $tahun->id)>{{ $t->tahun }}</option>
                        @empty
                            <option value="">—</option>
                        @endforelse
                    </select>
                </form>
                @if ($bolehTulis)
                    <x-btn variant="primary" icon="fa-plus" class="!py-2 !px-4" @click="bukaModal({ jenis: 'program', mode: 'tambah', induk: {{ $tahun->id }} })">Program</x-btn>
                @endif
            </div>
        </x-slot:aksi>

        @if (! $tahun)
            <x-empty-state icon="fa-calendar" text="Belum ada tahun anggaran. Tambahkan di Master › Tahun Anggaran." />
        @elseif ($program->isEmpty())
            <x-empty-state icon="fa-folder-open" text="Belum ada program pada tahun {{ $tahun->tahun }}." />
        @else
            <x-data-table>
                <x-slot:head><th>Kode</th><th>Nama</th><th class="hide-mobile">Bidang</th><th class="hide-mobile">Sumber</th><th>Pagu</th><th class="hide-mobile">PPTK</th>@if ($bolehTulis)<th class="text-center">Aksi</th>@endif</x-slot:head>
                @foreach ($program as $p)
                    <tr class="bg-[rgba(0,230,138,0.03)]">
                        <td class="font-bold text-[var(--accent)] whitespace-nowrap"><i class="fa-solid fa-folder mr-2"></i>{{ $p->kode }}</td>
                        <td class="font-bold" colspan="3">{{ $p->nama }}</td>
                        <td class="big-number !text-[0.85rem]">{{ rupiah($p->kegiatan->flatMap->subKegiatan->sum('pagu')) }}</td>
                        <td class="hide-mobile"></td>
                        @if ($bolehTulis)
                            <td class="text-center"><div class="flex gap-[6px] justify-center">
                                <button type="button" class="btn-pw-settings" title="Tambah kegiatan" @click="bukaModal({ jenis: 'kegiatan', mode: 'tambah', induk: {{ $p->id }} })"><i class="fa-solid fa-plus"></i></button>
                                <button type="button" class="btn-edit" title="Ubah" @click="ubah({ jenis: 'program', id: {{ $p->id }}, nilai: @js($p->only(['kode', 'nama', 'urutan'])) })"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button type="button" class="btn-danger" title="Hapus" @click="hapus('hapus-program-{{ $p->id }}')"><i class="fa-solid fa-trash"></i></button>
                                <form id="hapus-program-{{ $p->id }}" method="POST" action="{{ route('anggaran.program.destroy', $p) }}" hidden>@csrf @method('DELETE')</form>
                            </div></td>
                        @endif
                    </tr>
                    @foreach ($p->kegiatan as $k)
                        <tr>
                            <td class="font-semibold whitespace-nowrap !pl-8"><i class="fa-solid fa-folder-open text-[var(--fg-muted)] mr-2"></i>{{ $k->kode }}</td>
                            <td class="font-semibold" colspan="3">{{ $k->nama }}</td>
                            <td class="big-number !text-[0.85rem] text-[var(--fg-muted)]">{{ rupiah($k->subKegiatan->sum('pagu')) }}</td>
                            <td class="hide-mobile"></td>
                            @if ($bolehTulis)
                                <td class="text-center"><div class="flex gap-[6px] justify-center">
                                    <button type="button" class="btn-pw-settings" title="Tambah sub kegiatan" @click="bukaModal({ jenis: 'sub', mode: 'tambah', induk: {{ $k->id }} })"><i class="fa-solid fa-plus"></i></button>
                                    <button type="button" class="btn-edit" title="Ubah" @click="ubah({ jenis: 'kegiatan', id: {{ $k->id }}, nilai: @js($k->only(['kode', 'nama', 'urutan'])) })"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button type="button" class="btn-danger" title="Hapus" @click="hapus('hapus-kegiatan-{{ $k->id }}')"><i class="fa-solid fa-trash"></i></button>
                                    <form id="hapus-kegiatan-{{ $k->id }}" method="POST" action="{{ route('anggaran.kegiatan.destroy', $k) }}" hidden>@csrf @method('DELETE')</form>
                                </div></td>
                            @endif
                        </tr>
                        @foreach ($k->subKegiatan as $s)
                            <tr>
                                <td class="whitespace-nowrap !pl-14 text-[var(--fg-muted)]"><i class="fa-solid fa-file-lines mr-2"></i>{{ $s->kode }}</td>
                                <td>{{ $s->nama }}</td>
                                <td class="hide-mobile"><x-badge color="green">{{ $s->bidang->kode }}</x-badge></td>
                                <td class="hide-mobile"><x-badge-sumber :sumber="$s->sumberDana" /></td>
                                <td class="big-number !text-[0.85rem]">{{ rupiah($s->pagu) }}</td>
                                <td class="hide-mobile text-[var(--fg-muted)]">{{ $s->pptk ?? '—' }}</td>
                                @if ($bolehTulis)
                                    <td class="text-center"><div class="flex gap-[6px] justify-center">
                                        <button type="button" class="btn-edit" title="Ubah" @click="ubah({ jenis: 'sub', id: {{ $s->id }}, nilai: @js($s->only(['kode', 'nama', 'pagu', 'bidang_id', 'sumber_dana_id', 'pptk', 'urutan'])) })"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button type="button" class="btn-danger" title="Hapus" @click="hapus('hapus-sub-{{ $s->id }}')"><i class="fa-solid fa-trash"></i></button>
                                        <form id="hapus-sub-{{ $s->id }}" method="POST" action="{{ route('anggaran.sub-kegiatan.destroy', $s) }}" hidden>@csrf @method('DELETE')</form>
                                    </div></td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
                <x-slot:foot><td colspan="4">Total pagu {{ $tahun->tahun }}</td><td class="big-number !text-[0.9rem] text-[var(--accent)]">{{ rupiah($program->flatMap->kegiatan->flatMap->subKegiatan->sum('pagu')) }}</td><td colspan="{{ $bolehTulis ? 2 : 1 }}"></td></x-slot:foot>
            </x-data-table>
        @endif
        @foreach (['program', 'kegiatan', 'sub_kegiatan'] as $k)
            @error($k)<div class="pw-error show mt-4"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
        @endforeach
    </x-card>

    @if ($bolehTulis)
        <x-modal-konfirmasi-sandi id="anggaran" title="Konfirmasi Admin" pesan="Masukkan kata sandi untuk mengubah atau menghapus data anggaran." variant="warning" icon="fa-pen-to-square" tombol="Lanjutkan" />

        {{-- Modal Program --}}
        <x-modal id="form-program" icon="fa-folder" variant="accent" title="Program">
            <x-slot:title><span x-text="judul">Program</span></x-slot:title>
            <form method="POST" :action="action('{{ url('/anggaran/program') }}')" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf <template x-if="mode === 'ubah'"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="_form" :value="JSON.stringify({ jenis, mode, id, induk })">
                <input type="hidden" name="tahun_anggaran_id" :value="induk">
                <div class="mb-4"><x-form-input name="kode" label="Kode Program" placeholder="X.XX.XX" x-model="nilai.kode" required /></div>
                <div class="mb-4"><x-form-input name="nama" label="Nama Program" x-model="nilai.nama" required /></div>
                <div class="mb-5"><x-form-input name="urutan" label="Urutan" type="number" min="0" x-model="nilai.urutan" /></div>
                <div class="flex gap-[10px]"><button type="button" class="btn-secondary flex-1 justify-center" @click="tutup()">Batal</button><x-btn type="submit" variant="primary" class="flex-1 justify-center" icon="fa-check" x-bind:disabled="memproses"><span x-show="!memproses">Simpan</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn></div>
            </form>
        </x-modal>

        {{-- Modal Kegiatan --}}
        <x-modal id="form-kegiatan" icon="fa-folder-open" variant="accent" title="Kegiatan">
            <x-slot:title><span x-text="judul">Kegiatan</span></x-slot:title>
            <form method="POST" :action="action('{{ url('/anggaran/kegiatan') }}')" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf <template x-if="mode === 'ubah'"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="_form" :value="JSON.stringify({ jenis, mode, id, induk })">
                <input type="hidden" name="program_id" :value="induk">
                <div class="mb-4"><x-form-input name="kode" label="Kode Kegiatan" placeholder="X.XX.XX.X.XX" x-model="nilai.kode" required /></div>
                <div class="mb-4"><x-form-input name="nama" label="Nama Kegiatan" x-model="nilai.nama" required /></div>
                <div class="mb-5"><x-form-input name="urutan" label="Urutan" type="number" min="0" x-model="nilai.urutan" /></div>
                <div class="flex gap-[10px]"><button type="button" class="btn-secondary flex-1 justify-center" @click="tutup()">Batal</button><x-btn type="submit" variant="primary" class="flex-1 justify-center" icon="fa-check" x-bind:disabled="memproses"><span x-show="!memproses">Simpan</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn></div>
            </form>
        </x-modal>

        {{-- Modal Sub Kegiatan --}}
        <x-modal id="form-sub" icon="fa-file-lines" variant="accent" title="Sub Kegiatan">
            <x-slot:title><span x-text="judul">Sub Kegiatan</span></x-slot:title>
            <form method="POST" :action="action('{{ url('/anggaran/sub-kegiatan') }}')" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf <template x-if="mode === 'ubah'"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="_form" :value="JSON.stringify({ jenis, mode, id, induk })">
                <input type="hidden" name="kegiatan_id" :value="induk">
                <div class="mb-4"><x-form-input name="kode" label="Kode Sub Kegiatan" placeholder="X.XX.XX.X.XX.XXXX" x-model="nilai.kode" required /></div>
                <div class="mb-4"><x-form-input name="nama" label="Nama Sub Kegiatan" x-model="nilai.nama" required /></div>
                <div class="mb-4"><x-form-input name="pagu" label="Pagu (Rp)" inputmode="numeric" placeholder="Contoh: 250000000" x-model="nilai.pagu" required /></div>
                <div class="grid grid-cols-2 gap-4 mb-4 form-grid">
                    <x-form-select name="bidang_id" label="Bidang PJ" x-model="nilai.bidang_id" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($daftarBidang as $b)<option value="{{ $b->id }}">{{ $b->kode }} — {{ $b->nama }}</option>@endforeach
                    </x-form-select>
                    <x-form-select name="sumber_dana_id" label="Sumber Dana" x-model="nilai.sumber_dana_id" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($daftarSumber as $sd)<option value="{{ $sd->id }}">{{ $sd->kode }}</option>@endforeach
                    </x-form-select>
                </div>
                <div class="mb-4"><x-form-input name="pptk" label="PPTK" placeholder="Nama PPTK" x-model="nilai.pptk" /></div>
                <div class="mb-5"><x-form-input name="urutan" label="Urutan" type="number" min="0" x-model="nilai.urutan" /></div>
                <div class="flex gap-[10px]"><button type="button" class="btn-secondary flex-1 justify-center" @click="tutup()">Batal</button><x-btn type="submit" variant="primary" class="flex-1 justify-center" icon="fa-check" x-bind:disabled="memproses"><span x-show="!memproses">Simpan</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn></div>
            </form>
        </x-modal>
    @endif
</div>
</x-layout.app>
