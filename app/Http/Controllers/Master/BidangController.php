<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreBidangRequest;
use App\Http\Requests\Master\UpdateBidangRequest;
use App\Models\Bidang;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BidangController extends Controller
{
    public function index(Request $request): View
    {
        return view('master.bidang.index', [
            'daftar' => Bidang::withCount('users')->orderBy('urutan')->orderBy('kode')->get(),
            'edit' => $request->filled('edit') ? Bidang::findOrFail($request->integer('edit')) : null,
        ]);
    }

    public function store(StoreBidangRequest $request): RedirectResponse
    {
        Bidang::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master.bidang.index')->with('sukses', 'Bidang baru berhasil ditambahkan');
    }

    public function update(UpdateBidangRequest $request, Bidang $bidang): RedirectResponse
    {
        $bidang->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master.bidang.index')->with('sukses', 'Bidang berhasil diperbarui');
    }
}
