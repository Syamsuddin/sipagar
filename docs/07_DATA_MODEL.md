# 07 — Data Model (sumber kebenaran skema)

MySQL 8, InnoDB, `utf8mb4_unicode_ci`. Satu migrasi Laravel per tabel di `database/migrations/`. Dokumen lain **merujuk** ke sini, tidak menyalin. Bila migrasi aktual ≠ dokumen ini → kode menang, hentikan & lapor (INDEX aturan emas #5).

Konvensi umum: PK `id` BIGINT UNSIGNED auto; `created_at`/`updated_at` TIMESTAMP; tabel bertanda ⟲ punya `deleted_at` (soft delete); tabel bertanda ✎ dicatat ke `audit_logs`. Rupiah selalu `BIGINT UNSIGNED` (bulat). Persen `DECIMAL(5,2)`.

## users ⟲ ✎
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| name | VARCHAR(100) | NOT NULL | nama tampil (avatar = huruf pertama) |
| username | VARCHAR(50) | UNIQUE, NOT NULL | lower-case, ≥ 3 karakter |
| password | VARCHAR(255) | NOT NULL | bcrypt |
| role | ENUM('admin','operator','pimpinan') | NOT NULL | docs/05 |
| bidang_id | BIGINT UNSIGNED | FK bidang.id NULLABLE, ON DELETE RESTRICT | wajib terisi bila role=operator (validasi aplikasi) |
| is_active | TINYINT(1) | NOT NULL DEFAULT 1 | |
| last_login_at | TIMESTAMP | NULL | |
| remember_token | VARCHAR(100) | NULL | |

## bidang ✎
| Kolom | Tipe | Constraint |
|---|---|---|
| kode | VARCHAR(10) | UNIQUE, NOT NULL |
| nama | VARCHAR(150) | NOT NULL |
| urutan | TINYINT UNSIGNED | NOT NULL DEFAULT 0 |
| is_active | TINYINT(1) | NOT NULL DEFAULT 1 |

## sumber_dana ✎
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| kode | VARCHAR(30) | UNIQUE, NOT NULL | 'APBD II', 'DAK', … (15 nilai prototipe) |
| nama | VARCHAR(100) | NOT NULL | label dropdown/badge |
| css_class | VARCHAR(30) | NOT NULL DEFAULT 'sd-lainnya' | kelas badge docs/26 |
| urutan | TINYINT UNSIGNED | NOT NULL DEFAULT 0 | |
| is_active | TINYINT(1) | NOT NULL DEFAULT 1 | |

## tahun_anggaran ✎
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| tahun | SMALLINT UNSIGNED | UNIQUE, NOT NULL, CHECK 2020–2034 | |
| status | ENUM('draft','aktif','terkunci') | NOT NULL DEFAULT 'draft' | maks satu 'aktif' — dijaga `TahunAnggaranService::aktifkan()` (docs/16 #9), bukan constraint DB |
| locked_at | TIMESTAMP | NULL | |
| locked_by | BIGINT UNSIGNED | FK users.id NULL | |

## program ⟲ ✎
| Kolom | Tipe | Constraint |
|---|---|---|
| tahun_anggaran_id | BIGINT UNSIGNED | FK tahun_anggaran.id, ON DELETE RESTRICT |
| kode | VARCHAR(20) | NOT NULL; UNIQUE (tahun_anggaran_id, kode) |
| nama | VARCHAR(255) | NOT NULL |
| urutan | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 |

## kegiatan ⟲ ✎
| Kolom | Tipe | Constraint |
|---|---|---|
| program_id | BIGINT UNSIGNED | FK program.id, ON DELETE RESTRICT |
| kode | VARCHAR(20) | NOT NULL; UNIQUE (program_id, kode) |
| nama | VARCHAR(255) | NOT NULL |
| urutan | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 |

## sub_kegiatan ⟲ ✎
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| kegiatan_id | BIGINT UNSIGNED | FK kegiatan.id, ON DELETE RESTRICT | |
| bidang_id | BIGINT UNSIGNED | FK bidang.id, ON DELETE RESTRICT | scope Operator |
| sumber_dana_id | BIGINT UNSIGNED | FK sumber_dana.id, ON DELETE RESTRICT | |
| kode | VARCHAR(25) | NOT NULL; UNIQUE (kegiatan_id, kode) | |
| nama | VARCHAR(255) | NOT NULL | |
| pagu | BIGINT UNSIGNED | NOT NULL, CHECK pagu > 0 | |
| pptk | VARCHAR(100) | NULL | nama PPTK |
| urutan | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 | |

Indeks: (bidang_id), (sumber_dana_id), (kegiatan_id, urutan).

## target_triwulan ✎
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| sub_kegiatan_id | BIGINT UNSIGNED | FK sub_kegiatan.id, ON DELETE CASCADE | |
| triwulan | TINYINT UNSIGNED | NOT NULL, CHECK 1–4; UNIQUE (sub_kegiatan_id, triwulan) | |
| target_keuangan | BIGINT UNSIGNED | NOT NULL DEFAULT 0 | kumulatif (docs/04) |
| target_fisik | DECIMAL(5,2) | NOT NULL DEFAULT 0, CHECK 0–100 | kumulatif |

## realisasi_keuangan ⟲ ✎
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| sub_kegiatan_id | BIGINT UNSIGNED | FK sub_kegiatan.id, ON DELETE RESTRICT | |
| tanggal | DATE | NOT NULL | dalam tahun anggaran |
| jumlah | BIGINT UNSIGNED | NOT NULL, CHECK jumlah > 0 | |
| uraian | VARCHAR(500) | NOT NULL | |
| no_sp2d | VARCHAR(50) | NULL | |
| lampiran_path | VARCHAR(255) | NULL | disk `private`, `lampiran/{tahun}/{id}.{ext}` |
| created_by | BIGINT UNSIGNED | FK users.id, ON DELETE RESTRICT | |
| updated_by | BIGINT UNSIGNED | FK users.id NULL | |

Indeks: (sub_kegiatan_id, tanggal), (tanggal).

## realisasi_fisik ✎
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| sub_kegiatan_id | BIGINT UNSIGNED | FK sub_kegiatan.id, ON DELETE CASCADE | |
| bulan | TINYINT UNSIGNED | NOT NULL, CHECK 1–12; UNIQUE (sub_kegiatan_id, bulan) | |
| persen | DECIMAL(5,2) | NOT NULL, CHECK 0–100 | kumulatif |
| keterangan | VARCHAR(255) | NULL | |
| updated_by | BIGINT UNSIGNED | FK users.id NULL | |

## audit_logs
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| user_id | BIGINT UNSIGNED | FK users.id NULL | null utk aksi sistem/seeder |
| action | VARCHAR(30) | NOT NULL | created / updated / deleted / restored / login / logout / lock_tahun / unlock_tahun / reset_password |
| auditable_type | VARCHAR(100) | NULL | FQCN model |
| auditable_id | BIGINT UNSIGNED | NULL | |
| old_values | JSON | NULL | |
| new_values | JSON | NULL | |
| ip_address | VARCHAR(45) | NULL | |
| user_agent | VARCHAR(255) | NULL | |
| created_at | TIMESTAMP | NOT NULL | tanpa updated_at; tabel append-only |

Indeks: (auditable_type, auditable_id), (user_id, created_at).

## settings
| Kolom | Tipe | Constraint | Catatan |
|---|---|---|---|
| key | VARCHAR(50) | PK | `kop_nama_instansi`, `kop_alamat`, `kop_logo_path`, `ttd_nama`, `ttd_nip`, `ttd_jabatan`, `ttd_kota` |
| value | TEXT | NULL | |

## Tabel bawaan Laravel
`sessions`, `cache`, `cache_locks`, `jobs`, `failed_jobs`, `password_reset_tokens` (tidak dipakai — tidak ada reset mandiri, biarkan ada).

## Nilai turunan
Sisa, serapan, deviasi, status **tidak disimpan**; dihitung di `SerapanCalculator` (docs/04) dan query laporan (docs/08). Tidak ada view SQL di v2 (view di `SCHEMA.sql` prototipe tidak dipakai).

## Data sensitif
`users.password` bcrypt; `audit_logs` tidak boleh memuat `password` (kolom dikecualikan di trait audit); lampiran di disk private, diakses hanya via route ber-otorisasi (docs/21).
