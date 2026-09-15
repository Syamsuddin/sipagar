# INDEX — Peta Konteks
Untuk AI: baca ini sebelum task. Muat hanya dokumen yang ditunjuk. JANGAN muat semua.

## Tier 0 — selalu aktif
CLAUDE.md (inti). Jangan baca ulang sumbernya kecuali butuh detail.
Esensi 17_AGENT_WORKFLOW & 19_TASK_TEMPLATE sudah diringkas di CLAUDE.md — keduanya sengaja TIDAK ada di rute task. Muat penuhnya hanya saat butuh detail alur/format.

## Rute: jenis task → dokumen (path `docs/NN_NAME.md`)
| Jenis task | Muat | Catatan |
|---|---|---|
| Fitur baru (vertical slice) | 01,02,04,06,07,11,13,23,24 | cek 02 dulu; +05 bila sentuh peran/izin; +26 bila ber-UI; 19 utk format task |
| Halaman/komponen UI baru | 01,05,06,11,26,23,24 | ikuti token, komponen & states 26; jangan buat komponen tandingan; bandingkan dengan `SIPAGAR BKPSDM.html` |
| Laporan/ekspor baru atau ubah | 04,07,08,11,13,23,26 | satu Query utk web/xlsx/pdf |
| Ubah skema DB / migrasi | 04,07,11,13,22,24 | ⚠️ irreversibel; 22 wajib |
| Perbaikan bug | 11,13,14,16,18 | jangan lewat scope bug; 16 dulu |
| Endpoint/route baru | 05,06,07,08,11,13,21 | Policy + FormRequest wajib |
| Perubahan keamanan | 15,20,21,22 | ⚠️ gerbang manusia |
| Tambah peran/izin | 05,06,21 | — |
| Refactor arsitektur | 04,08,22 | jangan ubah keputusan sengaja |
| Observability/logging | 14,15,16 | — |
| Setup lingkungan | 09,10,11,12 | — |
| Seeder / data prototipe | 07,16,11 | `SEED.sql` = sumber data, bukan skema |
| Rilis | 11,15,22,24,25 | ⚠️ ikuti 25 berurutan |
| Strategi/scope | 00,01,02,03 | tanpa kode |

## Indeks lengkap
| Dok | Isi (pemilik fakta) |
|---|---|
| 00_EXECUTIVE_SUMMARY | masalah, solusi, metrik, pengguna |
| 01_PRD | daftar fitur F01–F16, user story, NFR |
| 02_SCOPE | in/out-of-scope, asumsi |
| 03_ROADMAP | slice S0–S5 |
| 04_DOMAIN_MODEL | glosarium, relasi konseptual, **rumus & ambang status** |
| 05_USER_ROLE | 3 peran, matriks akses |
| 06_BUSINESS_PROCESS | P1–P6 happy-path & gagal |
| 07_DATA_MODEL | **skema tabel** (sumber kebenaran data) |
| 08_ARCHITECTURE | lapisan, keputusan arsitektural |
| 09_STACK | teknologi & versi, terlarang |
| 10_DEV_ENV | setup lokal, `.env`, produksi ringkas |
| 11_COMMANDS | **semua perintah** + sinyal lulus |
| 12_PROJECT_STRUCTURE | pohon folder, penamaan, lokasi kode |
| 13_TESTING | jenis tes, alur wajib dites |
| 14_ERROR_HANDLING | klasifikasi error, pesan, exception custom |
| 15_OBSERVABILITY | logging, audit bisnis |
| 16_DEBUGGING_GUIDE | gejala→diagnosa, **jebakan proyek** |
| 17_AGENT_WORKFLOW | alur baku (stub) |
| 18_REPAIR_RULES | aturan perbaikan bug |
| 19_TASK_TEMPLATE | format task (stub) |
| 20_GUARDRAILS | 12 JANGAN |
| 21_SECURITY_RULES | auth, konfirmasi sandi, lampiran, header |
| 22_CHANGE_POLICY | git, operasi irreversibel, rollback |
| 23_ACCEPTANCE_CRITERIA | kriteria per fitur + blok verifikasi |
| 24_DEFINITION_OF_DONE | DoD universal |
| 25_RELEASE_CHECKLIST | rilis berurutan + rollback |
| 26_UI_DESIGN | **token, komponen, layar, states** — tampilan wajib identik prototipe |
| _MANIFEST.json | state kebutuhan terkonfirmasi, landmines, asumsi (bukan dokumen agen) |
| _SERAH_TERIMA.md / _SERAH_BUILD.json | dosir serah-terima build MVP (status, deviasi, asumsi, langkah rilis) — baca sebelum audit `review-vcbd` atau rilis |

## Aturan emas
1. Ragu? Muat paling sedikit dulu, eskalasi bila kurang.
2. Task irreversibel → wajib baca 22 + minta konfirmasi.
3. Konflik antar dokumen → berhenti, laporkan, minta keputusan.
4. Patokan selesai = 23 + 24, bukan kesempurnaan.
5. Dokumen ≠ kode aktual → KODE menang, hentikan & lapor selisih. `SCHEMA.sql`/`SEED.sql` di root adalah prototipe v1, bukan kode v2.
