<x-layout.app title="Master Tahun Anggaran">
    <div class="form-grid grid grid-cols-[400px_1fr] gap-6 items-start">
        <x-card title="Tambah Tahun Anggaran" icon="fa-calendar-plus" class="sticky top-[90px]">
            <form method="POST" action="{{ route('master.tahun-anggaran.store') }}" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf
                <div class="mb-4">
                    <x-form-select name="tahun" label="Tahun" required>
                        <option value="">-- Pilih tahun --</option>
                        @foreach (range(config('sipagar.tahun_min'), config('sipagar.tahun_max')) as $t)
                            <option value="{{ $t }}" @selected((int) old('tahun') === $t) @disabled($daftar->contains('tahun', $t))>{{ $t }}</option>
                        @endforeach
                    </x-form-select>
                </div>
                <p class="text-[0.78rem] text-[var(--fg-muted)] mb-5">Tahun baru berstatus <strong>draft</strong>. Hanya satu tahun boleh <strong>aktif</strong>; kunci tahun aktif sebelum mengaktifkan tahun lain.</p>
                <x-btn type="submit" variant="primary" class="w-full justify-center" icon="fa-plus" x-bind:disabled="memproses"><span x-show="!memproses">Tambah</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn>
            </form>
        </x-card>

        <x-card title="Daftar Tahun Anggaran" icon="fa-calendar">
            <x-slot:aksi><x-badge color="green">{{ $daftar->count() }} tahun</x-badge></x-slot:aksi>
            @error('status')<div class="pw-error show mb-4"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
            @if ($daftar->isEmpty())
                <x-empty-state icon="fa-calendar" />
            @else
                <x-data-table>
                    <x-slot:head><th>Tahun</th><th>Status</th><th class="hide-mobile">Dikunci</th><th class="text-center">Aksi</th></x-slot:head>
                    @foreach ($daftar as $ta)
                        <tr>
                            <td class="big-number !text-[0.95rem]">{{ $ta->tahun }}</td>
                            <td><x-badge :color="$ta->status->warnaBadge()">{{ $ta->status->label() }}</x-badge></td>
                            <td class="hide-mobile text-[var(--fg-muted)]">{{ $ta->locked_at ? $ta->locked_at->format('d/m/Y H:i').' · '.($ta->lockedBy?->name ?? '') : '—' }}</td>
                            <td class="text-center">
                                @if ($ta->status === \App\Enums\StatusTahun::Draft)
                                    <form method="POST" action="{{ route('master.tahun-anggaran.update', $ta) }}" class="inline-flex">@csrf @method('PUT')<input type="hidden" name="status" value="aktif">
                                        <button type="submit" class="btn-pw-settings" title="Aktifkan"><i class="fa-solid fa-circle-check"></i> Aktifkan</button>
                                    </form>
                                @else
                                    <span class="text-[var(--fg-muted)] text-[0.78rem]">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-card>
    </div>
</x-layout.app>
