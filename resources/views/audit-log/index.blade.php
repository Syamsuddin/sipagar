<x-layout.app title="Audit Log">
    <x-card class="mb-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="font-bold text-[0.95rem] flex items-center gap-2"><i class="fa-solid fa-filter text-[var(--accent)] text-[0.85rem]"></i>Filter Audit</div>
            <form x-data method="GET" action="{{ route('audit-log.index') }}" class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="user">Pengguna:</label>
                    <select class="form-input !w-[170px] !py-2 !px-3" id="user" name="user" @change="$el.form.submit()"><option value="">Semua</option>
                        @foreach ($daftarUser as $u)<option value="{{ $u->id }}" @selected(($filter['user'] ?? null) == $u->id)>{{ $u->name }}</option>@endforeach</select></div>
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="aksi">Aksi:</label>
                    <select class="form-input !w-[150px] !py-2 !px-3" id="aksi" name="aksi" @change="$el.form.submit()"><option value="">Semua</option>
                        @foreach ($daftarAksi as $a)<option value="{{ $a }}" @selected(($filter['aksi'] ?? null) === $a)>{{ $a }}</option>@endforeach</select></div>
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="model">Model:</label>
                    <select class="form-input !w-[170px] !py-2 !px-3" id="model" name="model" @change="$el.form.submit()"><option value="">Semua</option>
                        @foreach ($daftarModel as $m)<option value="{{ $m }}" @selected(($filter['model'] ?? null) === $m)>{{ $m }}</option>@endforeach</select></div>
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="dari">Dari:</label><input class="form-input !w-[150px] !py-2 !px-3" type="date" id="dari" name="dari" value="{{ $filter['dari'] ?? '' }}" @change="$el.form.submit()"></div>
                <div class="flex items-center gap-2"><label class="form-label !mb-0" for="sampai">Sampai:</label><input class="form-input !w-[150px] !py-2 !px-3" type="date" id="sampai" name="sampai" value="{{ $filter['sampai'] ?? '' }}" @change="$el.form.submit()"></div>
            </form>
        </div>
        @if ($errors->any())<div class="pw-error show mt-3"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $errors->first() }}</span></div>@endif
    </x-card>

    <x-card title="Jejak Audit" icon="fa-clock-rotate-left">
        <x-slot:aksi><x-badge color="green">{{ $log->total() }} catatan</x-badge></x-slot:aksi>
        @if ($log->isEmpty())
            <x-empty-state icon="fa-clock-rotate-left" />
        @else
            <x-data-table>
                <x-slot:head><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Objek</th><th class="hide-mobile">Perubahan</th><th class="hide-mobile">IP</th></x-slot:head>
                @foreach ($log as $l)
                    @php
                        $warna = match ($l->action) { 'created', 'login', 'restored' => 'green', 'updated', 'lock_tahun', 'unlock_tahun', 'reset_password' => 'yellow', default => 'red' };
                        $kunci = array_unique(array_merge(array_keys($l->old_values ?? []), array_keys($l->new_values ?? [])));
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap text-[var(--fg-muted)] text-[0.8rem]">{{ $l->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="font-semibold">{{ $l->user?->name ?? 'sistem' }}</td>
                        <td><x-badge :color="$warna">{{ $l->action }}</x-badge></td>
                        <td class="text-[0.8rem]">{{ $l->auditable_type ? class_basename($l->auditable_type).' #'.$l->auditable_id : '—' }}</td>
                        <td class="hide-mobile text-[0.75rem] text-[var(--fg-muted)] max-w-[420px]">
                            @foreach ($kunci as $k)
                                <div><span class="text-[var(--fg)]">{{ $k }}</span>: {{ is_array($l->old_values[$k] ?? null) ? json_encode($l->old_values[$k]) : ($l->old_values[$k] ?? '∅') }} → <span class="text-[var(--accent)]">{{ is_array($l->new_values[$k] ?? null) ? json_encode($l->new_values[$k]) : ($l->new_values[$k] ?? '∅') }}</span></div>
                            @endforeach
                        </td>
                        <td class="hide-mobile text-[var(--fg-muted)] text-[0.75rem]">{{ $l->ip_address }}</td>
                    </tr>
                @endforeach
            </x-data-table>
            @if ($log->hasPages())
                <div class="flex items-center justify-between flex-wrap gap-3 mt-4">
                    <span class="text-[0.78rem] text-[var(--fg-muted)]">Halaman {{ $log->currentPage() }} dari {{ $log->lastPage() }}</span>
                    <div class="flex gap-2">
                        @if ($log->onFirstPage())<span class="btn-secondary opacity-50">‹ Sebelumnya</span>@else<a class="btn-secondary no-underline" href="{{ $log->previousPageUrl() }}">‹ Sebelumnya</a>@endif
                        @if ($log->hasMorePages())<a class="btn-secondary no-underline" href="{{ $log->nextPageUrl() }}">Berikutnya ›</a>@else<span class="btn-secondary opacity-50">Berikutnya ›</span>@endif
                    </div>
                </div>
            @endif
        @endif
    </x-card>
</x-layout.app>
