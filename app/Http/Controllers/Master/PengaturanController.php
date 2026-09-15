<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdatePengaturanRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PengaturanController extends Controller
{
    public function index(): View
    {
        return view('master.pengaturan.index', ['pengaturan' => Setting::semua()]);
    }

    public function update(UpdatePengaturanRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('master.pengaturan.index')->with('sukses', 'Pengaturan berhasil disimpan');
    }
}
