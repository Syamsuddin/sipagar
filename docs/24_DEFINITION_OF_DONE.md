# 24 — Definition of Done (universal)

Task/slice dinyatakan selesai hanya bila **semua** terpenuhi:

1. Kriteria terima fitur di docs/23 terpenuhi dan dibuktikan blok verifikasinya.
2. `php artisan test` hijau seluruhnya (bukan hanya file terkait) — tanpa `skip` baru, tanpa assertion dilonggarkan.
3. `vendor/bin/pint --test` PASS.
4. Migrasi baru punya `down()` yang berjalan (`migrate:rollback --step=1` lalu `migrate` lokal).
5. Struktur & penamaan sesuai docs/12; rumus hanya di `SerapanCalculator`; tidak ada logika di Blade/Controller.
6. Tidak melanggar docs/20 & docs/21; tidak keluar scope docs/02.
7. Bila menyentuh UI: hanya komponen docs/26, layak di 768/480 px, tangkapan layar dibandingkan prototipe.
8. Bila menyentuh skema/perintah/struktur/UI: dokumen pemilik + `_MANIFEST.json` diperbarui dalam PR yang sama.
9. Laporan ke pengguna jujur: apa yang lulus, apa yang gagal, apa yang dilewati.
