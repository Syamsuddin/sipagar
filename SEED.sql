-- =====================================================================
-- SIPAGAR — SEED.sql
-- Data contoh yang ditarik dari fungsi seedData() di kedua HTML.
-- Jalankan SETELAH SCHEMA.sql (sumber_dana & user admin sudah di-seed di sana).
--
-- Sumber utama : SIPAGAR BKPSDM.html  (sipagar_programs_v5 / sipagar_realisasi_v5)
-- Pembanding   : Untitled-1ke 2.html  (…_v4) — program & realisasi identik
--                (id, tanggal, jumlah), hanya teks keterangan yang lebih panjang.
--                Varian v4 dicantumkan sebagai komentar di tiap baris realisasi.
--
-- createdAt di JS = new Date().toISOString() saat seed pertama kali dijalankan,
-- jadi tidak ada nilai tetap; di sini dibiarkan mengikuti DEFAULT CURRENT_TIMESTAMP.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM realisasi WHERE id IN ('r1', 'r2', 'r3', 'r4', 'r5', 'r6', 'r7', 'r8', 'r9', 'r10', 'r11', 'r12', 'r13', 'r14', 'r15', 'r16');
DELETE FROM programs  WHERE id IN ('p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9', 'p10');
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- PROGRAMS (10 baris) — created_by = admin
-- ---------------------------------------------------------------------
INSERT INTO programs (id, nama, pagu, tahun, sumber, created_by) VALUES
  ('p1', 'Pelatihan Dasar CPNS Gol. III', 285000000, 2025, 'APBD II', (SELECT id FROM users WHERE username='admin')),
  ('p2', 'Diklat Kepemimpinan Pengawas', 195000000, 2025, 'APBD II', (SELECT id FROM users WHERE username='admin')),
  ('p3', 'Pelatihan Teknis Fungsional', 150000000, 2025, 'DAK', (SELECT id FROM users WHERE username='admin')),
  ('p4', 'Penyusunan Analisis Jabatan', 95000000, 2025, 'DAU', (SELECT id FROM users WHERE username='admin')),
  ('p5', 'Sistem Informasi Kepegawaian (SIMPEG)', 320000000, 2025, 'Dana Transfer', (SELECT id FROM users WHERE username='admin')),
  ('p6', 'Mutasi dan Promosi Jabatan', 75000000, 2025, 'APBD II', (SELECT id FROM users WHERE username='admin')),
  ('p7', 'Pengelolaan Database Kepegawaian', 120000000, 2024, 'APBD II', (SELECT id FROM users WHERE username='admin')),
  ('p8', 'Diklat PIM IV Tingkat Dasar', 175000000, 2024, 'DAK', (SELECT id FROM users WHERE username='admin')),
  ('p9', 'Reviu Jabatan Fungsional', 85000000, 2026, 'APBN', (SELECT id FROM users WHERE username='admin')),
  ('p10', 'Pembayaran TPP ASN', 2400000000, 2025, 'TPP', (SELECT id FROM users WHERE username='admin'));

-- ---------------------------------------------------------------------
-- REALISASI (16 baris) — created_by = admin
-- ---------------------------------------------------------------------
INSERT INTO realisasi (id, program_id, tanggal, jumlah, ket, created_by) VALUES
  ('r1', 'p1', '2025-01-15', 45000000, 'Akomodasi peserta batch 1', (SELECT id FROM users WHERE username='admin')),  -- v4: Pembayaran akomodasi peserta batch 1
  ('r2', 'p1', '2025-02-20', 62000000, 'Honor narasumber batch 2', (SELECT id FROM users WHERE username='admin')),  -- v4: Honor narasumber dan konsumsi batch 2
  ('r3', 'p1', '2025-03-10', 38000000, 'Pengadaan materi dan ATK', (SELECT id FROM users WHERE username='admin')),  -- v4: Pengadaan materi pelatihan dan ATK
  ('r4', 'p2', '2025-02-05', 55000000, 'Penyelenggaraan diklat', (SELECT id FROM users WHERE username='admin')),  -- v4: Biaya penyelenggaraan diklat kepemimpinan
  ('r5', 'p2', '2025-04-12', 48000000, 'Akomodasi peserta', (SELECT id FROM users WHERE username='admin')),  -- v4: Akomodasi dan transport peserta
  ('r6', 'p3', '2025-01-25', 35000000, 'Pelatihan teknis perencanaan', (SELECT id FROM users WHERE username='admin')),
  ('r7', 'p3', '2025-03-18', 42000000, 'Pelatihan teknis keuangan', (SELECT id FROM users WHERE username='admin')),  -- v4: Pelatihan teknis keuangan daerah
  ('r8', 'p4', '2025-02-28', 28000000, 'Konsultan analisis jabatan', (SELECT id FROM users WHERE username='admin')),  -- v4: Konsultan penyusunan analisis jabatan
  ('r9', 'p5', '2025-01-10', 120000000, 'Pengadaan server', (SELECT id FROM users WHERE username='admin')),  -- v4: Pengadaan server dan perangkat jaringan
  ('r10', 'p5', '2025-03-22', 85000000, 'Pengembangan SIMPEG fase 1', (SELECT id FROM users WHERE username='admin')),  -- v4: Pengembangan aplikasi SIMPEG fase 1
  ('r11', 'p6', '2025-04-01', 15000000, 'Naskah keputusan mutasi', (SELECT id FROM users WHERE username='admin')),  -- v4: Pembuatan naskah keputusan mutasi
  ('r12', 'p10', '2025-01-31', 200000000, 'TPP Januari 2025', (SELECT id FROM users WHERE username='admin')),  -- v4: Pembayaran TPP Januari 2025
  ('r13', 'p10', '2025-02-28', 200000000, 'TPP Februari 2025', (SELECT id FROM users WHERE username='admin')),  -- v4: Pembayaran TPP Februari 2025
  ('r14', 'p10', '2025-03-31', 200000000, 'TPP Maret 2025', (SELECT id FROM users WHERE username='admin')),  -- v4: Pembayaran TPP Maret 2025
  ('r15', 'p7', '2024-06-15', 65000000, 'Lisensi software', (SELECT id FROM users WHERE username='admin')),  -- v4: Pengadaan lisensi software database
  ('r16', 'p8', '2024-08-20', 92000000, 'Diklat PIM IV angkatan 1', (SELECT id FROM users WHERE username='admin'));  -- v4: Penyelenggaraan diklat PIM IV angkatan 1

-- ---------------------------------------------------------------------
-- VERIFIKASI (opsional): hasil yang diharapkan dari v_program_ringkasan
-- ---------------------------------------------------------------------
-- p1   pagu=  285000000  realisasi= 145000000  sisa=  140000000  serapan=51%
-- p2   pagu=  195000000  realisasi= 103000000  sisa=   92000000  serapan=53%
-- p3   pagu=  150000000  realisasi=  77000000  sisa=   73000000  serapan=51%
-- p4   pagu=   95000000  realisasi=  28000000  sisa=   67000000  serapan=29%
-- p5   pagu=  320000000  realisasi= 205000000  sisa=  115000000  serapan=64%
-- p6   pagu=   75000000  realisasi=  15000000  sisa=   60000000  serapan=20%
-- p7   pagu=  120000000  realisasi=  65000000  sisa=   55000000  serapan=54%
-- p8   pagu=  175000000  realisasi=  92000000  sisa=   83000000  serapan=53%
-- p9   pagu=   85000000  realisasi=         0  sisa=   85000000  serapan=0%
-- p10  pagu= 2400000000  realisasi= 600000000  sisa= 1800000000  serapan=25%
-- SELECT id, nama, pagu, realisasi, sisa, pct_serapan, status_ringkasan FROM v_program_ringkasan ORDER BY id;
