<x-layout.app title="Pengguna">
    <div class="form-grid grid grid-cols-[400px_1fr] gap-6 items-start" x-data="{ role: '{{ old('role', $edit?->role->value ?? '') }}' }">
        <x-card :title="$edit ? 'Ubah Pengguna' : 'Tambah Pengguna Baru'" :icon="$edit ? 'fa-user-pen' : 'fa-user-plus'" class="sticky top-[90px]">
            <form method="POST" action="{{ $edit ? route('pengguna.update', $edit) : route('pengguna.store') }}" autocomplete="off" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf
                @if ($edit) @method('PUT') @endif
                <div class="mb-4"><x-form-input name="name" label="Nama Tampil" :value="$edit?->name" placeholder="Contoh: Siti Aminah" required /></div>
                <div class="mb-4"><x-form-input name="username" label="Username" :value="$edit?->username" placeholder="huruf kecil, angka, titik" required /></div>
                @unless ($edit)
                    <div class="mb-4"><x-form-input name="password" label="Sandi Awal" type="password" placeholder="Min 8 karakter, huruf & angka" required /></div>
                @endunless
                <div class="mb-4">
                    <x-form-select name="role" label="Peran" x-model="role" required>
                        <option value="">-- Pilih peran --</option>
                        @foreach (\App\Enums\Role::pilihan() as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('role', $edit?->role->value) === $nilai)>{{ $label }}</option>
                        @endforeach
                    </x-form-select>
                </div>
                <div class="mb-5" x-show="role === 'operator'" x-cloak>
                    <x-form-select name="bidang_id" label="Bidang (wajib utk Operator)">
                        <option value="">-- Pilih bidang --</option>
                        @foreach ($daftarBidang as $b)
                            <option value="{{ $b->id }}" @selected((int) old('bidang_id', $edit?->bidang_id) === $b->id)>{{ $b->nama }}</option>
                        @endforeach
                    </x-form-select>
                </div>
                <div class="flex gap-[10px]">
                    @if ($edit)<x-btn variant="secondary" :href="route('pengguna.index')" class="flex-1 justify-center">Batal</x-btn>@endif
                    <x-btn type="submit" variant="primary" class="flex-1 justify-center" :icon="$edit ? 'fa-check' : 'fa-plus'" x-bind:disabled="memproses">
                        <span x-show="!memproses">{{ $edit ? 'Perbarui' : 'Simpan' }}</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
                    </x-btn>
                </div>
            </form>
        </x-card>

        <x-card title="Daftar Pengguna" icon="fa-users">
            <x-slot:aksi><x-badge color="green">{{ $pengguna->count() }} pengguna</x-badge></x-slot:aksi>
            @if ($pengguna->isEmpty())
                <x-empty-state icon="fa-users" />
            @else
                <x-data-table>
                    <x-slot:head><th>No</th><th>Nama</th><th>Username</th><th>Peran</th><th>Bidang</th><th>Status</th><th class="hide-mobile">Login Terakhir</th><th class="text-center">Aksi</th></x-slot:head>
                    @foreach ($pengguna as $i => $u)
                        <tr>
                            <td class="text-[var(--fg-muted)] font-semibold">{{ $i + 1 }}</td>
                            <td class="font-semibold">{{ $u->name }}</td>
                            <td class="big-number !text-[0.85rem]">{{ $u->username }}</td>
                            <td><x-badge :color="$u->isAdmin() ? 'green' : ($u->isOperator() ? 'yellow' : 'red')">{{ $u->role->label() }}</x-badge></td>
                            <td class="text-[var(--fg-muted)]">{{ $u->bidang?->nama ?? '—' }}</td>
                            <td><x-badge :color="$u->is_active ? 'green' : 'red'">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                            <td class="hide-mobile text-[var(--fg-muted)]">{{ $u->last_login_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-center">
                                <div class="flex gap-[6px] justify-center">
                                    <a href="{{ route('pengguna.index', ['edit' => $u->id]) }}" class="btn-edit no-underline" title="Ubah"><i class="fa-solid fa-pen-to-square"></i></a>
                                    @can('kelolaStatus', $u)
                                        <form method="POST" action="{{ route('pengguna.aktif', $u) }}">@csrf @method('PATCH')
                                            <button type="submit" class="{{ $u->is_active ? 'btn-danger' : 'btn-pw-settings' }}" title="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"><i class="fa-solid {{ $u->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i></button>
                                        </form>
                                        <button type="button" class="btn-pw-settings" title="Reset sandi" @click="$dispatch('buka-konfirmasi', { id: 'admin', aksi: () => $dispatch('buka-reset', { id: {{ $u->id }}, nama: @js($u->username) }) })"><i class="fa-solid fa-key"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-card>
    </div>

    <x-modal-konfirmasi-sandi id="admin" title="Konfirmasi Admin" pesan="Masukkan kata sandi Anda untuk mereset sandi pengguna." variant="warning" icon="fa-key" tombol="Lanjutkan" />

    {{-- Modal sandi baru (setelah konfirmasi admin ≤ 5 menit, middleware konfirmasi-sandi) --}}
    <div class="modal-overlay" :class="{ show: terbuka }" x-data="{ terbuka: {{ $errors->has('password') ? 'true' : 'false' }}, id: {{ old('_reset_id', 0) }}, nama: @js(old('_reset_nama', '')), tutup() { this.terbuka = false } }"
         x-on:buka-reset.window="id = $event.detail.id; nama = $event.detail.nama; terbuka = true" x-on:keydown.escape.window="tutup()" @click.self="tutup()" id="modal-reset-sandi">
        <div class="modal-box">
            <div class="text-center mb-5">
                <div class="modal-icon-lock warning"><i class="fa-solid fa-key text-[1.4rem]"></i></div>
                <h3 class="font-bold text-[1.1rem] mb-2">Reset Kata Sandi</h3>
                <p class="text-[0.85rem] text-[var(--fg-muted)]">Sandi baru untuk <strong x-text="nama"></strong></p>
            </div>
            <form method="POST" :action="'{{ url('/pengguna') }}/' + id + '/reset-sandi'" autocomplete="off" x-data="{ memproses: false }" @submit="memproses = true">
                @csrf
                <input type="hidden" name="_reset_id" :value="id"><input type="hidden" name="_reset_nama" :value="nama">
                <div class="mb-4"><x-form-input name="password" label="Sandi Baru" type="password" placeholder="Min 8 karakter, huruf & angka" icon="fa-lock" required /></div>
                <div class="flex gap-[10px]">
                    <button type="button" class="btn-secondary flex-1 justify-center" @click="tutup()">Batal</button>
                    <x-btn type="submit" variant="primary" class="flex-1 justify-center" icon="fa-check" x-bind:disabled="memproses"><span x-show="!memproses">Reset</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span></x-btn>
                </div>
            </form>
        </div>
    </div>
</x-layout.app>
