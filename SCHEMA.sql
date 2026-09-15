-- =====================================================================
-- SIPAGAR — Sistem Pemantauan Anggaran BKPSDM
-- SCHEMA.sql  (MySQL 8 / MariaDB 10.4+)
--
-- Diturunkan dari struktur data localStorage di:
--   - SIPAGAR BKPSDM.html   (key: sipagar_users, sipagar_session,
--                            sipagar_programs_v5, sipagar_realisasi_v5)
--   - Untitled-1ke 2.html   (key: sipagar_users, sipagar_session,
--                            sipagar_programs_v4, sipagar_realisasi_v4)
--
-- Pemetaan objek JS -> tabel:
--   users[]     {username, nama, passwordHash, createdAt}            -> users
--   session     {username, nama, loginAt}                            -> sessions
--   SUMBER_DANA [{value, label, cssClass}]                            -> sumber_dana
--   programs[]  {id, nama, pagu, tahun, sumber, createdAt}           -> programs
--   realisasi[] {id, programId, tanggal, jumlah, ket, createdAt}     -> realisasi
--
-- Kolom turunan (sisa, serapan, status) TIDAK disimpan; dihitung lewat
-- view v_program_ringkasan agar selalu konsisten dengan rumus di HTML.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW  IF EXISTS v_dashboard_stat;
DROP VIEW  IF EXISTS v_program_ringkasan;
DROP TABLE IF EXISTS realisasi;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS sumber_dana;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. USERS  (localStorage: sipagar_users)
--    username disimpan lower-case oleh aplikasi (findUser membandingkan
--    case-insensitive), password = SHA-256(pw + '_sipagar_salt_bkpsdm_hss_2025')
--    dalam hex 64 karakter. Fallback hash non-crypto berbentuk 'fb_<hex>_sgr'.
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  username      VARCHAR(50)   NOT NULL,                 -- min 3 karakter, unik (case-insensitive)
  nama          VARCHAR(100)  NOT NULL,                 -- nama lengkap (tampil di header & avatar)
  password_hash VARCHAR(128)  NOT NULL,                 -- SHA-256 hex (64) / fallback 'fb_..._sgr'
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  CONSTRAINT ck_users_username_len CHECK (CHAR_LENGTH(username) >= 3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. SESSIONS  (localStorage: sipagar_session)
--    Aplikasi hanya menyimpan satu sesi aktif per browser; di server
--    sesi diberi token acak dan dikaitkan ke user.
-- ---------------------------------------------------------------------
CREATE TABLE sessions (
  id          CHAR(64)      NOT NULL,                   -- token sesi (random hex)
  user_id     INT UNSIGNED  NOT NULL,
  login_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- loginAt
  last_seen   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  expires_at  DATETIME      NULL,
  user_agent  VARCHAR(255)  NULL,
  ip_address  VARCHAR(45)   NULL,
  PRIMARY KEY (id),
  KEY idx_sessions_user (user_id),
  CONSTRAINT fk_sessions_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. SUMBER_DANA  (konstanta SUMBER_DANA di kedua HTML)
--    kode  = value   (disimpan di programs.sumber)
--    label = label   (teks dropdown/badge)
--    css_class = cssClass (warna badge)
-- ---------------------------------------------------------------------
CREATE TABLE sumber_dana (
  kode       VARCHAR(30)  NOT NULL,
  label      VARCHAR(100) NOT NULL,
  css_class  VARCHAR(30)  NOT NULL DEFAULT 'sd-lainnya',
  urutan     TINYINT UNSIGNED NOT NULL DEFAULT 0,       -- urutan tampil di dropdown
  aktif      TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. PROGRAMS  (localStorage: sipagar_programs_v4 / _v5)
--    id JS = base36 timestamp + random (gid()/generateId()), disimpan
--    sebagai VARCHAR agar data lama bisa diimpor apa adanya.
--    tahun : TAHUN_MULAI..TAHUN_SELESAI (2020–2034)
--    pagu  : rupiah bulat, harus > 0
-- ---------------------------------------------------------------------
CREATE TABLE programs (
  id          VARCHAR(32)     NOT NULL,                 -- gid() dari JS, atau UUID
  nama        VARCHAR(255)    NOT NULL,
  pagu        BIGINT UNSIGNED NOT NULL,                 -- rupiah bulat
  tahun       SMALLINT UNSIGNED NOT NULL,               -- tahun anggaran
  sumber      VARCHAR(30)     NOT NULL,                 -- FK -> sumber_dana.kode
  created_by  INT UNSIGNED    NULL,                     -- user pembuat (opsional)
  created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_programs_tahun  (tahun),
  KEY idx_programs_sumber (sumber),
  KEY idx_programs_tahun_sumber (tahun, sumber),        -- filter tab "Sisa Anggaran"
  CONSTRAINT fk_programs_sumber FOREIGN KEY (sumber)
    REFERENCES sumber_dana (kode) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_programs_user FOREIGN KEY (created_by)
    REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT ck_programs_pagu  CHECK (pagu > 0),
  CONSTRAINT ck_programs_tahun CHECK (tahun BETWEEN 2020 AND 2034)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. REALISASI  (localStorage: sipagar_realisasi_v4 / _v5)
--    Hapus program -> realisasi terkait ikut terhapus (sesuai hapusProgram()).
--    Validasi "jumlah <= sisa pagu" dilakukan di aplikasi, bukan DB.
-- ---------------------------------------------------------------------
CREATE TABLE realisasi (
  id          VARCHAR(32)     NOT NULL,
  program_id  VARCHAR(32)     NOT NULL,                 -- programId
  tanggal     DATE            NOT NULL,
  jumlah      BIGINT UNSIGNED NOT NULL,                 -- rupiah bulat, harus > 0
  ket         VARCHAR(500)    NOT NULL,                 -- keterangan (wajib diisi di form)
  created_by  INT UNSIGNED    NULL,
  created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_realisasi_program (program_id),
  KEY idx_realisasi_tanggal (tanggal),
  CONSTRAINT fk_realisasi_program FOREIGN KEY (program_id)
    REFERENCES programs (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_realisasi_user FOREIGN KEY (created_by)
    REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT ck_realisasi_jumlah CHECK (jumlah > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. VIEW: v_program_ringkasan
--    Mereplikasi kolom tab "Ringkasan" & "Sisa Anggaran":
--      realisasi   = SUM(realisasi.jumlah)
--      sisa        = pagu - realisasi
--      pct_serapan = ROUND(realisasi / pagu * 100)
--      pct_sisa    = ROUND(sisa / pagu * 100)
--      status_ringkasan : >=90 Hampir Habis | >=60 Sedang | else Aman
--      status_sisa      : >=100 Habis | >=90 Kritis | >=60 Sedang | else Aman
-- ---------------------------------------------------------------------
CREATE VIEW v_program_ringkasan AS
SELECT
  p.id,
  p.nama,
  p.tahun,
  p.sumber,
  sd.label                                             AS sumber_label,
  sd.css_class                                         AS sumber_css,
  p.pagu,
  COALESCE(r.total_realisasi, 0)                       AS realisasi,
  COALESCE(r.jumlah_transaksi, 0)                      AS jumlah_transaksi,
  p.pagu - COALESCE(r.total_realisasi, 0)              AS sisa,
  ROUND(COALESCE(r.total_realisasi, 0) / p.pagu * 100) AS pct_serapan,
  ROUND((p.pagu - COALESCE(r.total_realisasi, 0)) / p.pagu * 100) AS pct_sisa,
  CASE
    WHEN ROUND(COALESCE(r.total_realisasi, 0) / p.pagu * 100) >= 90 THEN 'Hampir Habis'
    WHEN ROUND(COALESCE(r.total_realisasi, 0) / p.pagu * 100) >= 60 THEN 'Sedang'
    ELSE 'Aman'
  END                                                  AS status_ringkasan,
  CASE
    WHEN ROUND(COALESCE(r.total_realisasi, 0) / p.pagu * 100) >= 100 THEN 'Habis'
    WHEN ROUND(COALESCE(r.total_realisasi, 0) / p.pagu * 100) >= 90  THEN 'Kritis'
    WHEN ROUND(COALESCE(r.total_realisasi, 0) / p.pagu * 100) >= 60  THEN 'Sedang'
    ELSE 'Aman'
  END                                                  AS status_sisa,
  p.created_at,
  p.updated_at
FROM programs p
LEFT JOIN sumber_dana sd ON sd.kode = p.sumber
LEFT JOIN (
  SELECT program_id, SUM(jumlah) AS total_realisasi, COUNT(*) AS jumlah_transaksi
  FROM realisasi
  GROUP BY program_id
) r ON r.program_id = p.id;

-- ---------------------------------------------------------------------
-- 7. VIEW: v_dashboard_stat  (kartu statistik di tab Dashboard)
-- ---------------------------------------------------------------------
CREATE VIEW v_dashboard_stat AS
SELECT
  COALESCE(SUM(p.pagu), 0)                                        AS total_pagu,
  COALESCE((SELECT SUM(jumlah) FROM realisasi), 0)                AS total_realisasi,
  COALESCE(SUM(p.pagu), 0) - COALESCE((SELECT SUM(jumlah) FROM realisasi), 0) AS total_sisa,
  CASE WHEN COALESCE(SUM(p.pagu), 0) > 0
       THEN ROUND(COALESCE((SELECT SUM(jumlah) FROM realisasi), 0) / SUM(p.pagu) * 100)
       ELSE 0 END                                                 AS pct_serapan,
  CASE WHEN COALESCE(SUM(p.pagu), 0) > 0
       THEN ROUND((SUM(p.pagu) - COALESCE((SELECT SUM(jumlah) FROM realisasi), 0)) / SUM(p.pagu) * 100)
       ELSE 0 END                                                 AS pct_sisa,
  COUNT(p.id)                                                     AS jumlah_program,
  (SELECT COUNT(*) FROM realisasi)                                AS jumlah_realisasi
FROM programs p;

-- ---------------------------------------------------------------------
-- 8. SEED: SUMBER_DANA (sesuai konstanta di kedua HTML)
-- ---------------------------------------------------------------------
INSERT INTO sumber_dana (kode, label, css_class, urutan) VALUES
  ('APBD I',          'APBD I (Provinsi)',                 'sd-apbd',    1),
  ('APBD II',         'APBD II (Kabupaten/Kota)',          'sd-apbd',    2),
  ('APBN',            'APBN',                              'sd-apbn',    3),
  ('Dana Transfer',   'Dana Transfer Umum',                'sd-dt',      4),
  ('DAU',             'Dana Alokasi Umum (DAU)',           'sd-dau',     5),
  ('DAK',             'Dana Alokasi Khusus (DAK)',         'sd-dak',     6),
  ('DBHPT',           'DBH Pajak & Retribusi',             'sd-dbhp',    7),
  ('DBHCHT',          'DBH Cukai Hasil Tembakau',          'sd-dbhp',    8),
  ('DBHSDA',          'DBH Sumber Daya Alam',              'sd-dbhp',    9),
  ('BAN',             'Bantuan Keuangan (BAN)',            'sd-ban',    10),
  ('BLUD',            'Badan Layanan Umum Daerah (BLUD)',  'sd-blud',   11),
  ('TPP',             'Tunjangan Kinerja (TPP)',           'sd-tpp',    12),
  ('Hibah',           'Hibah Pemerintah Pusat',            'sd-ban',    13),
  ('Pinjaman Daerah', 'Pinjaman Daerah',                   'sd-lainnya',14),
  ('Lainnya',         'Lainnya',                           'sd-lainnya',15);

-- ---------------------------------------------------------------------
-- 9. SEED: AKUN DEFAULT  (seedDefaultUser() di Untitled-1ke 2.html)
--    username: admin, password: admin123
--    hash = SHA2(CONCAT('admin123', '_sipagar_salt_bkpsdm_hss_2025'), 256)
--    -> identik dengan hashPw() di browser, sehingga login lintas
--       localStorage <-> DB tetap cocok.
-- ---------------------------------------------------------------------
INSERT INTO users (username, nama, password_hash) VALUES
  ('admin', 'Administrator', SHA2(CONCAT('admin123', '_sipagar_salt_bkpsdm_hss_2025'), 256));
