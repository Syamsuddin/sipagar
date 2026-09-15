# 13 — Strategi Tes

Pest (docs/09). Perintah → docs/11. Kriteria per fitur → docs/23. DB tes: MySQL `sipagar_test` (bukan SQLite — CHECK & ENUM harus diuji nyata), `RefreshDatabase`.

## Jenis & target
| Jenis | Lokasi | Cakupan minimum |
|---|---|---|
| Unit | `tests/Unit/SerapanCalculatorTest.php` | semua rumus & ambang docs/04, termasuk pagu 0, realisasi > pagu, fisik bulan kosong |
| Feature (HTTP) | `tests/Feature/<Modul>Test.php` | tiap alur docs/06 happy-path + tiap kondisi gagal yang disebut di sana |
| Otorisasi | di tiap Feature test | matriks docs/05: minimal 1 kasus ✓ dan 1 kasus ✗ per baris kapabilitas |
| Ekspor | `tests/Feature/LaporanExportTest.php` | xlsx: `Excel::fake()` + assert kelas & jumlah baris; pdf: response `application/pdf` & ukuran > 1 KB |
| UI smoke | di Feature test | halaman 200 + mengandung penanda komponen prototipe (`class="tab-btn`, `stat-card`, `data-table`) |

Coverage target 70 % (`--min=70`) pada `app/Services`, `app/Queries`, `app/Policies`.

## Wajib dites (alur kritikal)
1. Login gagal 5× → 429; user nonaktif → ditolak.
2. Operator bidang A menulis realisasi sub kegiatan bidang B → 403; membaca → 200.
3. Realisasi jumlah > sisa → 422 dengan pesan sisa; tepat = sisa → 201.
4. Target tidak monoton / TW4 ≠ pagu → 422.
5. Tahun terkunci: semua endpoint tulis → 423; laporan tetap 200.
6. Kunci tahun tanpa sandi benar → 403; 3× salah → modal ditutup (state Alpine, tes JS tidak wajib — cukup endpoint `konfirmasi-sandi` mengembalikan 403 & counter di sesi).
7. Audit log tercatat pada create/update/delete/lock dengan `old_values`/`new_values` benar & tanpa `password`.
8. Soft delete: data terhapus tidak masuk hitungan serapan.
9. Agregat laporan: Σ per bidang = Σ total; angka web = angka Export (bandingkan array dari Query yang sama).
10. Seeder `PrototipeSeeder` idempoten (jalan 2× → jumlah baris sama).

## Factory
Satu factory per model; `SubKegiatanFactory` menerima state `->untukBidang($bidang)` dan `->pagu(int)`; `TahunAnggaranFactory` state `->aktif()`, `->terkunci()`.
