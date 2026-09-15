# 09 — Stack

| Lapisan | Teknologi | Versi | Catatan |
|---|---|---|---|
| Bahasa | PHP | 8.4 | ext: pdo_mysql, mbstring, gd (DomPDF), zip (Excel), intl |
| Framework | Laravel | ^12.0 | starter kosong (`laravel new` tanpa kit) |
| DB | MySQL | 8.0 | |
| Template | Blade + Blade Components | bawaan | komponen di `resources/views/components` |
| CSS | CSS kanonik prototipe (`resources/css/app.css`) + Tailwind CSS | ^3.4 | Tailwind hanya utilitas layout/spacing; `preflight` **dimatikan** agar reset prototipe menang |
| JS | Alpine.js | ^3.14 | tab, modal, toast, dropdown |
| Grafik | Chart.js | ^4.4 | |
| Ikon | Font Awesome Free | 6.5 (`@fortawesome/fontawesome-free`) | via Vite, bukan CDN |
| Font | Plus Jakarta Sans, Space Grotesk | Google Fonts `@import` di `app.css` | `[ASUMSI]` server punya akses internet klien; fallback `sans-serif`/`monospace` |
| Bundler | Vite | bawaan Laravel 12 | |
| Excel | maatwebsite/excel | ^3.1 | |
| PDF | barryvdh/laravel-dompdf | ^3.0 | layout cetak terang |
| Tes | Pest | ^3.0 | + `pest-plugin-laravel` |
| Lint | Laravel Pint | bawaan | |
| Runtime | Node | 20 LTS | build aset saja |
| Server | Ubuntu 22.04/24.04, nginx, php8.4-fpm | — | docs/10 |

## Teknologi terlarang
| Dilarang | Alasan |
|---|---|
| daisyUI, Bootstrap, UI kit lain | tampilan wajib identik prototipe (docs/26); kit lain membawa gaya tandingan |
| jQuery | Alpine sudah mencukupi; prototipe pun vanilla |
| Livewire, Inertia, React/Vue | scope MVP tidak butuh; menambah kompleksitas build & tes |
| CDN runtime (`cdn.tailwindcss.com`, cdnjs) | prototipe memakainya; v2 semua aset lewat Vite agar deterministik & offline-safe |
| Warna/font/radius di luar token docs/26 | drift visual |
| Paket Composer/NPM baru tanpa alasan tertulis di PR | docs/20 |
