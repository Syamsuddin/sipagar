# 04 — Domain Model

Pemilik istilah domain, entitas konseptual, dan **rumus perhitungan**. Skema fisik → docs/07.

## Glosarium
| Istilah | Makna |
|---|---|
| Tahun Anggaran | Periode 1 Jan–31 Des; status `draft` (disusun/dikoreksi — struktur, target & realisasi **dapat ditulis**; bukan default filter), `aktif` (tahun berjalan — dapat ditulis, default semua filter, maks satu), `terkunci` (baca saja, 423). Server hanya memblokir `terkunci`; UI menawarkan sub kegiatan tahun aktif saja |
| Program / Kegiatan / Sub Kegiatan | Hierarki nomenklatur Kepmendagri; **pagu, target, realisasi hanya di Sub Kegiatan** |
| Kode rekening | Kode nomenklatur (`X.XX.XX` / `X.XX.XX.X.XX` / `X.XX.XX.X.XX.XXXX`), unik per tahun pada tingkatnya |
| Pagu | Alokasi anggaran Sub Kegiatan (rupiah bulat) |
| Bidang | Unit kerja penanggung jawab Sub Kegiatan; batas data Operator |
| Sumber Dana | Asal pendanaan (APBD II, DAK, DAU, TPP, …); 15 kode dari prototipe |
| PPTK | Pejabat Pelaksana Teknis Kegiatan — nama teks pada Sub Kegiatan |
| Target Triwulan | Rencana **kumulatif** s.d. akhir TW n: keuangan (Rp) dan fisik (%) |
| Realisasi Keuangan | Satu transaksi belanja: tanggal, jumlah, uraian, no. SP2D, lampiran |
| Realisasi Fisik | Capaian fisik **kumulatif** (%) pada akhir bulan m |
| Serapan | Persentase realisasi keuangan terhadap pagu |
| Sisa | Pagu − realisasi keuangan |
| Deviasi | Realisasi kumulatif − target kumulatif pada periode yang sama (keuangan dalam Rp & poin %, fisik dalam poin %) |
| Status serapan | Label Aman / Sedang / Kritis / Habis (lihat rumus) |
| Kunci tahun | Tindakan Admin yang memblokir semua tulis pada data tahun tersebut |

## Entitas & relasi konseptual
```
TahunAnggaran 1─n Program 1─n Kegiatan 1─n SubKegiatan
SubKegiatan n─1 Bidang ; n─1 SumberDana
SubKegiatan 1─4 TargetTriwulan ; 1─n RealisasiKeuangan ; 1─≤12 RealisasiFisik
User n─1 Bidang (operator wajib; admin/pimpinan null)
AuditLog n─1 User ; polymorphic ke entitas mana pun
Setting (key-value tunggal)
```

## Rumus (implementasi tunggal: `app/Services/SerapanCalculator.php`)
| Nilai | Rumus |
|---|---|
| realisasi(sk, s.d. tanggal T) | Σ jumlah RealisasiKeuangan dengan tanggal ≤ T (soft-deleted dikecualikan) |
| sisa | pagu − realisasi |
| serapan % | `pagu > 0 ? round(realisasi / pagu × 100) : 0` |
| sisa % | `round(sisa / pagu × 100)` |
| akhir TW n | TW1 = 31 Mar, TW2 = 30 Jun, TW3 = 30 Sep, TW4 = 31 Des |
| target keuangan % TW n | `round(target_keuangan_n / pagu × 100)` |
| deviasi keuangan TW n | realisasi(s.d. akhir TW n) − target_keuangan_n (Rp); dalam poin: serapan% − target% |
| fisik s.d. bulan m | persen RealisasiFisik bulan m; bila bulan m kosong → bulan terisi terakhir < m; tidak ada → 0 |
| deviasi fisik TW n | fisik(s.d. bulan 3n) − target_fisik_n |
| status serapan | serapan ≥ 100 → **Habis**; ≥ 90 → **Kritis**; ≥ 60 → **Sedang**; < 60 → **Aman** |
| warna status | Aman `badge-green`, Sedang `badge-yellow`, Kritis & Habis `badge-red` (docs/26) |
| agregat (bidang/sumber dana/total) | Σ pagu, Σ realisasi pada anggota; serapan agregat dihitung dari Σ, bukan rata-rata % |

Aturan konsistensi target: `target_keuangan_1 ≤ … ≤ target_keuangan_4 = pagu`; `target_fisik_1 ≤ … ≤ target_fisik_4 = 100`. Realisasi fisik bulan m ≥ bulan m−1 yang terisi.
