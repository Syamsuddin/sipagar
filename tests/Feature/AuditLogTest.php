<?php

use App\Models\AuditLog;
use App\Models\Bidang;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;
use Database\Seeders\MasterSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('create/update/delete/restore model ✎ menghasilkan audit dengan old/new tanpa password (docs/13 #7)', function () {
    AuditLog::query()->delete();

    $bidang = Bidang::factory()->create(['kode' => 'AUD', 'nama' => 'Awal']);
    $bidang->update(['nama' => 'Diubah', 'urutan' => 5]);
    $sk = SubKegiatan::factory()->create(['pagu' => 100]);
    $sk->delete();
    $sk->restore();
    $user = User::factory()->create(['password' => 'Rahasia123', 'name' => 'Budi']);
    $user->update(['password' => 'Rahasia456', 'name' => 'Budi Baru']);

    $created = AuditLog::where('auditable_type', Bidang::class)->where('auditable_id', $bidang->id)->where('action', 'created')->firstOrFail();
    expect($created->old_values)->toBeNull()->and($created->new_values)->toMatchArray(['kode' => 'AUD', 'nama' => 'Awal'])->and($created->new_values)->not->toHaveKey('created_at');

    $updated = AuditLog::where('auditable_type', Bidang::class)->where('auditable_id', $bidang->id)->where('action', 'updated')->firstOrFail();
    expect($updated->old_values)->toBe(['nama' => 'Awal', 'urutan' => 0])->and($updated->new_values)->toBe(['nama' => 'Diubah', 'urutan' => 5]);

    expect(AuditLog::where('auditable_type', SubKegiatan::class)->where('auditable_id', $sk->id)->pluck('action')->all())->toBe(['created', 'deleted', 'restored']);
    $deleted = AuditLog::where('auditable_type', SubKegiatan::class)->where('action', 'deleted')->first();
    expect($deleted->old_values)->toMatchArray(['pagu' => 100])->and($deleted->new_values)->toBeNull();

    $userLogs = AuditLog::where('auditable_type', User::class)->where('auditable_id', $user->id)->get();
    expect($userLogs)->toHaveCount(2);
    foreach ($userLogs as $l) {
        expect(json_encode($l->old_values ?? []).json_encode($l->new_values ?? []))->not->toContain('password')->not->toContain('Rahasia');
    }
    expect($userLogs->firstWhere('action', 'updated')->new_values)->toBe(['name' => 'Budi Baru']);
});

test('perubahan hanya stempel waktu / tanpaAudit tidak dicatat; user_id null utk seeder', function () {
    $b = Bidang::factory()->create();
    AuditLog::query()->delete();

    $b->touch();
    Bidang::tanpaAudit(fn () => $b->update(['nama' => 'Senyap']));
    expect(AuditLog::count())->toBe(0)->and($b->fresh()->nama)->toBe('Senyap');

    $this->seed(MasterSeeder::class);
    expect(AuditLog::where('auditable_type', SumberDana::class)->where('action', 'created')->count())->toBe(15)
        ->and(AuditLog::whereNotNull('user_id')->count())->toBe(0);
});

test('audit_logs append-only: tidak ada route ubah/hapus; layar Audit Log hanya Admin dengan filter & paginasi 50', function () {
    expect(collect(app('router')->getRoutes()->getRoutesByMethod()['PUT'] ?? [])->keys()->filter(fn ($u) => str_contains($u, 'audit'))->isEmpty())->toBeTrue()
        ->and(collect(app('router')->getRoutes()->getRoutesByMethod()['DELETE'] ?? [])->keys()->filter(fn ($u) => str_contains($u, 'audit'))->isEmpty())->toBeTrue();

    $this->actingAs(User::factory()->operator()->create())->get(route('audit-log.index'))->assertForbidden();
    $this->actingAs(User::factory()->pimpinan()->create())->get(route('audit-log.index'))->assertForbidden();

    $lain = User::factory()->admin()->create(['name' => 'Admin Lain']);
    AuditLog::query()->delete(); // buang audit pembuatan user di atas
    foreach (range(1, 60) as $i) {
        AuditLog::forceCreate(['user_id' => $this->admin->id, 'action' => 'created', 'auditable_type' => Bidang::class, 'auditable_id' => $i, 'new_values' => ['kode' => "B{$i}"], 'created_at' => now()->subDays(70 - $i)]);
    }
    AuditLog::forceCreate(['user_id' => $lain->id, 'action' => 'lock_tahun', 'auditable_type' => TahunAnggaran::class, 'auditable_id' => 1, 'created_at' => now()]);

    $this->actingAs($this->admin)->get(route('audit-log.index'))->assertOk()->assertSee('61 catatan')->assertSee('Halaman 1 dari 2')->assertSee('data-table', false)->assertSee('class="tab-btn', false);
    $this->actingAs($this->admin)->get(route('audit-log.index', ['aksi' => 'lock_tahun']))->assertOk()->assertSee('1 catatan')->assertSee('Admin Lain');
    $this->actingAs($this->admin)->get(route('audit-log.index', ['user' => $lain->id]))->assertOk()->assertSee('1 catatan');
    $this->actingAs($this->admin)->get(route('audit-log.index', ['model' => 'TahunAnggaran']))->assertOk()->assertSee('1 catatan');
    $this->actingAs($this->admin)->get(route('audit-log.index', ['dari' => now()->subDays(15)->toDateString(), 'sampai' => now()->toDateString()]))->assertOk()->assertSee('7 catatan'); // i=55..60 (15..10 hari lalu) + lock_tahun
    $this->actingAs($this->admin)->get(route('audit-log.index', ['aksi' => 'hack']))->assertSessionHasErrors('aksi');
});

test('setiap respons membawa X-Request-Id (docs/15) dan header keamanan', function () {
    $res = $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
    expect($res->headers->get('X-Request-Id'))->toHaveLength(26);
    $res->assertHeader('X-Frame-Options', 'DENY');
});
