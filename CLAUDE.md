# CLAUDE.md
File ini selalu aktif. Untuk hal di luar ini → buka INDEX.md.

## Proyek
SIPAGAR v2 — monev anggaran BKPSDM Kab. HSS (Laravel, multi-user, Program→Kegiatan→Sub Kegiatan, target triwulan, realisasi keuangan & fisik, 4 laporan xlsx/pdf). Menggantikan prototipe `SIPAGAR BKPSDM.html`. Detail: docs/00. Scope: docs/02.

## Prinsip kerja agen (non-negotiable)
1. Friksi sebanding irreversibilitas.
2. Lapisan deterministik (validasi, constraint, tes) di bawah penalaran.
3. Human-in-the-loop untuk high-stakes (migrasi DB, hapus data, keamanan, rilis).
4. Jangan refactor keputusan yang disengaja diam-diam.
5. Hormati scope (docs/02). Out-of-scope → berhenti & tanya.

## Stack (sumber: docs/09)
Laravel 12 / PHP 8.4 / MySQL 8 / Blade + `resources/css/app.css` (CSS prototipe) + Tailwind utilitas + Alpine + Chart.js via Vite / Laravel Excel + DomPDF / Pest. Terlarang: daisyUI, Bootstrap, jQuery, Livewire/Inertia, CDN runtime, warna/font di luar docs/26.

## Struktur & konvensi (sumber: docs/12)
- Controller tipis → FormRequest → Service → Eloquent; laporan lewat `app/Queries`, ekspor lewat `app/Exports` + `views/pdf`.
- Rumus serapan/deviasi/status hanya di `app/Services/SerapanCalculator.php` (docs/04); nilai turunan tidak disimpan.
- Otorisasi = Policy + scope bidang (docs/05); tabel snake_case Indonesia, model PascalCase.

## UI (sumber: docs/26)
Tampilan WAJIB identik prototipe: tema gelap tunggal, token `--accent #00e68a`, font Plus Jakarta Sans + Space Grotesk. Rakit layar hanya dari komponen `x-*` di docs/26; edit/hapus/kunci lewat modal konfirmasi sandi.

## Perintah penting (sumber: docs/11)
`php artisan serve` + `npm run dev` · **`php artisan test`** · `php artisan migrate --seed` ⚠️ (`--force` di produksi) · `npm run build` · `vendor/bin/pint --test`

## Guardrail inti (penuh: docs/20, 21, 22)
JANGAN: hard-code rahasia · lewati CSRF/Policy/FormRequest/kunci-tahun · tulis rumus di luar SerapanCalculator · forceDelete/migrate:fresh di luar lokal · tambah paket/komponen/warna tanpa alasan · longgarkan tes · commit langsung ke main.

## Alur per-task (penuh: docs/17, 19)
Baca INDEX.md → muat dokumen relevan → konfirmasi scope → vertical slice (docs/03) → tes (docs/13) → DoD (docs/24).

## Definisi selesai (ringkas; penuh: docs/24)
Kriteria docs/23 terbukti + `php artisan test` hijau + pint PASS + migrasi reversible + UI sesuai docs/26 + dokumen pemilik & `_MANIFEST.json` diperbarui.

## Untuk apa pun di luar ini → INDEX.md
