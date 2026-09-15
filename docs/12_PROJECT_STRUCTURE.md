# 12 — Struktur Proyek & Penamaan

Struktur Laravel 12 standar + folder tambahan di bawah. Arsitektur → docs/08.

```
app/
  Console/Commands/BuatAdminCommand.php        # sipagar:buat-admin
  Enums/{Role,StatusTahun,StatusSerapan}.php   # backed enum string
  Exports/{MonevTriwulan,RekapSumberDana,RekapBidang,BukuRealisasi,TrenSerapan}Export.php
  Http/
    Controllers/
      Auth/{Login,KonfirmasiSandi}Controller.php   # KonfirmasiSandi = POST /konfirmasi-sandi (docs/21)
      DashboardController.php
      Anggaran/{Program,Kegiatan,SubKegiatan}Controller.php
      TargetTriwulanController.php
      Realisasi/{RealisasiKeuangan,RealisasiFisik,Lampiran}Controller.php
      Laporan/{MonevTriwulan,Rekap,BukuRealisasi,TrenSerapan}Controller.php
      Master/{Bidang,SumberDana,TahunAnggaran,Pengaturan}Controller.php
      {Pengguna,AuditLog,Profil}Controller.php
    Middleware/{EnsureRole,EnsureTahunTerbuka,KonfirmasiSandi}.php
    Requests/<Modul>/{Store,Update}<Model>Request.php
  Models/{User,Bidang,SumberDana,TahunAnggaran,Program,Kegiatan,SubKegiatan,TargetTriwulan,RealisasiKeuangan,RealisasiFisik,AuditLog,Setting}.php
  Models/Concerns/{Auditable,ScopedByBidang}.php
  Policies/{SubKegiatan,TargetTriwulan,RealisasiKeuangan,RealisasiFisik,User,TahunAnggaran}Policy.php
  Queries/{DashboardQuery,MonevTriwulanQuery,RekapQuery,BukuRealisasiQuery,TrenSerapanQuery}.php
  Services/{SerapanCalculator,AnggaranService,TargetService,RealisasiService,TahunAnggaranService,PenggunaService,AuditService}.php
  Support/helpers.php                           # rupiah(), rupiah_singkat() — autoload `files` composer.json
  View/Components/Layout/{App,Auth}.php         # kelas komponen <x-layout.app>/<x-layout.auth> → views/layouts/*
config/sipagar.php                             # tahun_min/max (.env), konfirmasi_sandi_menit
database/
  migrations/                                   # satu file per tabel docs/07
  seeders/{DatabaseSeeder,MasterSeeder,AdminLokalSeeder,PrototipeSeeder}.php
resources/
  css/app.css                                   # token & komponen prototipe — sumber gaya kanonik (docs/26)
  css/print.css                                 # layout cetak PDF terang
  js/app.js                                     # Alpine + komponen: tabs, modal, toast, confirmPassword, charts
  views/
    layouts/{app,auth,pdf}.blade.php
    components/                                 # x-card, x-stat-card, x-data-table, x-badge, x-badge-sumber, x-form-input, x-form-select, x-btn, x-modal, x-modal-konfirmasi-sandi, x-toast, x-empty-state, x-progress, x-tab-nav, x-user-badge
    auth/login.blade.php
    dashboard/index.blade.php
    anggaran/…  target/…  realisasi/{keuangan,fisik}/…  laporan/{monev,rekap,buku,tren}/…
    master/{bidang,sumber-dana,tahun-anggaran,pengaturan}/…
    pengguna/…  audit-log/…  profil/…
    pdf/{monev,rekap,buku,tren}.blade.php
routes/web.php
tests/
  Feature/<Modul>Test.php                        # docs/13
  Unit/SerapanCalculatorTest.php
  Unit/RupiahHelperTest.php
deploy.sh
```

## Konvensi penamaan
| Hal | Konvensi | Contoh |
|---|---|---|
| Tabel | snake_case tunggal (bahasa Indonesia) | `sub_kegiatan`, `realisasi_keuangan` — set `$table` eksplisit di Model |
| Model/Class | PascalCase Indonesia | `SubKegiatan`, `RealisasiKeuangan` |
| Kolom | snake_case | `sub_kegiatan_id`, `no_sp2d` |
| Route name | `modul.aksi` | `realisasi.keuangan.store`, `laporan.monev.pdf` |
| URL | kebab-case | `/realisasi/keuangan`, `/laporan/buku-realisasi` |
| Komponen Blade | `x-` + nama kelas prototipe | `.stat-card` → `<x-stat-card>` |
| Enum | `app/Enums`, backed string | `Role::Operator->value === 'operator'` |
| Uang | int rupiah; format tampil `Rp 1.234.567` via helper `rupiah()`; stat-card memakai `rupiah_singkat()` (`Rp 2.8 M`, identik fRs prototipe) — keduanya di `app/Support/helpers.php` | |
| Teks UI | Bahasa Indonesia, tanpa file terjemahan | |

## Lokasi jenis kode
- Aturan bisnis → `Services`; **bukan** di Controller atau Model.
- Rumus → hanya `SerapanCalculator`.
- Query agregasi laporan → `Queries`; Export & view PDF hanya memformat.
- Otorisasi → `Policies` + middleware `EnsureRole`; jangan cek `role` manual di Blade selain `@can`.
- Tidak ada logika di Blade selain kondisi tampil.
