<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** Layar Audit Log (docs/26): filter user, aksi, model, rentang tanggal; paginasi 50; hanya Admin (route role:admin). */
class AuditLogController extends Controller
{
    public const AKSI = ['created', 'updated', 'deleted', 'restored', 'login', 'logout', 'lock_tahun', 'unlock_tahun', 'reset_password'];

    public function index(Request $request): View
    {
        $data = $request->validate([
            'user' => ['nullable', 'integer'],
            'aksi' => ['nullable', 'string', 'in:'.implode(',', self::AKSI)],
            'model' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
        ]);

        $log = AuditLog::query()->with('user')
            ->when($data['user'] ?? null, fn ($q, $u) => $q->where('user_id', $u))
            ->when($data['aksi'] ?? null, fn ($q, $a) => $q->where('action', $a))
            ->when($data['model'] ?? null, fn ($q, $m) => $q->where('auditable_type', 'App\\Models\\'.$m))
            ->when($data['dari'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($data['sampai'] ?? null, fn ($q, $s) => $q->whereDate('created_at', '<=', $s))
            ->orderByDesc('id')
            ->paginate(50)->withQueryString();

        return view('audit-log.index', [
            'log' => $log,
            'filter' => $data,
            'daftarUser' => User::withTrashed()->orderBy('name')->get(['id', 'name', 'username']),
            'daftarAksi' => self::AKSI,
            'daftarModel' => AuditLog::query()->whereNotNull('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type')
                ->map(fn ($t) => class_basename($t))->all(),
        ]);
    }
}
