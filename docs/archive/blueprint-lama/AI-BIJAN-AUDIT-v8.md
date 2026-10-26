# AI BIJAN Master Blueprint — Laporan Audit & Cadangan (v7 → v8)

**Tarikh:** 19 Oktober 2026
**Sumber:** `AI-BIJAN-MASTER-BLUEPRINT.md` (v7, 9,079 baris, 424,866 bait)
**Hasil:** `AI-BIJAN-MASTER-BLUEPRINT-v8.md` (9,575 baris, 420,991 aksara)

---

## 1. Ringkasan

| Item | v7 | v8 |
|------|----|----|
| Bab | 69 tajuk (2 berganda) | 77 (tiada berganda) |
| Bahagian (PART) | 11 (1 pada level salah) | 12 (semua H1) |
| Jadual | 240 (4 terpecah, 1 cacat) | 262 (semua lurus) |
| Sel jadual rosak | 10 | 0 |
| Aksara kawalan dalam teks | 6 | 0 |
| Indeks struktur | Basi (tertinggal 15 bab) | Dijana semula, 77 bab |

Semua perubahan dijalankan oleh skrip `build_v8.py` dengan *assertion* pada setiap langkah, dan hasilnya disahkan oleh pemeriksaan bebas (10/10 lulus).

---

## 2. Kecacatan yang ditemui dan dibaiki

### 2.1 Kerosakan aksara kawalan — 10 sel jadual (paling serius)

Ini bukan salah taip. Satu pemprosesan teks terdahulu telah menukar huruf kepada jujukan escape, dan jujukan itu ditulis ke fail sebagai **aksara kawalan sebenar** yang menelan huruf asal:

| Aksara | Huruf yang hilang | Contoh dalam v7 | Betul |
|--------|-------------------|-----------------|-------|
| `\x1b` (ESC, `\e`) | `e` | `\x1bpf_records` | `epf_records` |
| `\x1b` | `e` | `\x1ba_forms` | `ea_forms` |
| `\x08` (`\b`) | `b` | `\x08ank_transfer_files` | `bank_transfer_files` |
| `\x08` | `b` | `\x08atch_inventory` | `batch_inventory` |
| `\x07` (`\a`) | `a` | `\x07bc_classifications` | `abc_classifications` |
| `\x0b` (`\v`) | `v` | `\x0btfr_reports` | `vtfr_reports` |

Empat lagi kes menggunakan `\r`, yang bertukar menjadi CR dan oleh itu kelihatan seperti hujung baris biasa — ia memecahkan baris jadual kepada dua:

| v7 | v8 |
|----|----|
| `\| ` + baris baru + `fqs` | `\| rfqs` |
| `\| ` + baris baru + `fq_responses` | `\| rfq_responses` |
| `\| ` + baris baru + `eturn_authorizations` | `\| return_authorizations` |
| `\| ` + baris baru + `eturn_to_vendor` | `\| return_to_vendor` |

Semuanya dalam jadual BAB 57 (Payroll) dan BAB 58 (SCM). Nama lajur pangkalan data yang rosak akan menyebabkan migrasi gagal atau lajur dicipta dengan nama salah — jadi ini bukan kosmetik.

**Amaran:** kerosakan jenis ini tidak kelihatan semasa membaca biasa. Jika fail ini pernah melalui alat penukaran (regex, `sed`, skrip Python lama), kemungkinan besar punca masih ada dan akan berulang.

### 2.2 Jadual 23.1 hilang lajur

Empat baris dalam "Prosedur Penarikan Consent" kehilangan sel **Tempoh**, dan baris terakhir juga kehilangan paip penutup. Nilai yang diisi dalam v8 (`< 5 minit`, `< 15 minit`, `< 15 minit`, `Segera selepas langkah 5`) adalah **cadangan v8**, bukan dipulihkan dari v7 — pemilik proses perlu mengesahkannya.

### 2.3 Penomboran bab berganda

| Masalah | Pembaikan |
|---------|-----------|
| Dua `## BAB 2` (Model Seni Bina + Database Strategy) | Yang kedua → **BAB 2A** |
| `## BAB 17.7` sebagai bab, sedangkan 17 sudah wujud | → **BAB 17A**, 10 subseksyen `17.7.x` → `17A.x` |
| 2 rujukan silang `BAB 17.7` | dikemas kini → `BAB 17A` |

### 2.4 Hierarki bahagian

`## PART XI` berada pada H2 manakala 10 PART lain pada H1. Ia akan muncul sebagai subseksyen dalam mana-mana TOC automatik. Dibaiki ke H1.

### 2.5 Indeks struktur basi

Jadual "Struktur Dokumen Gabungan" dalam v7 menyenaraikan BAB 0–35 dan 50–60 sahaja. Ia **tertinggal BAB 36–49, 51 dan 52** (15 bab) dan merujuk seksyen `6a` yang tidak wujud dalam dokumen. Diganti dengan indeks 77 bab yang dijana daripada kandungan sebenar. Lajur "Sumber versi" dibuang kerana tidak boleh disahkan dan sebahagiannya salah — lebih baik tiada daripada mengelirukan.

---

## 3. Yang sengaja TIDAK diubah

1. **Kandungan strategik** — angka kompetitif (7.7/10), unjuran hasil, harga RM288–RM1,888 tidak disentuh. Ia keputusan perniagaan, bukan ralat dokumentasi.
2. **Penomboran BAB 56A/56B/56C** — kelihatan janggal tetapi konsisten dengan rujukan silang sedia ada; menukarnya mencipta lebih banyak kerosakan daripada manfaat.
3. **Glosari BAB 99** dikekalkan di kedudukan terakhir.
4. **Tiada bab sedia ada dipadam.** Semua penambahan bersifat tambahan.

---

## 4. Cadangan tambahan yang BELUM dilaksanakan

BAB 61–68 sudah ditulis dalam PART XII. Senarai di bawah ialah cadangan seterusnya, disusun mengikut keutamaan. Setiap satu disemak terhadap dokumen v8 untuk mengelakkan pertindihan.

### P0 — sebelum pelancaran komersial

| Cadangan | Sebab | Usaha |
|----------|-------|-------|
| **Matriks cukai pelbagai negara** (SST → GST/VAT/SST-Thai) | v8 kuat untuk Malaysia; pengembangan SEA akan tersekat pada negara kedua | Sederhana |
| **Kos AI per tenant dan per ciri** | BAB 41 (FinOps) ada, tetapi tanpa atribusi per tenant margin boleh jadi negatif tanpa disedari | Kecil |
| **Ujian beban berjadual (k6) + model kapasiti per pelan** | BAB 37 menyebut prestasi, tiada program ujian beban berulang | Kecil |
| **Program bug bounty / saluran lapor kerentanan** | Disebut sekali sahaja; platform multi-tenant perlu saluran formal | Kecil |
| **Trust center awam** (status, SOC 2/ISO 27001 roadmap, DPA) | Jualan enterprise akan menanyakannya lebih awal daripada jangkaan | Kecil |

### P1 — dalam 2 suku tahun pertama

| Cadangan | Sebab | Usaha |
|----------|-------|-------|
| **Portal reseller dengan penyelesaian hasil automatik** | Model reseller wujud, tetapi tiada mekanisme bayaran perkongsian hasil | Sederhana |
| **Health score & ramalan churn per tenant** | Churn disebut 15 kali tetapi tiada skor ramalan | Sederhana |
| **Audit kebolehcapaian WCAG 2.2 AA** | Disebut sekali; halangan undang-undang di beberapa pasaran | Kecil |
| **Penyetempatan Thai, Vietnam, Bahasa Indonesia** | Hanya 4 sebutan; perlu untuk pengembangan SEA | Sederhana |
| **Semakan keselamatan plugin marketplace** | BAB 46 wujud tanpa proses semakan keselamatan pihak ketiga | Sederhana |
| **Game day / chaos engineering berjadual** | Tiada langsung; melengkapkan BAB 36 (DR) dan BAB 61 (SLO) | Kecil |

### P2 — kemudian

| Cadangan | Sebab |
|----------|-------|
| Enjin kualiti data & penyahgandaan CRM | `dedup` disebut 3 kali, tiada reka bentuk |
| Portal dokumentasi pembangun awam + proses keluaran SDK | BAB 10.7 ada, tetapi tiada kitaran hayat SDK |
| Program pensijilan rakan kongsi | Melengkapkan BAB 42 dan BAB 52 |
| A/B testing pada harga dan pembungkusan | 4 sebutan, tiada rangka kerja eksperimen |
| Laporan kelestarian/ESG | Diminta oleh tender kerajaan dan enterprise |

---

## 5. Cara mengesahkan hasil

```bash
python3 build_v8.py        # bina semula dari v7, setiap langkah ada assertion
```

Pemeriksaan bebas yang dijalankan pada v8:

- tiada aksara kawalan `[0x00–0x08, 0x0b, 0x0c, 0x0e–0x1f]`
- tiada CR (LF tulen)
- tiada baris jadual kosong
- 262 jadual, semua bilangan lajur konsisten
- 268 code fence, seimbang
- 12 PART, semuanya H1
- 77 bab, tiada nombor berganda
- tiada rujukan `BAB 17.7` di luar baris changelog
- PART XII sebelum glosari BAB 99
