<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · SIPAGAR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="bg-mesh"></div>
<div class="bg-grid"></div>

<div class="content-wrap">
    <header class="sticky top-0 z-[100] border-b border-[var(--border)] bg-[rgba(10,15,13,0.85)] backdrop-blur-[16px]">
        <div class="max-w-[1320px] mx-auto px-6 py-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-[14px]">
                    <div class="w-[42px] h-[42px] bg-[var(--accent-dim)] border border-[rgba(0,230,138,0.3)] rounded-xl flex items-center justify-center"><i class="fa-solid fa-building-columns text-[var(--accent)] text-[1.1rem]"></i></div>
                    <div>
                        <div class="font-['Space_Grotesk',monospace] font-bold text-[1.1rem] tracking-[-0.02em]">SIPAGAR</div>
                        <div class="text-[0.72rem] text-[var(--fg-muted)] mt-px">Sistem Informasi Pagu Anggaran &amp; Realisasi</div>
                    </div>
                </div>
                <div class="header-right flex items-center gap-[10px]">
                    <div class="pulse-dot"></div>
                    <span class="text-[0.75rem] text-[var(--fg-muted)] hide-mobile">BKPSDM Kab. Hulu Sungai Selatan</span>
                    <x-user-badge :nama="auth()->user()->name" />
                    <button type="button" class="btn-pw-settings" title="Ubah kata sandi" x-data @click="$dispatch('buka-modal', 'ubah-password')"><i class="fa-solid fa-key"></i> <span class="hide-mobile">Sandi</span></button>
                    <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                        @csrf
                        <button type="submit" class="btn-logout" title="Keluar"><i class="fa-solid fa-right-from-bracket"></i> <span class="hide-mobile">Keluar</span></button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-[1320px] mx-auto px-6 pt-7 pb-[60px]">
        <x-tab-nav :items="$menu" />
        @if ($subMenu)
            <x-tab-nav :items="$subMenu" class="-mt-3" />
        @endif

        <section class="tab-panel active" role="tabpanel">
            {{ $slot }}
        </section>
    </main>

    <footer class="border-t border-[var(--border)] px-6 py-5 text-center"><p class="text-[0.75rem] text-[var(--fg-muted)]">SIPAGAR v2.0 — BKPSDM Kabupaten Hulu Sungai Selatan &middot; {{ date('Y') }}</p></footer>
</div>

<x-toast />

{{-- Modal ubah sandi (docs/26 §Profil): form aktif di S1 (POST /profil/sandi) --}}
<x-modal id="ubah-password" title="Ubah Kata Sandi" icon="fa-key" variant="accent" pesan="Masukkan kata sandi lama, lalu buat baru." :terbuka="$errors->hasAny(['sandi_lama', 'sandi_baru'])">
    <form method="POST" action="{{ route('profil.sandi') }}" autocomplete="off" x-data="{ memproses: false }" @submit="memproses = true">
        @csrf
        <div class="mb-[14px]"><x-form-input name="sandi_lama" label="Kata Sandi Lama" type="password" placeholder="Kata sandi lama" icon="fa-lock" required /></div>
        <div class="mb-[14px]" x-data="kekuatanSandi()">
            <x-form-input name="sandi_baru" label="Kata Sandi Baru" type="password" placeholder="Minimal 8 karakter, huruf & angka" icon="fa-lock" required minlength="8" x-model="sandi" />
            <div class="pw-strength-track"><div class="pw-strength-fill" :style="'width:' + tingkat.w + ';background:' + tingkat.c"></div></div>
            <div class="pw-strength-text" x-text="tingkat.t"></div>
        </div>
        <div class="mb-5"><x-form-input name="sandi_baru_confirmation" label="Konfirmasi" type="password" placeholder="Ulangi kata sandi baru" icon="fa-lock" required minlength="8" /></div>
        <div class="flex gap-[10px]">
            <x-btn variant="secondary" class="flex-1 justify-center" @click="tutup()">Batal</x-btn>
            <x-btn type="submit" variant="primary" class="flex-1 justify-center" icon="fa-check" x-bind:disabled="memproses">
                <span x-show="!memproses">Simpan</span><span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
            </x-btn>
        </div>
    </form>
</x-modal>

</body>
</html>
