# 08 — Arsitektur

Pola: **monolit modular Laravel**, server-rendered Blade, tanpa SPA. Stack & versi → docs/09. Struktur folder → docs/12.

## Lapisan & tanggung jawab
```
Browser (Blade + Alpine + Chart.js)
   │ HTTP (form POST / GET; JSON hanya utk data grafik)
routes/web.php ─► Middleware (auth, role, tahun.terbuka, throttle)
   ▼
Controller (tipis: terima FormRequest → panggil Service/Query → kembalikan view/redirect/download)
   ▼                          ▼                         ▼
Service (tulis, aturan)   Query (baca/agregasi laporan)   Export (xlsx/pdf dari Query)
   ▼                          ▼
Eloquent Model (+ Policy, Scope, trait Auditable) ─► MySQL
```
| Lapisan | Lokasi | Aturan |
|---|---|---|
| Routing | `routes/web.php` | nama route `modul.aksi`; grup per peran lewat middleware `role:` |
| Validasi | `app/Http/Requests/*Request.php` | semua input; aturan lintas-baris (target monoton, jumlah ≤ sisa) di `withValidator` |
| Otorisasi | `app/Policies/*Policy.php` | peran + scope bidang; dipanggil `authorize()` di controller |
| Logika tulis | `app/Services/*Service.php` | transaksi DB, audit, aturan docs/06 |
| Rumus | `app/Services/SerapanCalculator.php` | satu-satunya tempat rumus docs/04 |
| Baca laporan | `app/Queries/*Query.php` | mengembalikan koleksi DTO/array untuk view & export |
| Ekspor | `app/Exports/*Export.php` (xlsx, Laravel Excel) · `resources/views/pdf/*.blade.php` (DomPDF) | satu Query dipakai web, xlsx, pdf |
| Audit | `app/Models/Concerns/Auditable.php` | observer created/updated/deleted/restored → `audit_logs` |
| Kunci tahun | `app/Http/Middleware/EnsureTahunTerbuka.php` + cek ulang di Service | 423 bila terkunci |

## Keputusan arsitektural
| Keputusan | Alasan |
|---|---|
| Server-rendered Blade, bukan SPA | tampilan prototipe statis-sederhana; Alpine cukup untuk tab/modal/toast |
| Nilai turunan tidak disimpan | satu rumus di `SerapanCalculator`, tak ada drift antara tabel & laporan |
| Target/realisasi kumulatif | selaras format monev Permendagri; deviasi = pengurangan langsung |
| Scope bidang lewat Policy `milikBidangUser()` + scope lokal `forUser()` (trait `ScopedByBidang`) pada select tulis — bukan global scope, karena semua peran boleh MELIHAT semua bidang (docs/05) | penegakan tulis ada di Policy, tak bisa terlewat di controller mana pun |
| Ekspor dari Query yang sama dengan tampilan web | angka web = angka Excel = angka PDF |
| Modal konfirmasi sandi untuk edit/hapus/kunci | mempertahankan kebiasaan prototipe; friksi sebanding irreversibilitas (docs/22) |
| Tanpa queue di MVP | ekspor ≤ 10 detik sinkron; queue `database` disiapkan tapi tidak wajib |

Integrasi eksternal: **tidak ada**.
