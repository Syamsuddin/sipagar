# 18 — Aturan Perbaikan Bug

Alur: **reproduksi** (tes Pest yang gagal) → **akar masalah** (bukan gejala; cek docs/16 dulu) → **perbaikan minimal** → tes regresi hijau (docs/11) → catat di PR.

Larangan:
- Perbaikan tidak melebihi scope bug; refactor luas butuh izin (docs/22).
- Dilarang "menghijaukan" tes dengan melonggarkan assertion, menghapus tes, atau `skip`.
- Dilarang mengubah rumus docs/04 atau ambang status untuk "menyesuaikan" hasil — bila rumus salah, ubah docs/04 + `_MANIFEST.json` dulu lewat konfirmasi.
- Bug tampilan diperbaiki di `app.css`/komponen Blade, bukan dengan inline style atau kelas Tailwind warna baru (docs/26).
- Bila bug berasal dari selisih dokumen vs kode → lapor, jangan pilih diam-diam (INDEX aturan emas #5).
