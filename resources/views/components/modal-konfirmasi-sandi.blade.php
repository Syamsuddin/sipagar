{{-- <x-modal-konfirmasi-sandi id title pesan variant="danger|warning" icon tombol>
     Dibuka lewat $dispatch('buka-konfirmasi', { id, aksi }); aksi = <form> yang di-submit setelah sandi benar.
     confirmPassword() → POST /konfirmasi-sandi (docs/21); 3 gagal → modal ditutup. --}}
@props(['id', 'title' => 'Konfirmasi', 'pesan' => 'Masukkan kata sandi untuk melanjutkan.', 'variant' => 'danger', 'icon' => 'fa-lock', 'tombol' => 'Konfirmasi'])
<div class="modal-overlay" :class="{ show: terbuka }" id="modal-{{ $id }}"
     x-data="konfirmasiSandi('{{ Route::has('konfirmasi-sandi') ? route('konfirmasi-sandi') : '/konfirmasi-sandi' }}')"
     x-on:buka-konfirmasi.window="if ($event.detail.id === '{{ $id }}') buka($event.detail.aksi)"
     x-on:keydown.escape.window="tutup()" @click.self="tutup()">
    <div class="modal-box">
        <div class="text-center mb-5">
            <div class="modal-icon-lock {{ $variant }}" :class="{ 'lock-shake': goyang }"><i class="fa-solid {{ $icon }} text-[1.4rem]"></i></div>
            <h3 class="font-bold text-[1.1rem] mb-2">{{ $title }}</h3>
            <p class="text-[0.85rem] text-[var(--fg-muted)]">{{ $pesan }}</p>
        </div>
        <form @submit.prevent="konfirmasi()">
            <div class="mb-4" x-data="toggleSandi()">
                <label class="form-label" for="{{ $id }}-sandi">Masukkan Kata Sandi</label>
                <div class="relative">
                    <input class="form-input !pl-[42px]" :class="{ 'input-error': salah }" :type="tipe" id="{{ $id }}-sandi" placeholder="Kata sandi Anda" autocomplete="off" x-model="sandi" x-ref="sandi" @input="salah = false">
                    <i class="fa-solid fa-lock absolute left-[14px] top-1/2 -translate-y-1/2 text-[var(--fg-muted)] text-[0.85rem] pointer-events-none"></i>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 bg-transparent border-0 text-[var(--fg-muted)] cursor-pointer p-1" @click="balik()" aria-label="Tampilkan"><i class="fa-solid" :class="terlihat ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                </div>
                <div class="pw-error" :class="{ show: salah }"><i class="fa-solid fa-circle-xmark"></i> <span x-text="percobaan ? 'Kata sandi salah! Percobaan ' + percobaan + ' dari 3' : 'Kata sandi wajib diisi'"></span></div>
            </div>
            <div class="flex gap-[10px]">
                <button type="button" class="btn-secondary flex-1 justify-center" @click="tutup()">Batal</button>
                <button type="submit" class="{{ $variant === 'danger' ? 'btn-danger !py-3 !text-[0.875rem]' : 'btn-primary' }} flex-1 justify-center inline-flex items-center gap-2" :disabled="memproses">
                    <span x-show="!memproses"><i class="fa-solid {{ $variant === 'danger' ? 'fa-trash' : 'fa-check' }}"></i> {{ $tombol }}</span>
                    <span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>
