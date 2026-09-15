# 21 — Aturan Keamanan

Wajib & defensif. Peran → docs/05. Skema → docs/07.

| Area | Aturan |
|---|---|
| Autentikasi | Guard `web` sesi DB; `Hash::make` bcrypt (rounds 12); login throttle `5/menit per username+IP` (429); `last_login_at` dicatat; user `is_active=0` → gagal login; saat dinonaktifkan/reset sandi, `PenggunaService` menghapus baris `sessions` milik user itu (prasyarat `SESSION_DRIVER=database`, docs/10) dan `EnsureRole` (`role` tanpa argumen pada grup auth) menolak 403 sesi nonaktif yang tersisa |
| Sandi | min 8 karakter (prototipe 6 → dinaikkan), wajib huruf & angka; ubah sandi butuh sandi lama; reset oleh Admin lewat modal konfirmasi sandi Admin |
| Konfirmasi sandi (modal) | endpoint `POST /konfirmasi-sandi` memverifikasi sandi user aktif, menyimpan `password_confirmed_at` di sesi selama **5 menit**; middleware `KonfirmasiSandi` melindungi update/delete anggaran (Program, Kegiatan, Sub Kegiatan) & realisasi keuangan, lock/unlock tahun, reset sandi user lain (target & realisasi fisik = upsert tanpa modal); 3 kegagalan berturut → 403 + jeda 60 detik (throttle) |
| Otorisasi | Policy untuk setiap model tulis; `authorize()` di semua aksi non-index; scope bidang dua lapis: scope lokal `forUser()` (`Models/Concerns/ScopedByBidang`) pada select tulis + Policy `milikBidangUser()` sebagai penegak server (bukan global scope — docs/05: semua peran melihat semua bidang); route grup `middleware('role:admin')` untuk master/pengguna/audit |
| Kunci tahun | `EnsureTahunTerbuka` pada semua route tulis anggaran/target/realisasi + cek ulang di Service (transaksi) |
| Validasi input | FormRequest semua endpoint; `jumlah`/`pagu` integer 1..9.999.999.999.999; tanggal dalam tahun anggaran; kode rekening regex; `username` `^[a-z0-9._-]{3,50}$` |
| CSRF | semua form POST/PUT/DELETE `@csrf`; fetch Alpine kirim header `X-CSRF-TOKEN` |
| Lampiran | disk `private` (`storage/app/private`), tidak pernah publik; validasi `mimes:pdf,jpg,jpeg,png` + `max:2048` + cek mime aktual; nama file = `{id}.{ext}` (bukan nama asli); unduh lewat `LampiranController@show` dengan Policy `view` → `response()->file()`; berkas lampiran transaksi yang di-soft-delete dibiarkan (dipulihkan bila restore), diganti/dihapus hanya saat lampiran baru diunggah |
| Output | Blade `{{ }}` default escape; `{!! !!}` dilarang kecuali konten PDF yang sudah disanitasi |
| Header | middleware `SecurityHeaders`: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: same-origin`, CSP `default-src 'self'; script-src 'self' 'unsafe-eval'; font-src 'self' fonts.gstatic.com data:; style-src 'self' 'unsafe-inline' fonts.googleapis.com; img-src 'self' data:` (`unsafe-eval` wajib untuk Alpine standar; alternatif build `@alpinejs/csp` ditolak karena melarang ekspresi inline) |
| Sesi | `SESSION_SECURE_COOKIE=true`, `SameSite=Lax`, lifetime 120 menit, regenerate saat login |
| Rahasia | hanya `.env`; `.env` tidak di git; `APP_DEBUG=false` produksi |
| Audit | trait `Auditable` mengecualikan `password`, `remember_token`; `audit_logs` append-only (tidak ada route update/delete) |
| Ekspor | file dibuat di memori/`storage/app/tmp` dan dihapus setelah dikirim; nama file tanpa input pengguna mentah |
| Kepatuhan | tidak ada regulasi khusus di luar praktik baik; data pegawai tidak disimpan (hanya nama PPTK) |
