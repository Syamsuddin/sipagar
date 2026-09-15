{{-- Card filter laporan (pola filter "Sisa Anggaran" prototipe) + tombol Unduh Excel/PDF (docs/26).
     $kolom: daftar filter yang dipakai: tahun, triwulan, bidang, sumber_dana, sub_kegiatan, tanggal --}}
@props(['kolom', 'ikon', 'judul', 'filter', 'daftarTahun', 'daftarBidang', 'daftarSumber', 'pilihanSub' => collect()])
<x-card class="mb-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="font-bold text-[0.95rem] flex items-center gap-2"><i class="fa-solid {{ $ikon }} text-[var(--accent)] text-[0.85rem]"></i>{{ $judul }}</div>
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-3 flex-wrap" id="form-filter">
            @if (in_array('tahun', $kolom))
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="tahun">Tahun:</label>
                    <select class="form-input !w-[110px] !py-2 !px-3" id="tahun" name="tahun" onchange="this.form.submit()">
                        @foreach ($daftarTahun as $ta)<option value="{{ $ta->tahun }}" @selected($filter && $filter->tahun->id === $ta->id)>{{ $ta->tahun }}</option>@endforeach
                    </select></div>
            @endif
            @if (in_array('triwulan', $kolom))
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="triwulan">s.d. TW:</label>
                    <select class="form-input !w-[90px] !py-2 !px-3" id="triwulan" name="triwulan" onchange="const d = this.form.dari, s = this.form.sampai; if (d) d.value = ''; if (s) s.value = ''; this.form.submit()">
                        @foreach ([1, 2, 3, 4] as $tw)<option value="{{ $tw }}" @selected($filter && $filter->triwulan === $tw)>{{ $tw }}</option>@endforeach
                    </select></div>
            @endif
            @if (in_array('tanggal', $kolom))
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="dari">Dari:</label><input class="form-input !w-[150px] !py-2 !px-3" type="date" id="dari" name="dari" value="{{ $filter?->dari?->toDateString() }}" onchange="this.form.submit()"></div>
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="sampai">Sampai:</label><input class="form-input !w-[150px] !py-2 !px-3" type="date" id="sampai" name="sampai" value="{{ $filter?->sampai?->toDateString() }}" onchange="this.form.submit()"></div>
            @endif
            @if (in_array('bidang', $kolom))
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="bidang">Bidang:</label>
                    <select class="form-input !w-[170px] !py-2 !px-3" id="bidang" name="bidang" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach ($daftarBidang as $b)<option value="{{ $b->id }}" @selected($filter?->bidangId === $b->id)>{{ $b->kode }}</option>@endforeach
                    </select></div>
            @endif
            @if (in_array('sumber_dana', $kolom))
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="sumber_dana">Sumber:</label>
                    <select class="form-input !w-[160px] !py-2 !px-3" id="sumber_dana" name="sumber_dana" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach ($daftarSumber as $s)<option value="{{ $s->id }}" @selected($filter?->sumberDanaId === $s->id)>{{ $s->kode }}</option>@endforeach
                    </select></div>
            @endif
            @if (in_array('sub_kegiatan', $kolom))
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="sub_kegiatan">Sub Keg.:</label>
                    <select class="form-input !w-[220px] !py-2 !px-3" id="sub_kegiatan" name="sub_kegiatan" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach ($pilihanSub as $sk)<option value="{{ $sk->id }}" @selected($filter?->subKegiatanId === $sk->id)>{{ $sk->kode }}</option>@endforeach
                    </select></div>
            @endif
            @if ($filter)
                <x-btn variant="secondary" :href="request()->fullUrlWithQuery(['export' => 'xlsx', 'halaman' => null])" icon="fa-file-excel">Unduh Excel</x-btn>
                <x-btn variant="secondary" :href="request()->fullUrlWithQuery(['export' => 'pdf', 'halaman' => null])" icon="fa-file-pdf">Unduh PDF</x-btn>
            @endif
        </form>
    </div>
</x-card>
