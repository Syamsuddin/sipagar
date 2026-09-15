# 23 — Kriteria Terima per Fitur

Terarsip (selesai): F01–F13 (seluruh MVP). Kriteria UI umum tetap berlaku untuk perubahan berikutnya. → `docs/_archive/23-F0x.md`.

Selesai = semua kriteria fitur di bawah **+** docs/24. Pola UI (states, breakpoint, komponen) → docs/26; setiap layar data wajib memenuhi "Kriteria UI umum" di akhir dokumen. Perintah → docs/11.

## Kriteria UI umum (semua layar; detail docs/26)
- Layar dibangun hanya dari komponen `x-*` docs/26; tidak ada inline style baru selain nilai dinamis (lebar progress).
- State kosong memakai `<x-empty-state>`; error validasi memakai `.input-error` + teks; sukses/gagal memakai toast.
- Layak di 768 px (grid 2 kolom / 1 kolom) dan 480 px (stat-grid 1 kolom, `.hide-mobile` tersembunyi).
- Aksi destruktif & edit data anggaran memakai `<x-modal-konfirmasi-sandi>`.
- Tangkapan layar Dashboard v2 dibandingkan dengan prototipe pada 1320 px: header, nav, kartu, tabel tidak berbeda secara kasat mata.
