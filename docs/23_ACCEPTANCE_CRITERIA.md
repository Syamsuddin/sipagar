# 23 — Kriteria Terima per Fitur

Terarsip (selesai): F01–F11, F13 → `docs/_archive/23-F0x.md`.

Selesai = semua kriteria fitur di bawah **+** docs/24. Pola UI (states, breakpoint, komponen) → docs/26; setiap layar data wajib memenuhi "Kriteria UI umum" di akhir dokumen. Perintah → docs/11.

## F12 — Audit, soft delete, kunci tahun
- Setiap create/update/delete/restore model ✎ (docs/07) menghasilkan baris `audit_logs` dengan `old_values`/`new_values` tanpa `password`.
- Kunci tahun: modal sandi → status `terkunci`, `locked_at/by`; tulis apa pun pada tahun itu → 423; buka kunci dicatat.
- Layar Audit Log: filter user, aksi, model, rentang tanggal; hanya Admin.
```
php artisan test tests/Feature/AuditLogTest.php tests/Feature/TahunAnggaranLockTest.php → passed
```

## Kriteria UI umum (semua layar; detail docs/26)
- Layar dibangun hanya dari komponen `x-*` docs/26; tidak ada inline style baru selain nilai dinamis (lebar progress).
- State kosong memakai `<x-empty-state>`; error validasi memakai `.input-error` + teks; sukses/gagal memakai toast.
- Layak di 768 px (grid 2 kolom / 1 kolom) dan 480 px (stat-grid 1 kolom, `.hide-mobile` tersembunyi).
- Aksi destruktif & edit data anggaran memakai `<x-modal-konfirmasi-sandi>`.
- Tangkapan layar Dashboard v2 dibandingkan dengan prototipe pada 1320 px: header, nav, kartu, tabel tidak berbeda secara kasat mata.
