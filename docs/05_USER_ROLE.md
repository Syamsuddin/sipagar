# 05 — Peran & Matriks Akses

Pemilik peran. Implementasi: kolom `users.role` (docs/07) + Policy per model (docs/08). Aturan keamanan → docs/21.

## Peran
| Peran | `role` | Bidang | Deskripsi |
|---|---|---|---|
| Admin | `admin` | null | Pemilik struktur anggaran, master, pengguna, kunci tahun |
| Operator | `operator` | **wajib** | Pencatat target & realisasi Sub Kegiatan **bidangnya saja** |
| Pimpinan | `pimpinan` | null | Baca saja: dashboard, laporan, ekspor |

## Matriks akses
| Kapabilitas | Admin | Operator | Pimpinan |
|---|---|---|---|
| Login, ubah sandi sendiri | ✓ | ✓ | ✓ |
| Dashboard & 4 laporan (web/xlsx/pdf) | ✓ | ✓ | ✓ |
| Lihat struktur anggaran, target, realisasi (semua bidang) | ✓ | ✓ | ✓ |
| CRUD Pengguna | ✓ | ✗ | ✗ |
| CRUD Bidang, Sumber Dana, Tahun Anggaran, Pengaturan | ✓ | ✗ | ✗ |
| CRUD Program/Kegiatan/Sub Kegiatan | ✓ | ✗ | ✗ |
| CRUD Target triwulan | ✓ semua | ✓ bidang sendiri | ✗ |
| CRUD Realisasi keuangan & fisik | ✓ semua | ✓ bidang sendiri | ✗ |
| Unduh lampiran realisasi | ✓ | ✓ | ✓ |
| Kunci / buka kunci tahun anggaran | ✓ (konfirmasi sandi) | ✗ | ✗ |
| Lihat Audit Log | ✓ | ✗ | ✗ |
| Hapus permanen (forceDelete) | ✗ (tidak ada UI) | ✗ | ✗ |

Aturan lintas-peran:
- Semua tulis pada tahun `terkunci` ditolak untuk **semua** peran (HTTP 423).
- Edit/hapus data anggaran & aksi kunci tahun memakai modal konfirmasi sandi (docs/26) — kebiasaan prototipe dipertahankan.
- Pengguna nonaktif (`is_active = 0`) tidak bisa login; sesi aktif diputus saat dinonaktifkan.
