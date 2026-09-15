# 00 — Executive Summary

## Masalah
BKPSDM Kab. Hulu Sungai Selatan memantau pagu dan realisasi anggaran lewat prototipe `SIPAGAR BKPSDM.html`: satu pengguna per browser (localStorage), data tidak terbagi antar bidang, tidak ada target triwulan, tidak ada capaian fisik, dan laporan monev triwulan masih direkap manual di Excel.

## Solusi
SIPAGAR v2 — aplikasi web Laravel multi-user dengan struktur anggaran Program → Kegiatan → Sub Kegiatan, target triwulan keuangan & fisik, realisasi keuangan per transaksi dan fisik per bulan, dashboard serapan, serta empat laporan (Monev Triwulan, Rekap Sumber Dana & Bidang, Buku Realisasi, Tren Serapan) yang dapat diunduh sebagai Excel dan PDF. Tampilan **identik** dengan prototipe (docs/26).

## Nilai & metrik sukses
- Laporan Monev Triwulan tersusun **< 5 menit** dari data transaksi, tanpa rekap manual.
- Setiap perubahan data tercatat (audit log); data tahun yang sudah ditutup tidak bisa diubah.

## Pengguna sasaran
| Peran | Siapa |
|---|---|
| Admin | Subbag Perencanaan & Keuangan — pemilik struktur anggaran & pengguna |
| Operator | PPTK / bendahara pembantu tiap Bidang — pencatat target & realisasi bidangnya |
| Pimpinan | Kepala Badan / Sekretaris — pembaca dashboard & laporan |

## Status & cakupan
Greenfield (tulis ulang dari prototipe). Cakupan MVP: fitur F01–F13 (docs/01); batas di docs/02; urutan slice di docs/03. Satu OPD; verifikasi berjenjang, notifikasi, impor DPA, multi-OPD ditunda.
