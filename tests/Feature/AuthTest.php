<?php

// S0: hanya tampilan login. Alur POST /login, rate limit, user nonaktif → S1 (F01, docs/13 #1).

test('halaman login tampil dengan layout auth prototipe', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('auth-box', false)
        ->assertSee('inputBox', false)
        ->assertSee('btn-login', false)
        ->assertSee('name="username"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="_token"', false)
        ->assertDontSee('Daftar Akun')
        ->assertDontSee('Lupa Kata Sandi');
});
