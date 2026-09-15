<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreSumberDanaRequest;
use App\Http\Requests\Master\UpdateSumberDanaRequest;
use App\Models\SumberDana;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SumberDanaController extends Controller
{
    /** Kelas badge yang tersedia di app.css (docs/26). */
    public const KELAS_BADGE = ['sd-apbd', 'sd-apbn', 'sd-dt', 'sd-ban', 'sd-blud', 'sd-tpp', 'sd-dau', 'sd-dak', 'sd-dbhp', 'sd-lainnya'];

    public function index(Request $request): View
    {
        return view('master.sumber-dana.index', [
            'daftar' => SumberDana::orderBy('urutan')->orderBy('kode')->get(),
            'edit' => $request->filled('edit') ? SumberDana::findOrFail($request->integer('edit')) : null,
            'kelasBadge' => self::KELAS_BADGE,
        ]);
    }

    public function store(StoreSumberDanaRequest $request): RedirectResponse
    {
        SumberDana::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master.sumber-dana.index')->with('sukses', 'Sumber dana baru berhasil ditambahkan');
    }

    public function update(UpdateSumberDanaRequest $request, SumberDana $sumberDana): RedirectResponse
    {
        $sumberDana->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master.sumber-dana.index')->with('sukses', 'Sumber dana berhasil diperbarui');
    }
}
