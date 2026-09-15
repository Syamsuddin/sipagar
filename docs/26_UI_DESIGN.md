# 26 — UI Design (sumber kebenaran antarmuka)

**Aturan utama: tampilan v2 identik dengan prototipe `SIPAGAR BKPSDM.html`.** CSS prototipe (`<style>` blok, ±150 baris) diangkat utuh ke `resources/css/app.css` dan menjadi design system kanonik. Tailwind hanya utilitas layout (`grid`, `flex`, `gap-*`, `mb-*`, `w-*`) untuk menggantikan inline style prototipe; `preflight` dimatikan. **Dilarang** membuat komponen tandingan, warna/font/radius baru, atau tema terang. Versi paket → docs/09. Hak akses layar → docs/05.

## Token (CSS variables di `:root`, nilai persis prototipe)
| Token | Nilai | Pakai untuk |
|---|---|---|
| `--bg` | `#0a0f0d` | latar halaman, input, teks di atas tombol aksen |
| `--bg-card` / `--bg-card-hover` | `#111a16` / `#162420` | kartu, modal, option select |
| `--fg` / `--fg-muted` | `#e8f0ec` / `#7a9488` | teks utama / label, sub-teks, ikon pasif |
| `--accent` / `--accent-dim` / `--accent-glow` | `#00e68a` / `rgba(0,230,138,.12)` / `rgba(0,230,138,.25)` | aksi utama, tab aktif, badge hijau, hover baris; hover tombol `#00ff99` |
| `--danger` / `--danger-dim` | `#ff5c5c` / `rgba(255,92,92,.12)` | realisasi, hapus, error, badge merah |
| `--warning` / `--warning-dim` | `#ffb347` / `rgba(255,179,71,.12)` | sisa, edit, badge kuning |
| teal | `#00b4d8` / `rgba(0,180,216,.12)` | serapan, toast-info |
| Toast latar | `.toast-success #0d2a1a` · `.toast-error #2a0d0d` · `.toast-info #0d1a2a` (persis prototipe) | latar toast |
| Palet PDF (`print.css`, pengecualian tema) | teks `#111`, garis `#444`/`#e4e4e4`, latar th `#eee`, subtotal `#f5f5f5`, total `#f0f0f0`, muted `#555` | hanya `layouts/pdf` |
| `--border` / `--border-light` | `#1e3029` / `#2a4038` | garis, grid latar, scrollbar |
| Font body | `'Plus Jakarta Sans', sans-serif` (300–900) | semua teks |
| Font display | `'Space Grotesk', monospace` (400–700) | brand, `.big-number` (angka rupiah/%) |
| Radius | kartu 16 · modal/auth-box 20 · input/tombol 10–12 · badge 6 · btn kecil 8 · ikon-kotak 9–12 | |
| Ukuran teks | label `.form-label` .8rem uppercase ls .05em · th .75rem uppercase ls .06em · td .875rem · badge .7rem · kartu-judul .95rem/700 · stat angka 1.5rem | |
| Latar | `.bg-mesh` (3 radial-gradient hijau) + `.bg-grid` (60 px, opacity .15, mask radial) fixed di belakang `.content-wrap` | setiap halaman |
| Animasi | `authPop`, `modalPop`, `fadeIn`, `toastIn/Out`, `panelIn`, `pulseRing`, `lockShake`, `particleFloat`; semua dimatikan pada `prefers-reduced-motion` | |
| Palet grafik (urut) | `#00e68a #00b4d8 #ffb347 #ff5c5c #a78bfa #f472b6 #34d399 #fbbf24 #60a5fa #fb923c #f87171 #4ade80 #38bdf8 #c084fc #facc15`; bar realisasi `rgba(255,92,92,.x)` border `#ff5c5c`, bar pagu `rgba(0,230,138,.x)` border `#00e68a`; teks/grid `--fg-muted`/`--border` | Chart.js |
| Badge sumber dana | `.sd-apbd .sd-apbn .sd-dt .sd-ban .sd-blud .sd-tpp .sd-dau .sd-dak .sd-dbhp .sd-lainnya` (warna sesuai prototipe, mis. `.sd-dak #60a5fa`, `.sd-dbhp #f87171`, `.sd-lainnya #94a3b8`; disimpan di `sumber_dana.css_class`) | |

## Komponen kanonik (kelas prototipe → komponen Blade di `resources/views/components/`)
| Komponen | Kelas prototipe | Catatan |
|---|---|---|
| `<x-layout.app title>` | header sticky `rgba(10,15,13,.85)` + `backdrop-filter: blur(16px)`, brand ikon `fa-building-columns` + "SIPAGAR" + sub "Sistem Informasi Pagu Anggaran & Realisasi"; kanan: `.pulse-dot`, teks instansi (`.hide-mobile`), `<x-user-badge>`, `.btn-pw-settings` (Sandi), `.btn-logout` (Keluar); `<main>` max-width 1320, padding 28/24/60; footer prototipe (`SIPAGAR v2.0 — BKPSDM … · tahun`) | semua halaman ber-auth; stat-card angka memakai `rupiah_singkat()` (identik prototipe) |
| `<x-layout.auth>` | `.auth-screen` + partikel (30 span) + `.auth-brand` + `.auth-box` + `.inputBox` (ikon kiri, toggle mata) + `.btn-login`; `.auth-error`; `.auth-info` versi | login saja (tanpa signup/reset) |
| `<x-tab-nav>` | `<nav role="tablist">` flex gap 8, scroll-x; `.tab-btn` + ikon FA; `.active` | item = menu di bawah; v2 memakai link route (bukan panel JS) |
| `<x-card title icon>` | `.card` (+ judul .95rem/700 ikon aksen) | pembungkus semua konten |
| `<x-stat-card color icon label value sub>` | `.card.stat-card.{green|red|yellow|teal}`; kotak ikon 34 px; `.big-number` 1.5rem; sub-teks .75rem; opsional `<x-progress>` | dashboard, header laporan |
| `<x-data-table>` | `.data-table` di dalam `overflow-x:auto`; th uppercase; hover baris `--accent-dim`; tfoot 700 | semua daftar; kolom aksi `text-align:center` |
| `<x-badge color>` | `.badge.badge-{green|yellow|red}` | status docs/04 |
| `<x-badge-sumber :sumber>` | `.badge.sd-*` font .65rem | sumber dana |
| `<x-form-input>` / `<x-form-select>` / `<x-form-textarea>` | `.form-label` + `.form-input` (select: chevron SVG kustom); error → `.input-error` + `.pw-error.show` | |
| `<x-btn variant>` | `.btn-primary` (aksen, teks `--bg`, hover glow, translateY -1) · `.btn-secondary` (outline) · `.btn-danger` · `.btn-edit` · `.btn-pw-settings` · `.btn-logout` | ikon FA di kiri, gap 8 |
| `<x-modal id title icon variant pesan>` | `.modal-overlay` blur 6 px + `.modal-box` 480 px; `.modal-icon-lock.{danger|warning}` 56 px bulat; variant `accent` utk ubah sandi | dibuka via `$dispatch('buka-modal', id)`; Escape/klik overlay menutup |
| `<x-modal-konfirmasi-sandi id title pesan variant tombol>` | modal + `.inputBox` sandi + `.pw-error` "Kata sandi salah! Percobaan x dari 3" + `lockShake` ikon; 3 gagal → tutup | edit/hapus/kunci; dibuka via `$dispatch('buka-konfirmasi', {id, aksi: form})`; Alpine `konfirmasiSandi()` memanggil `POST /konfirmasi-sandi`, sukses → `aksi.submit()` |
| `<x-toast>` | `.toast-container` kanan-atas; `.toast-{success|error|info}` ikon `fa-check-circle` / `fa-circle-xmark` / `fa-circle-info`; hilang 3 s | flash session `sukses` / `gagal` / `info` → toast (sudah dipasang di `x-layout.app` & `x-layout.auth`) |
| `<x-empty-state icon text>` | `.empty-state` ikon 3rem opacity .3, "Belum ada data." | state kosong |
| `<x-progress value color>` | `.progress-track` 8 px + `.progress-fill` (lebar inline dinamis — satu-satunya inline style yang diizinkan) | serapan |
| `<x-user-badge>` | `.user-badge` + `.avatar` huruf pertama nama | header |
| Form + daftar | grid `400px 1fr` (`.form-grid`, form `position:sticky; top:90px`), daftar di kanan | pola layar tambah/edit prototipe |

## Inventaris layar & navigasi
Tab-nav (urut kiri→kanan; tampil sesuai peran): **Dashboard** `fa-chart-pie` · **Anggaran** `fa-folder-open` · **Target** `fa-bullseye` · **Realisasi** `fa-receipt` · **Laporan** `fa-file-lines` · **Master** `fa-database` (Admin) · **Pengguna** `fa-users` (Admin) · **Audit** `fa-clock-rotate-left` (Admin). Sub-menu (Realisasi, Laporan, Master) = baris tab-btn kedua di bawah nav utama, gaya sama.

| Layar | Route | Peran | Pola |
|---|---|---|---|
| Login | `GET/POST /login`, `POST /logout` | semua | `x-layout.auth` |
| Dashboard | `/dashboard?tahun=&bidang=` | semua | filter tahun (default aktif; opsi **Semua** seperti prototipe) & bidang (Operator default bidangnya) → 4 stat-card → chart-grid 2 kolom (bar top-10, doughnut per sumber dana) → card tabel ringkasan; kosong → `x-empty-state` |
| Anggaran | `/anggaran?tahun=` | semua (tulis: admin) | pohon Program › Kegiatan › Sub Kegiatan sebagai tabel berindentasi; form tambah/edit di modal (`x-modal`) |
| Target | `/target/{subKegiatan}` | admin, operator (bidang) | pilih sub kegiatan (select) → tabel 4 baris TW × (keu Rp, fisik %) editable → simpan |
| Realisasi › Keuangan | `/realisasi/keuangan?sub_kegiatan=&edit=` | semua baca; tulis admin, operator (bidang) | pola form(400) + daftar; select sub kegiatan menampilkan "Sisa: Rp …" aksen/danger seperti prototipe |
| Realisasi › Fisik | `/realisasi/fisik/{subKegiatan}` | semua baca; tulis admin, operator (bidang) | 4 stat-card TW (fisik s.d. bulan 3n, deviasi vs target) + grid 12 bulan input % (kosong = bulan dihapus) |
| Laporan › Monev / Rekap / Buku / Tren | `/laporan/{monev|rekap|buku-realisasi|tren}` (+`?export=xlsx|pdf`, filter `tahun, triwulan, bidang, sumber_dana, sub_kegiatan, dari, sampai, halaman`) | semua | card filter (`laporan/_filter`, baris flex seperti filter "Sisa Anggaran" prototipe, auto-submit) → tombol `btn-secondary` Unduh Excel/PDF → tabel/grafik; Rekap & Buku memakai filter **TW + rentang tanggal** (TW = preset 1 Jan–akhir TW; tanggal eksplisit menang; nama berkas TWn atau `<dari>_<sampai>`); Rekap = 4 stat-card + 2 tabel; Tren = grafik garis (realisasi, target putus-putus, tahun lalu) + tabel 12 bulan; Buku paginasi 50 |
| Master › Bidang / Sumber Dana / Tahun / Pengaturan | `/master/{bidang|sumber-dana|tahun-anggaran|pengaturan}` | admin | form + daftar; Tahun: tombol Kunci (`btn-danger`) / Buka (`btn-edit`) → `x-modal-konfirmasi-sandi` id `kunci-tahun`; buka → draft |
| Pengguna | `/pengguna` | admin | form + daftar; aksi aktif/nonaktif, reset sandi (modal sandi) |
| Audit Log | `/audit-log?user=&aksi=&model=&dari=&sampai=` | admin | card filter + tabel (waktu, pengguna, aksi badge, objek, perubahan k: lama → baru, IP), paginasi 50 |
| Profil / Sandi | modal `ubah-password` dari header (`POST /profil/sandi`) | semua | modal prototipe (sandi lama, baru + strength bar, konfirmasi) |
| Lampiran | `GET /realisasi/lampiran/{id}` | semua | file |
| 403/404/500/423 | `resources/views/errors/*` | — | `x-layout.app` + `x-empty-state` ikon `fa-lock`/`fa-ghost`/`fa-triangle-exclamation` |

## UI states (wajib tiap layar data)
| State | Tampilan |
|---|---|
| Loading | server-rendered; tombol submit `disabled` + ikon `fa-spinner fa-spin` + "Memproses..." (pola tombol login prototipe) |
| Kosong | `x-empty-state`; tabel disembunyikan (bukan tabel kosong) |
| Error validasi | `.input-error` pada field + teks `.pw-error.show`; toast-error ringkas "Lengkapi semua field!"-style |
| Sukses | redirect + toast-success (teks: "Program baru berhasil ditambahkan", "Realisasi dicatat", "Berhasil dihapus" (info), "Kata sandi berhasil diubah!") |
| Terkunci | badge merah "TERKUNCI" di header tabel + semua tombol tulis disembunyikan |

## Perangkat & breakpoint
Desktop-first 1320 px. `≤768px`: `.stat-grid` 2 kolom, `.chart-grid`/`.form-grid` 1 kolom, padding sel 10/8, `.hide-mobile` hilang, header-right kolom. `≤480px`: `.stat-grid` 1 kolom. Tabel lebar → `overflow-x:auto` di pembungkus.

## PDF (satu-satunya pengecualian tema)
`layouts/pdf.blade.php` + `print.css`: kertas putih, font `DejaVu Sans` 9–10 pt, kop instansi (logo + nama + alamat dari `settings`), judul laporan, tabel garis tipis, subtotal tebal, blok tanda tangan kanan bawah (kota-tanggal, jabatan, nama, NIP). Landscape untuk Monev & Buku Realisasi.

## Bahasa & aksesibilitas
id-ID. `role="tablist"/"tab"`, `aria-selected`, `label for` dipertahankan seperti prototipe; `prefers-reduced-motion` dihormati. Audit kontras/keyboard penuh `[TERBUKA]` pasca-MVP.
