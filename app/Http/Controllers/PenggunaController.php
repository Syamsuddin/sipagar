<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengguna\ResetSandiRequest;
use App\Http\Requests\Pengguna\StoreUserRequest;
use App\Http\Requests\Pengguna\UpdateUserRequest;
use App\Models\Bidang;
use App\Models\User;
use App\Services\PenggunaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    public function __construct(private readonly PenggunaService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $edit = $request->filled('edit') ? User::findOrFail($request->integer('edit')) : null;

        return view('pengguna.index', [
            'pengguna' => User::with('bidang')->orderBy('name')->get(),
            'daftarBidang' => Bidang::where('is_active', true)->orderBy('urutan')->get(),
            'edit' => $edit,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->tambah($request->validated());

        return redirect()->route('pengguna.index')->with('sukses', 'Pengguna baru berhasil ditambahkan');
    }

    public function update(UpdateUserRequest $request, User $pengguna): RedirectResponse
    {
        $this->service->ubah($pengguna, $request->validated());

        return redirect()->route('pengguna.index')->with('sukses', 'Pengguna berhasil diperbarui');
    }

    public function toggleAktif(Request $request, User $pengguna): RedirectResponse
    {
        $this->authorize('kelolaStatus', $pengguna);

        $this->service->setAktif($pengguna, ! $pengguna->is_active);

        return redirect()->route('pengguna.index')->with('info', $pengguna->is_active ? 'Pengguna diaktifkan' : 'Pengguna dinonaktifkan');
    }

    public function resetSandi(ResetSandiRequest $request, User $pengguna): RedirectResponse
    {
        $this->service->resetSandi($pengguna, $request->string('password'));

        return redirect()->route('pengguna.index')->with('sukses', "Sandi {$pengguna->username} berhasil direset");
    }
}
