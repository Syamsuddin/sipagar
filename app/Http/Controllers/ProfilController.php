<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profil\UbahSandiRequest;
use Illuminate\Http\RedirectResponse;

class ProfilController extends Controller
{
    public function ubahSandi(UbahSandiRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->string('sandi_baru')]);

        return back()->with('sukses', 'Kata sandi berhasil diubah!');
    }
}
