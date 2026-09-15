<x-layout.app title="Master Sumber Dana">
    <div class="form-grid grid grid-cols-[400px_1fr] gap-6 items-start">
        <x-card :title="$edit ? 'Ubah Sumber Dana' : 'Tambah Sumber Dana'" :icon="$edit ? 'fa-pen-to-square' : 'fa-plus-circle'" class="sticky top-[90px]">
            <form method="POST" action="{{ $edit ? route('master.sumber-dana.update', $edit) : route('master.sumber-dana.store') }}" autocomplete="off" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf
                @if ($edit) @method('PUT') @endif
                <div class="mb-4"><x-form-input name="kode" label="Kode" :value="$edit?->kode" placeholder="Contoh: DAK" required maxlength="30" /></div>
                <div class="mb-4"><x-form-input name="nama" label="Nama / Label" :value="$edit?->nama" placeholder="Contoh: Dana Alokasi Khusus (DAK)" required /></div>
                <div class="mb-4">
                    <x-form-select name="css_class" label="Warna Badge">
                        @foreach ($kelasBadge as $k)
                            <option value="{{ $k }}" @selected(old('css_class', $edit?->css_class ?? 'sd-lainnya') === $k)>{{ $k }}</option>
                        @endforeach
                    </x-form-select>
                </div>
                <div class="mb-4"><x-form-input name="urutan" label="Urutan" type="number" :value="$edit?->urutan ?? 0" min="0" max="255" /></div>
                @if ($edit)
                    <div class="mb-5"><label class="form-label">Status</label><label class="flex items-center gap-2 text-[0.875rem]"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $edit->is_active))> Aktif</label></div>
                @endif
                <div class="flex gap-[10px]">
                    @if ($edit)<x-btn variant="secondary" :href="route('master.sumber-dana.index')" class="flex-1 justify-center">Batal</x-btn>@endif
                    <x-btn type="submit" variant="primary" class="flex-1 justify-center" :icon="$edit ? 'fa-check' : 'fa-plus'" x-bind:disabled="memproses"><span x-show="!memproses">{{ $edit ? 'Perbarui' : 'Simpan' }}</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn>
                </div>
            </form>
        </x-card>

        <x-card title="Daftar Sumber Dana" icon="fa-coins">
            <x-slot:aksi><x-badge color="green">{{ $daftar->count() }} sumber</x-badge></x-slot:aksi>
            @if ($daftar->isEmpty())
                <x-empty-state icon="fa-coins" />
            @else
                <x-data-table>
                    <x-slot:head><th>No</th><th>Kode</th><th>Nama</th><th class="hide-mobile">Badge</th><th>Status</th><th class="text-center">Aksi</th></x-slot:head>
                    @foreach ($daftar as $i => $s)
                        <tr>
                            <td class="text-[var(--fg-muted)] font-semibold">{{ $i + 1 }}</td>
                            <td class="font-semibold">{{ $s->kode }}</td>
                            <td>{{ $s->nama }}</td>
                            <td class="hide-mobile"><x-badge-sumber :sumber="$s" /></td>
                            <td><x-badge :color="$s->is_active ? 'green' : 'red'">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                            <td class="text-center"><a href="{{ route('master.sumber-dana.index', ['edit' => $s->id]) }}" class="btn-edit no-underline" title="Ubah"><i class="fa-solid fa-pen-to-square"></i></a></td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-card>
    </div>
</x-layout.app>
