# 14 — Penanganan Error

## Klasifikasi & perlakuan
| Kelas | Contoh | Ke pengguna | Ke log (docs/15) |
|---|---|---|---|
| Validasi (422) | jumlah > sisa, kode duplikat | pesan per-field di bawah input (`.pw-error`/`.input-error`) + toast-error ringkas | tidak |
| Otorisasi (403) | operator lintas bidang | halaman 403 bertema prototipe "Anda tidak berhak" | `warning` + user_id + route |
| Tahun terkunci (423) | tulis pada tahun terkunci | toast-error "Tahun anggaran N telah dikunci" | `info` |
| Konfirmasi sandi gagal (403) | modal sandi | teks "Kata sandi salah! Percobaan x dari 3" (persis prototipe) | `warning` setelah percobaan ke-3 |
| Tidak ditemukan (404) | id salah | halaman 404 bertema | tidak |
| Throttle (429) | login | "Terlalu banyak percobaan, coba lagi dalam N detik" | `warning` + IP |
| Kesalahan server (500) | exception tak terduga | halaman 500 generik "Terjadi kesalahan, hubungi Admin" + kode referensi (`request id`) | `error` + stack trace |
| Ekspor gagal | DomPDF/Excel exception | toast-error "Gagal membuat berkas, coba lagi" | `error` |

## Aturan
- Pesan ke pengguna: Bahasa Indonesia, kalimat pendek, sebutkan angka yang relevan (contoh prototipe: `Melebihi sisa! Sisa: Rp 12.000.000`).
- Format toast: `success` / `error` / `info` — kelas & ikon persis prototipe (docs/26).
- `APP_DEBUG=false` di produksi; **jangan** tampilkan stack trace, query, atau path server.
- Exception domain custom di `app/Exceptions/`: `TahunTerkunciException` (→ 423), `MelebihiSisaPaguException` (→ 422). Render lewat `bootstrap/app.php` `withExceptions`.
- Validasi lintas-baris (target monoton, sisa pagu) di FormRequest `withValidator`, bukan di controller.
- Setiap 500 punya `request id` (header `X-Request-Id`, tercatat di log) untuk dirujuk pengguna.
