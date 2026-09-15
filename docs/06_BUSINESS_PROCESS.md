# 06 — Proses Bisnis

Pemilik alur. Peran → docs/05; data → docs/07; rumus → docs/04; pesan error → docs/14.

## P1 — Penyusunan anggaran tahun N (Admin)
1. Master › Tahun Anggaran › tambah `N` (status `draft`).
2. Anggaran › pilih tahun N › tambah Program → Kegiatan → Sub Kegiatan (kode, nama, pagu, bidang, sumber dana, PPTK).
3. Ubah status → `aktif`. Hanya satu tahun `aktif` pada satu waktu (tahun aktif = default semua filter).

Gagal: kode duplikat pada tingkat & tahun sama → 422; pagu ≤ 0 → 422; tahun `terkunci` → 423; edit/hapus Sub Kegiatan yang sudah punya realisasi → boleh edit nama/PPTK, **pagu tidak boleh < realisasi**, hapus ditolak 422 selama ada realisasi; hapus Program/Kegiatan yang masih punya anak → 422 (tidak ada cascade).

## P2 — Penetapan target triwulan (Operator bidang / Admin)
1. Target › pilih Sub Kegiatan (Operator hanya melihat bidangnya).
2. Isi 4 baris: TW1–TW4 keuangan (Rp) & fisik (%) kumulatif. Simpan sekaligus (upsert 4 baris).

Gagal: tidak monoton naik → 422; TW4 keuangan ≠ pagu / fisik ≠ 100 → 422; bidang lain → 403; tahun terkunci → 423.

## P3 — Pencatatan realisasi (Operator bidang / Admin)
Keuangan: Realisasi › Keuangan › pilih Sub Kegiatan → form (tanggal, jumlah, uraian, no. SP2D, lampiran) → simpan → toast sukses; sisa & serapan diperbarui.
Fisik: Realisasi › Fisik › pilih Sub Kegiatan → grid 12 bulan → isi persen kumulatif → simpan (upsert; bulan yang dikosongkan pada form = baris bulan itu dihapus).

Gagal: jumlah > sisa → 422 "Melebihi sisa! Sisa: Rp …"; tanggal di luar tahun anggaran → 422; fisik menurun dari bulan terisi sebelumnya atau > 100 → 422; lampiran bukan pdf/jpg/png atau > 2 MB → 422; bidang lain → 403; tahun terkunci → 423.
Edit/hapus transaksi: modal konfirmasi sandi (3 percobaan → modal ditutup); hapus = soft delete + audit.

## P4 — Pemantauan & pelaporan (semua peran)
1. Dashboard: filter tahun (default aktif) & bidang → kartu, grafik, tabel ringkasan status.
2. Laporan › pilih jenis › filter (tahun, TW, bidang, sumber dana, sub kegiatan; **Rekap & Buku** juga rentang tanggal `dari`–`sampai`: TW = preset 1 Jan–akhir TW, tanggal eksplisit menang — keputusan pemilik 2026-09-15) → tabel/grafik. Monev: deviasi keuangan ditampilkan dalam poin % (Rp tersedia di SerapanCalculator). Tren: target kumulatif diinterpolasi linear antar titik TW (bulan 0 = 0, bulan 3n = target TWn); kurva tahun lalu = data tahun N−1 bila ada.
3. Unduh Excel / PDF → file `sipagar_<jenis>_<tahun>_<periode>.xlsx|pdf`.

Gagal: filter kosong → default tahun aktif & TW berjalan; data kosong → `.empty-state`, ekspor tetap berisi header + baris "Tidak ada data".

## P5 — Penutupan tahun (Admin)
1. Master › Tahun Anggaran › **Kunci** → modal konfirmasi sandi → status `terkunci`, `locked_at/by` dicatat, audit log.
2. Semua endpoint tulis pada data tahun itu → 423 dengan pesan "Tahun anggaran N telah dikunci".
3. **Buka kunci** hanya Admin, modal sandi, dicatat audit (`unlock_tahun`) → status kembali **`draft`** (`locked_at/by` dikosongkan; data tahun itu dapat ditulis lagi untuk koreksi — docs/04). Aktifkan ulang lewat P1 langkah 3 (aturan satu tahun aktif).

## P6 — Manajemen pengguna (Admin)
Tambah pengguna (nama, username, sandi awal, peran, bidang bila operator) → aktif/nonaktif → reset sandi (modal konfirmasi). Menghapus pengguna = nonaktifkan (tidak ada hard delete; audit log merujuk `user_id`).
