# 22 — Kebijakan Perubahan

Prinsip: **friksi sebanding irreversibilitas.**

## Git
- Branch: `feat/S<n>-<fitur>`, `fix/<isu>`, `chore/<hal>`; basis `main`.
- Dilarang commit langsung ke `main`; masuk lewat PR; merge oleh pemilik proyek (Syamsuddin).
- Commit kecil per kriteria terima; pesan `feat(realisasi): validasi sisa pagu (F05)`.
- PR wajib: tes hijau (`php artisan test`), `pint --test` PASS, ringkasan dokumen yang terdampak.

## Operasi irreversibel → gerbang manusia (konfirmasi eksplisit sebelum dijalankan)
| Operasi | Gerbang |
|---|---|
| Migrasi destruktif (drop/rename kolom-tabel, ubah tipe yang memotong data) | konfirmasi + migrasi `down()` teruji + backup DB |
| `forceDelete`, `TRUNCATE`, `migrate:fresh`, `db:wipe` di luar lokal | **dilarang** (docs/20) |
| Kunci / buka kunci tahun anggaran | hanya lewat UI Admin + modal sandi; agen tidak menjalankannya via tinker |
| Reset sandi pengguna lain | UI Admin + modal sandi |
| Mengubah rumus docs/04 atau ambang status | konfirmasi + update docs/04 & `_MANIFEST.json` + tes unit diperbarui |
| Mengubah token/komponen docs/26 | konfirmasi + tangkapan layar sebelum/sesudah |
| Menambah dependensi | alasan tertulis di PR |
| Rilis produksi | docs/25 berurutan |

## Rollback
- Kode: `git revert` PR (bukan force-push) → deploy ulang.
- DB: `migrate:rollback --step=1` hanya bila `down()` teruji; jika tidak, restore dari backup pra-rilis (docs/25).
- Aset: `npm run build` ulang dari commit sebelumnya.
