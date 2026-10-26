# Strategi Produk & Keutamaan

Konteks yang mesti difahami sebelum mencadangkan atau membina ciri baharu.

## Diagnosis Teras

**AI BIJAN ada backend gred produksi tetapi UI gred demo.**

| Lapisan | Ukuran |
|---|---|
| API backend | ~1,015 endpoint, 314 ujian lulus |
| Modul | 39 (34 asal + 5 BAB 82) |
| Isolasi tenant | 108 model, 4 lapisan, diuji |
| Pematuhan Malaysia | EPF/SOCSO/EIS/PCB/HRDF + eksport + e-Invois |
| AI (ejen, boardroom, guard) | 11 ejen, 7 langkah, autonomy 0-4 |
| Enjin jualan (BAB 82) | Prospecting + Blast + SalesCopilot + MediaStudio + ExecutiveAI |
| **UI** | **34 view × 5.5 KB × 3 panggilan API** |
| **Migrasi masuk** | **Tiada — jurang #1** |
| **Integrasi bayaran** | **Tiada** |

Contoh konkrit: `pos.blade.php` ialah 7.1 KB dengan 6 panggilan API. POS sebenar
memerlukan 800–1,500 baris. Ia rangka, bukan produk.

## Kesilapan yang Mesti Dielak

| Jangan | Sebab |
|---|---|
| Tanda ✅ dalam dokumen untuk ciri belum dikodkan | Menyesatkan jualan & pelaburan |
| Tambah modul baharu | 34 rangka sudah lebih dari cukup |
| Bina app iOS/Android asli | PWA cukup, kos 5× lebih rendah |
| Sambung integrasi serentak | Setiap satu perlu kelulusan berbulan-bulan |
| Dakwa "ganti SAP" | Tidak benar; hilang kredibiliti |
| Dakwa "AI urus perniagaan" | Tidak boleh dibuktikan |
| Bina Collective Intelligence sekarang | Perlu 100+ tenant sebelum bermakna |
| Bina Voice sekarang | Belum dikodkan; tunggu permintaan berbayar |
| Tambah modul lagi tanpa migrasi + UI | 3 syarat di BAB 83 perlu dipenuhi dahulu |
| Iklankan ciri AI tanpa metrik | Help Scout: tiada ciri tanpa angka yang boleh diukur |

## Keutamaan Mengikut Nisbah Impak/Kerja

| # | Ciri | Status backend | Kerja diperlukan |
|---|---|---|---|
| 1 | **⭐ B-Migration Wizard** (BAB 83) | 🟡 Import CSV ada, tiada wizard/profil/simulator | Modul `Migration/` |
| 2 | **POS sebenar** | ✅ Lengkap (`storeDraft`, `addItem`, `complete`, `refund`, `open`/`close`) | UI + mod offline |
| 3 | **UI 2 → 7** (semua modul) | 🟡 34 view rangka | Frontend — kritikal untuk skor |
| 4 | **AI metrics dalam jualan** (BAB 83) | ✅ Semua data ada | Endpoint + paparan metrik |
| 5 | **WhatsApp sebagai UI** | 🟡 Omnichannel + AI + Boardroom + Blast ada | Sambungan + pemacu |
| 6 | **Laporan ROI automatik** | ✅ Semua jadual trypt | Servis + view |
| 7 | **PWA 4 skrin** | ✅ Semua endpoint ada | Frontend |
| 8 | **B-Agent Studio + MCP** (BAB 83) | ✅ `AIAgents` ada | UI builder + MCP endpoint |
| 9 | **FPX** | ❌ | Integrasi pihak ketiga |
| 10 | **OSS incorporation** (BAB 84) | ✅ 20 Tier 1 candidates | License audit + `THIRD-PARTY-NOTICES.md` |

> **Nota BAB 83:** Keutamaan #1 dan #3 menggantikan "tambah modul" sebagai
> keutamaan. Tanpa migrasi + UI, 39 modul tidak menjadi produk.

## Harga Pesaing (disahkan 24 Okt 2026)

| Produk | Harga | Nota |
|---|---|---|
| Bukku | RM0–135 + 8% SST | Perakaunan sahaja, pengguna tanpa had, e-Invois percuma |
| StoreHub | RM122/235/471 bulanan | POS+e-Invois, 20k pelanggan, per perniagaan |
| Zoho One | **RM148/pekerja** atau RM360/pengguna | **Wajib semua pekerja** = RM2,220 untuk 15 staf |
| Odoo | USD13.50–25.50/pengguna | + kos pelaksanaan RM15k–80k |
| AI BIJAN | RM288 flat | 8× lebih murah dari Zoho untuk 15 pekerja |

**Ancaman paling nyata:** Bukku (RM146) + StoreHub Starter (RM122) = **RM268**,
hampir sama harga AI BIJAN RM288 tetapi kedua-duanya sudah matang.

**Cara menang:** integrasi (POS→invois→jurnal tanpa salinan manual) + pematuhan HR
(EPF/PCB/HRDF yang tiada pada StoreHub/Bukku) + AI yang bertindak.

**Jangan** jadikan harga sebagai hujah utama — ia bukan pembeza.

## Prinsip Produk

1. **Satu modul sempurna mengalahkan 34 modul separuh siap.**
2. **Jika modul menerima wang, ia mesti berfungsi offline.**
3. **Jual hasil, bukan keupayaan.** "RM82 → RM18,400" mengalahkan "11 ejen AI".
4. **Jangan tanda ✅ tanpa menyemak kod dan lulus ujian.**
5. **Pematuhan Malaysia ialah senjata, bukan senarai semak.**

## Metrik Kejayaan

| Metrik | Sasaran |
|---|---|
| Masa daftar → transaksi pertama | < 30 minit |
| Pelanggan guna POS setiap hari | 10 (fasa 1) |
| Kadar pembaharuan | > 85% |
| NPS | > 40 |

## Rujukan

- `docs/ANALISIS-KOMPETITIF.md` — analisis pesaing jujur dengan skor
- `docs/CADANGAN-IMPAK-TINGGI.md` — 15 cadangan dengan urutan pelaksanaan
- `docs/BUSINESS-GROWTH-ENGINE.md` — 9 kelebihan dengan status kod

Sebelum mencadangkan ciri baharu, semak ketiga-tiga dokumen ini dahulu.
