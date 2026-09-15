<x-layout.app title="Target Triwulan">
    <x-card title="Target Triwulan {{ $tahun?->tahun }}" icon="fa-bullseye">
        <x-slot:aksi>
            <div class="flex items-center gap-2 flex-wrap">
                <x-badge color="green">{{ $subKegiatan->bidang->kode }}</x-badge>
                <x-badge-sumber :sumber="$subKegiatan->sumberDana" />
                @if ($terkunci)<x-badge color="red">Terkunci</x-badge>@endif
            </div>
        </x-slot:aksi>
        @include('target._pilih')

        <div class="stat-grid grid grid-cols-4 gap-4 mb-6">
            <x-stat-card color="green" icon="fa-coins" label="Pagu" :value="rupiah_singkat($subKegiatan->pagu)" :sub="$subKegiatan->kode" />
            <x-stat-card color="teal" icon="fa-file-lines" label="Sub Kegiatan" :value="\Illuminate\Support\Str::limit($subKegiatan->nama, 28)" :sub="$subKegiatan->kegiatan->program->nama" />
            <x-stat-card color="yellow" icon="fa-user-tie" label="PPTK" :value="$subKegiatan->pptk ?? '—'" :sub="$subKegiatan->bidang->nama" />
            <x-stat-card color="red" icon="fa-calendar-check" label="TW4 (target akhir)" :value="rupiah_singkat($subKegiatan->pagu)" sub="fisik 100 %" />
        </div>

        @if ($errors->any())
            <div class="pw-error show mb-4"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $errors->first() }}</span></div>
        @endif

        <form method="POST" action="{{ route('target.update', $subKegiatan) }}" x-data="{ memproses: false, pagu: {{ $subKegiatan->pagu }}, keu: @js($baris->pluck('keuangan', 'triwulan')->all()), persen(tw) { const v = parseInt(String(this.keu[tw] ?? 0).replace(/[^0-9]/g, '')) || 0; return this.pagu > 0 ? Math.round(v / this.pagu * 100) : 0; } }" @submit="memproses = true">
            @csrf @method('PUT')
            <x-data-table>
                <x-slot:head><th>Triwulan</th><th class="hide-mobile">s.d.</th><th>Target Keuangan (Rp, kumulatif)</th><th>% Pagu</th><th>Target Fisik (%, kumulatif)</th></x-slot:head>
                @foreach ($baris as $b)
                    @php $tw = $b['triwulan']; @endphp
                    <tr>
                        <td class="font-bold"><x-badge color="green">TW{{ $tw }}</x-badge></td>
                        <td class="hide-mobile text-[var(--fg-muted)]">{{ ['', '31 Mar', '30 Jun', '30 Sep', '31 Des'][$tw] }}</td>
                        <td>
                            @if ($bolehTulis && ! $terkunci)
                                <input class="form-input !py-2 {{ $errors->has("target.$tw.keuangan") ? 'input-error' : '' }}" inputmode="numeric" name="target[{{ $tw }}][keuangan]" x-model="keu[{{ $tw }}]" value="{{ $b['keuangan'] }}" required>
                                @error("target.$tw.keuangan")<div class="pw-error show"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
                            @else
                                <span class="big-number !text-[0.85rem]">{{ rupiah($b['keuangan']) }}</span>
                            @endif
                        </td>
                        <td class="big-number !text-[0.85rem] text-[#00b4d8]"><span x-text="persen({{ $tw }}) + '%'">{{ $b['persen'] }}%</span></td>
                        <td>
                            @if ($bolehTulis && ! $terkunci)
                                <input class="form-input !py-2 !w-[120px] {{ $errors->has("target.$tw.fisik") ? 'input-error' : '' }}" type="number" step="0.01" min="0" max="100" name="target[{{ $tw }}][fisik]" value="{{ $b['fisik'] }}" required>
                                @error("target.$tw.fisik")<div class="pw-error show"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
                            @else
                                <span class="big-number !text-[0.85rem]">{{ $b['fisik'] }}%</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
            @if ($bolehTulis && ! $terkunci)
                <div class="mt-5 flex justify-end">
                    <x-btn type="submit" variant="primary" icon="fa-check" x-bind:disabled="memproses"><span x-show="!memproses">Simpan Target</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn>
                </div>
            @endif
        </form>
    </x-card>
</x-layout.app>
