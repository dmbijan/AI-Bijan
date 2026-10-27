# Analisis Kompetitif AI BIJAN — Berpijak pada Kod Sebenar

**Tarikh:** 24 Oktober 2026
**Kaedah:** Setiap tanda status disahkan dengan menyemak `app/`, `database/migrations/`, `tests/` dan `routes/`. Harga pesaing disahkan dari laman rasmi pada 24 Okt 2026.

> **Kenapa dokumen ini wujud:** `docs/ai-bijan-vs-world-comparison.md` (versi lama)
> menandakan ✅ untuk ciri yang **belum dikodkan** — Voice Interface, Edge AI,
> Proprietary Model, Collective Intelligence. Dokumen itu tidak boleh digunakan
> untuk jualan atau pelaburan. Ini versi yang boleh dipertahankan.

---

## 0. Harga Pesaing — Disahkan 24 Okt 2026

### Malaysia (tempatan)

| Produk | Harga disahkan | Kategori | Nota sumber |
|---|---|---|---|
| **StoreHub** | RM122 (Starter) / RM235 (Advanced) / RM471 (Pro) **sebulan** | POS + inventori + loyalty + e-Invois | storehub.com/my/pricing |
| **Bukku** | RM0 (Launch) / RM35 (Seed) / RM65 (Grow) / RM95 (Prime) / RM135 (Elite) + 8% SST | Perakaunan + e-Invois LHDN | bukku.my/pricing |
| **SiteGiant** | Harga tidak dipaparkan awam (hubungi) | Multichannel eCommerce ERP | sitegiant.my/pricing |

**Nota penting tentang StoreHub:**
- Harga ialah **per bulan per perniagaan** (bukan per pengguna)
- Setiap pelan termasuk **1 kaunter + 1 kedai**; tambahan kaunter/kedai ialah add-on
- **20,000+ pelanggan** di Asia Tenggara
- E-Invois **sudah terbina** dalam semua pelan
- Boleh beli 36–60 bulan → dapat 12–24 bulan percuma

**Nota penting tentang Bukku:**
- **Harga per syarikat**, pengguna **tanpa had** pada semua pelan berbayar
- e-Invois LHDN **termasuk pada pelan RM0**
- Add-on berasingan: SST RM12, multi-currency RM12, OCR RM8, storan RM5
- Fokus: **perakaunan sahaja** — tiada POS, tiada inventori fizikal penuh, tiada HR

**Nota penting tentang SiteGiant:**
- 20,000+ pedagang; fokus **eCommerce multichannel** (Shopee, Lazada, TikTok)
- Tiada POS fizikal teras; tiada HR/payroll
- Harga tersembunyi — perlu hubungi jualan

### Global (masuk Malaysia)

| Produk | Harga disahkan | Nota |
|---|---|---|
| **Zoho One** | **RM148/pekerja/bulan** (bayar tahunan, semua pekerja) atau **RM360/pengguna/bulan** (fleksibel) | zoho.com/one/pricing — disahkan 24 Okt 2026 |
| **Odoo** | USD13.50 (~RM60) Standard / USD25.50 (~RM113) Custom **per pengguna/bulan** (tahunan) | odoo.com/pricing — ada Agentic AI pada pelan Custom |

**Penemuan kritikal Zoho:** model "ALL EMPLOYEE PRICING" bermakna anda mesti beli
lesen untuk **setiap pekerja dalam payroll**. Untuk PKS 15 pekerja:
**RM148 × 15 = RM2,220/bulan**. Ini 7.7× harga AI BIJAN.

**Penemuan kritikal Odoo:** USD25.50/pengguna/bulan untuk pelan Custom (yang ada
Agentic AI). Untuk 15 pengguna = **~RM1,695/bulan**. Tetapi Odoo **tidak termasuk
kos pelaksanaan** (integrator Malaysia: RM15k–80k sekali).

---

## 0.1 Perbandingan Kos Sebenar — PKS 15 Pekerja

| Sistem | Kiraan | Kos bulanan | Kos tahun 1 |
|---|---|---|---|
| **AI BIJAN (cadangan)** | RM288 flat | **RM288** | RM3,456 |
| Bukku Elite | RM135 + 8% SST | RM146 | RM1,749 |
| StoreHub Advanced | RM235 flat | RM235 | RM2,820 |
| Odoo Custom | USD25.50 × 15 | ~RM1,695 | ~RM20,340 + pelaksanaan |
| Zoho One (all-employee) | RM148 × 15 | **RM2,220** | RM26,640 |
| MS Dynamics | RM540 × 15 | RM8,100 | RM97,200 |
| SAP S/4HANA | Enterprise | RM45,000+ | RM540,000+ |

**Tafsiran:**
- AI BIJAN bukan termurah — **Bukku lebih murah** kerana ia perakaunan sahaja
- AI BIJAN bersaing dengan **StoreHub** (RM235 vs RM288) — harga hampir sama
- AI BIJAN **8× lebih murah** daripada Zoho One untuk syarikat 15 pekerja

**Kesimpulan harga:** harga RM288 adalah **munasabah dan kompetitif**, tetapi
**bukan pembeza utama**. Pembeza mesti AI + pematuhan, bukan harga.

---

## 1. Skala Sebenar AI BIJAN

> ✅ **Dikemas kini 26 Okt 2026** — angka disahkan daripada kod sebenar.

| Ukuran | Nilai (24 Okt) | Nilai (26 Okt) | Boleh disahkan |
|---|---|---|---|
| Modul backend | 34 | **58** | `app/Modules/` |
| Endpoint API | ~970 | **1,518** | `php artisan route:list --path=api/v1` |
| Jadual pangkalan data | 27 | **346** | `database/migrations/` (49 fail) |
| Ujian lulus | 220 | **962** | `php artisan test` |
| Assertion | — | **5,033** | `php artisan test` |
| Model dengan isolasi tenant | 108 | **292** | Trait `BelongsToTenant` |
| Resource Filament | 0 | **66** | `app/Filament/Resources/` |
| Kebenaran | — | **212** | jadual `permissions` |

### Realiti UI vs Backend

| Lapisan | Kematangan (24 Okt) | Kematangan (26 Okt) |
|---|---|---|
| Backend API | **Tinggi** | **Tinggi** — 1,518 endpoint, 962 ujian |
| UI | **Rendah** (skor 2/10) | **Sederhana** (skor 7/10) — 66 resource Filament + 6 widget + Tetapan |

**Kesimpulan dikemas kini:** UI no longer "enjin tanpa badan kereta". Filament
(T-71) menaikkan skor UI daripada **2/10 → 7/10** dengan 66 resource CRUD berfungsi.
Namun UI masih **generik** (borang dinamik) — bukan UI domain yang direka khusus.
Pembeza kompetitif kekal pada **AI + pematuhan MY + integrasi**, bukan UI.

---

## 2. Analisis Kompetitor — Dilapis Tiga

### Lapisan A: Gergasi Perusahaan (tidak bersaing secara langsung)

| Sistem | Harga | Kekuatan | Kelemahan untuk PKS Malaysia |
|---|---|---|---|
| SAP S/4HANA | RM45k–450k/bulan | Kedalaman proses | Kos pelaksanaan 2–3× lesen; 6–18 bulan go-live |
| Oracle Fusion | RM22k–225k/bulan | Kewangan, HR | Sama; tiada sokongan BM |
| MS Dynamics 365 | RM540–1,600/pengguna/bulan | Integrasi Microsoft | Kos per pengguna menghukum PKS 15 staf |
| Salesforce | RM110k–2.2j/tahun | CRM terdalam | CRM sahaja; tiada POS/inventori |

**Kesimpulan:** bukan pesaing. Mereka tidak menyasarkan kedai 5 pekerja di Kota Bharu.

---

### Lapisan B: Pesaing Sebenar (SME all-in-one)

| Sistem | Harga disahkan | Kekuatan sebenar | Celah yang boleh direbut |
|---|---|---|---|
| **StoreHub** | RM122–471/bulan (per perniagaan) | POS + inventori + loyalty + **e-Invois terbina**, 20k pelanggan | Tiada HR/payroll; tiada kewangan penuh; tiada AI sebenar |
| **Bukku** | RM0–135/bulan (+8% SST) | Perakaunan Malaysia, **e-Invois LHDN pada pelan percuma**, pengguna tanpa had | Perakaunan sahaja — tiada POS, HR, CRM |
| **SiteGiant** | Harga tersembunyi | Multichannel eCommerce (Shopee/Lazada/TikTok), 20k pedagang | eCommerce sahaja; tiada POS fizikal, HR, kewangan |
| **Zoho One** | **RM148/pekerja** atau RM360/pengguna | 50+ app matang | **Wajib beli lesen semua pekerja**; tiada EPF/PCB asli; tiada e-Invois; AI cetek |
| **Odoo** | USD13.50–25.50/pengguna | Open source, 50+ modul, Agentic AI pada Custom | Lokalisasi MY separa; **kos pelaksanaan RM15k–80k**; perlu integrator |
| **HubSpot** | RM135–1,350/pengguna | Pemasaran & CRM terbaik | Tiada POS/inventori/kewangan |

**Pesaing paling dekat:** **StoreHub** (POS+e-Invois, harga hampir sama) dan
**Bukku** (perakaunan+e-Invois, jauh lebih murah tetapi sempit).

**Ancaman gabungan:** pelanggan boleh guna **Bukku (RM146) + StoreHub Starter
(RM122) = RM268/bulan** — hampir sama harga dengan AI BIJAN RM288, tetapi
mendapat perakaunan matang + POS matang.

> **Ini ancaman paling nyata.** AI BIJAN mesti menang pada **integrasi** dan
> **AI**, bukan pada harga atau bilangan modul.

---

### Lapisan C: AI-Native (pesaing baharu)

| Sistem | Ancaman | Sebab |
|---|---|---|
| ChatGPT/Claude Teams | Rendah | Tiada data perniagaan; tiada tindakan |
| Relevance AI / Stack AI | Sederhana | Boleh bina workflow, tetapi tiada modul perniagaan |
| **Sistem AI menegak** (cth AI untuk restoran) | **Tinggi** | Fokus, pantas, mudah dijual |

**Ancaman sebenar bukan gergasi.** Ia pemula yang pilih **satu industri** dan
selesaikan sepenuhnya.

---

## 3. Analisis Jurang — Jujur

### Di mana AI BIJAN benar-benar mendahului

| Kelebihan | Status kod | Boleh ditiru? |
|---|---|---|
| **Isolasi tenant seni bina** | ✅ **292 model**, 4 lapisan, diuji | Ya, tetapi lambat |
| **Pagar biometrik PDPA** | ✅ 6 pagar dikuatkuasakan kod | **Sukar** — perlu kepakaran undang-undang |
| **B-Boardroom multi-ejen** | ✅ 7 langkah, diuji | Sederhana |
| **AI Token Wallet + margin** | ✅ Reseller model | Ya |
| **White-label + revenue share** | 🟡 Asas ada | Ya |
| **PromptGuard (AI safety)** | ✅ 16 ujian | Ya, tetapi jarang dibuat |
| **Payroll statutori Malaysia** | ✅ Kiraan + eksport | **Sukar** — butiran tempatan |

### Di mana AI BIJAN ketinggalan

| Jurang | Pesaing yang mendahului | Kesan |
|---|---|---|
| **UI produk** | Semua | **Kritikal** — tidak boleh dijual |
| **Mod mudah alih** | StoreHub, Zoho | Tinggi — pemilik PKS hidup di telefon |
| **WhatsApp sebagai UI** | StoreHub | Tinggi — saluran utama PKS |
| **Onboarding layan diri** | StoreHub, Zoho | Tinggi — PKS tiada masa |
| **Integrasi bayaran sebenar** | Semua | Kritikal — tiada FPX = tiada jualan |
| **e-Invois LHDN** | Bukku, Financio | Tinggi — wajib di Malaysia |
| **Laporan ROI terbukti** | Tiada (peluang) | Peluang pembeza |
| **Kedalaman satu modul** | Semua | Kritikal — POS kita rangka, bukan produk |

---

## 4. Posisi Strategik yang Boleh Dipertahankan

### Formulasi

> **AI BIJAN ialah sistem operasi perniagaan AI-first untuk PKS Malaysia,
> dengan pematuhan tempatan terbina dalam dan AI yang bertindak — bukan
> sekadar menjawab.**

### Tiga tiang pembeza

**Tiang 1 — Pematuhan Malaysia sebagai senjata, bukan senarai semak**

| Keperluan | Zoho | Odoo | StoreHub | Bukku | AI BIJAN |
|---|---|---|---|---|---|
| EPF/SOCSO/EIS/PCB | ❌ | Separa | ❌ | ❌ | ✅ |
| HRDF 1% tanpa had | ❌ | ❌ | ❌ | ❌ | ✅ |
| **e-Invois LHDN** | ❌ | Separa | ✅ | ✅ (percuma) | 🟡 |
| Pagar consent PDPA biometrik | ❌ | ❌ | ❌ | ❌ | ✅ |
| Audit trail penuh | Separa | Separa | ❌ | Separa | ✅ |
| Pengguna tanpa had | ❌ (RM148/pekerja) | ❌ (per pengguna) | ✅ | ✅ | ✅ |

> **Penting:** e-Invois kini **jadual stakes**, bukan pembeza. StoreHub dan Bukku
> sudah ada. AI BIJAN mesti siapkannya untuk bersaing, bukan untuk mendahului.
>
> **Pembeza sebenar:** EPF/SOCSO/EIS/PCB/HRDF + pagar PDPA + AI yang bertindak.
> Tiada satu pesaing pun mempunyai kombinasi ini.

**Tiang 2 — AI yang bertindak, dengan pagar**

Bukan "AI chatbot dalam dashboard". AI yang:
- Menyediakan dokumen (draf, bukan tandatangan)
- Mencadang tindakan (bukan melaksanakan tanpa kelulusan)
- Dipagari 6 semakan sebelum tindakan berisiko
- Semua tindakan diaudit

**Tiang 3 — Ekonomi tanpa had pengguna**

| Sistem | 15 pekerja | Kos bulanan |
|---|---|---|
| Zoho One | RM165 × 15 | **RM2,475** |
| MS Dynamics | RM540 × 15 | **RM8,100** |
| **AI BIJAN** | RM288 flat | **RM288** |

Untuk PKS dengan 15 pekerja, ini **8.6× lebih murah** daripada Zoho.

---

## 5. Kesilapan Posisi yang Mesti Dielak

| Dakwaan salah | Kenapa bahaya | Ganti dengan |
|---|---|---|
| "Ganti SAP" | Tidak benar; hilang kredibiliti | "Untuk PKS, bukan perusahaan" |
| "AI urus perniagaan anda" | Tidak boleh dibuktikan | "AI jimatkan X jam, tambah Y prospek" |
| "59 modul siap" | 66 resource Filament = CRUD berfungsi, tetapi borang generik | "POS, CRM, HR, kewangan dalam satu" |
| "Voice interface" | Belum dikodkan | Jangan sebut sehingga siap |
| "Collective intelligence" | Belum dikodkan | Jangan sebut sehingga siap |
| "Proprietary model" | Tidak wujud | "Model terbaik untuk setiap tugas" |

---

## 6. Pelan Tindakan Kompetitif

### Fasa 1 — Boleh dijual (minggu 1–8)

| Tindakan | Sebab kompetitif |
|---|---|
| POS sebenar (bukan rangka) | Bersaing terus dengan StoreHub |
| WhatsApp sebagai UI | Bezakan daripada Zoho/Odoo |
| Onboarding < 30 minit | Bezakan daripada Odoo (perlu integrator) |
| Laporan ROI automatik | Satu-satunya pembeza yang pesaing tiada |

**Metrik kejayaan:** 10 pelanggan berbayar menggunakan POS setiap hari.

### Fasa 2 — Boleh dipercayai (minggu 9–16)

| Tindakan | Sebab kompetitif |
|---|---|
| Integrasi FPX (ToyyibPay/Billplz) | Tanpa ini, tiada transaksi sebenar |
| e-Invois LHDN | Wajib di Malaysia; Bukku sudah ada |
| Mod mudah alih (PWA) | Pemilik PKS hidup di telefon |
| MySQL + backup | Kestabilan produksi |

### Fasa 3 — Boleh berkembang (minggu 17+)

| Tindakan | Sebab kompetitif |
|---|---|
| White-label + reseller | Saluran jualan berganda |
| Integrasi Ads | Hanya selepas ada pelanggan |
| Vision/CCTV | Hanya selepas POS terbukti |
| Voice | Hanya selepas ada permintaan berbayar |

---

## 7. Skor Kompetitif (Jujur)

Skala 1–10, dinilai pada **keupayaan sebenar hari ini**, bukan rancangan.

| Dimensi | AI BIJAN | Zoho One | Odoo | StoreHub | Bukku |
|---|---|---|---|---|---|
| Kedalaman fungsi | 5 | 9 | 9 | 6 | 5 |
| **Kematangan UI** | **2** | 9 | 7 | 8 | 8 |
| Pematuhan Malaysia | **8** | 3 | 5 | 6 | 8 |
| Keupayaan AI | **7** | 3 | 4 | 2 | 1 |
| Keselamatan & isolasi | **9** | 7 | 6 | 5 | 6 |
| Kos efektif (PKS 15) | **9** | 3 | 5 | 8 | 10 |
| Kemudahan onboarding | 4 | 8 | **2** | 9 | 8 |
| **Purata** | **6.3** | 6.0 | 5.4 | 6.3 | 6.6 |

**Tafsiran jujur:**
- AI BIJAN **seri dengan StoreHub (6.3)** dan **sedikit di belakang Bukku (6.6)**
- Bukku mendahului kerana UI matang + harga sangat rendah — tetapi ia **sempit**
  (perakaunan sahaja)
- Kekuatan AI BIJAN terletak pada **AI (7) + isolasi (9) + pematuhan (8)**
- **UI (2) ialah satu-satunya sebab kita tidak mendahului**

**Jika UI mencapai 8:** purata AI BIJAN menjadi **7.2** — mendahului semua.

**Nota tentang skor Zoho:** turun daripada 6.4 ke 6.0 selepas mengesahkan harga
sebenar (RM148/pekerja × 15 = RM2,220/bulan) dan ketiadaan e-Invois LHDN.

---

## 8. Ancaman Paling Nyata

### Ancaman 1: Gabungan dua produk murah

| Gabungan | Kos/bulan | Apa pelanggan dapat |
|---|---|---|
| Bukku Elite + StoreHub Starter | RM146 + RM122 = **RM268** | Perakaunan matang + POS matang + e-Invois |
| AI BIJAN | **RM288** | Lebih banyak modul, tetapi UI belum siap |

**Kesan:** dengan harga hampir sama, pelanggan rasional pilih gabungan yang
**sudah terbukti berfungsi**.

**Cara menang:** integrasi. Dalam AI BIJAN, satu transaksi POS terus jadi invois
dengan e-Invois, rekod inventori, dan catatan jurnal — **tanpa salinan manual**.
Dalam gabungan, pemilik mesti eksport-import antara dua sistem setiap hari.

### Ancaman 2: StoreHub turun harga

StoreHub sudah ada 20,000 pelanggan dan e-Invois. Jika ia turun ke RM188,
AI BIJAN kehilangan hujah harga sepenuhnya.

**Cara menang:** AI yang bertindak + pematuhan HR (StoreHub tiada payroll).

### Ancaman 3: AI menjadi jadual stakes

Zoho (Zia), Odoo (Agentic AI), dan setiap vendor sedang tambah AI. Dalam 12–18
bulan, "ada AI" bukan pembeza lagi.

**Cara menang:** AI yang **menyelesaikan kerja** (jana dokumen, susulan pelanggan,
kawalan inventori) dengan **pagar kelulusan** — bukan chatbot yang menjawab soalan.

---

## 9. Ringkasan Satu Halaman

| Soalan | Jawapan |
|---|---|
| Adakah AI BIJAN terbaik di dunia? | **Tidak.** |
| Adakah ia terbaik untuk PKS Malaysia dengan AI? | **Berpotensi** — tetapi UI mesti siap dahulu. |
| Apa kelebihan yang sukar ditiru? | Pematuhan HR Malaysia (EPF/PCB/HRDF) + pagar PDPA + AI bertindak. |
| Apa kelemahan paling kritikal? | UI (purata 5.5 KB, 3 panggilan API). |
| Siapa pesaing paling dekat? | **StoreHub** (POS) dan **Bukku** (perakaunan). |
| Apa satu perkara yang mesti dibuat dahulu? | **POS sebenar.** |
| Adakah RM288 harga yang betul? | **Ya**, tetapi bukan pembeza — StoreHub RM235, Bukku RM146. |
| Bila boleh jual? | Selepas POS + FPX + e-Invois + onboarding < 30 minit. |

---

## Nota Pemeliharaan

Dokumen ini mesti dikemas kini apabila status kod berubah. **Jangan** tandakan ✅
tanpa menyemak `app/`, `routes/` dan lulus ujian. Rujuk skill `aibijan-coding`
untuk prosedur pengesahan.
