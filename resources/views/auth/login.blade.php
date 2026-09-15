<x-layout.auth title="Masuk">
    <h2>Sign In</h2>
    <p class="auth-subtitle">Masuk ke akun Anda untuk melanjutkan</p>

    @if ($errors->any() || ! empty($throttle))
        <div class="auth-error show"><i class="fa-solid fa-circle-xmark"></i><span>{{ $throttle ?? $errors->first() }}</span></div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" x-data="{ memproses: false }" @submit="memproses = true">
        @csrf
        <div class="inputBox"><input type="text" id="username" name="username" placeholder="Username" autocomplete="username" value="{{ old('username') }}" class="{{ $errors->has('username') ? 'input-error' : '' }}" required autofocus><i class="fa-solid fa-user field-icon"></i></div>
        <div class="inputBox" x-data="toggleSandi()"><input :type="tipe" id="password" name="password" placeholder="Kata Sandi" autocomplete="current-password" class="{{ $errors->has('username') ? 'input-error' : '' }}" required><i class="fa-solid fa-lock field-icon"></i><button type="button" class="pw-toggle-btn" @click="balik()" aria-label="Tampilkan"><i class="fa-solid" :class="terlihat ? 'fa-eye-slash' : 'fa-eye'"></i></button></div>
        <button type="submit" class="btn-login" :disabled="memproses">
            <span x-show="!memproses"><i class="fa-solid fa-right-to-bracket mr-[6px]"></i> Login</span>
            <span x-show="memproses" x-cloak><i class="fa-solid fa-spinner fa-spin mr-[6px]"></i> Memproses...</span>
        </button>
    </form>
</x-layout.auth>
