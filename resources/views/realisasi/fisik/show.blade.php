<x-layout.app title="Realisasi Fisik">
    @php $bulanNama = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']; $tulis = $bolehTulis && ! $terkunci; @endphp
    <x-card title="Realisasi Fisik {{ $tahun?->tahun }}" icon="fa-percent">
        <x-slot:aksi>
            <div class="flex items-center gap-2 flex-wrap">
                <x-badge color="green">{{ $subKegiatan->bidang->kode }}</x-badge>
                <x-badge-sumber :sumber="$subKegiatan->sumberDana" />
                @if ($terkunci)<x-badge color="red">Terkunci</x-badge>@endif
            </div>
        </x-slot:aksi>
        @include('realisasi._pilih')

        <div class="stat-grid grid grid-cols-4 gap-4 mb-6">
            @foreach ([1, 2, 3, 4] as $tw)
                @php $dev = $deviasi->get($tw); @endphp
                <x-stat-card :color="$dev === null ? 'teal' : ($dev < 0 ? 'red' : 'green')" icon="fa-bullseye" label="TW{{ $tw }} · target {{ $target->get($tw) !== null ? $target->get($tw).'%' : '—' }}" :value="number_format(app(\App\Services\SerapanCalculator::class)->fisikSampaiBulan($fisik, $tw * 3), 2, ',', '.').'%'" :sub="$dev === null ? 'target belum diisi' : 'deviasi '.($dev >= 0 ? '+' : '').number_format($dev, 2, ',', '.').' poin'" />
            @endforeach
        </div>

        @if ($errors->any())
            <div class="pw-error show mb-4"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $errors->first() }}</span></div>
        @endif

        <form method="POST" action="{{ route('realisasi.fisik.update', $subKegiatan) }}" x-data="{ memproses: false }" @submit="memproses = true">
            @csrf @method('PUT')
            <div class="grid grid-cols-4 gap-4 form-grid">
                @foreach (range(1, 12) as $b)
                    <div class="card !p-4">
                        <div class="text-[0.75rem] font-semibold text-[var(--fg-muted)] uppercase tracking-[0.05em] mb-2">{{ $bulanNama[$b] }} <span class="text-[var(--fg)] normal-case font-normal">· bulan {{ $b }}</span></div>
                        @if ($tulis)
                            <input class="form-input !py-2 {{ $errors->has("fisik.$b") ? 'input-error' : '' }}" type="number" step="0.01" min="0" max="100" name="fisik[{{ $b }}]" value="{{ old("fisik.$b", $fisik[$b] ?? '') }}" placeholder="—">
                            @error("fisik.$b")<div class="pw-error show"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
                        @else
                            <div class="big-number text-[1.1rem]">{{ isset($fisik[$b]) ? number_format($fisik[$b], 2, ',', '.').'%' : '—' }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($tulis)
                <div class="mt-5 flex justify-end">
                    <x-btn type="submit" variant="primary" icon="fa-check" x-bind:disabled="memproses"><span x-show="!memproses">Simpan Realisasi Fisik</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn>
                </div>
            @endif
        </form>
    </x-card>
</x-layout.app>
