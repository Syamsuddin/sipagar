# 17 — Alur Kerja Agen

Ikuti alur baku VCBD tanpa penyimpangan proyek:
1. Baca `CLAUDE.md` → `INDEX.md` → muat hanya dokumen rute task.
2. Cek docs/02: di luar scope → berhenti & tanya.
3. Tulis task memakai docs/19; konfirmasi scope bila ambigu.
4. Kerjakan sebagai **vertical slice** (migrasi → model → service → controller → view → tes), urut docs/03.
5. Jalankan tes docs/13 (perintah docs/11); UI dibandingkan dengan docs/26.
6. Task irreversibel (docs/22) → konfirmasi manusia sebelum eksekusi.
7. Selesai = docs/23 (fitur) + docs/24 (universal). Laporkan hasil apa adanya, termasuk tes gagal.
