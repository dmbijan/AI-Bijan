# AI BIJAN SaaS Ecosystem V4.5 (18 Oktober 2026)

## Dokumen Induk Produk, Seni Bina, Keperluan Sistem dan Pelan Pembangunan

**Versi:** 4.5  
**Tarikh:** 18 Oktober 2026  
**Nama produk:** AI BIJAN SaaS  
**Tagline:** *Your AI Business Team, in the Room.*  
**Prinsip utama:** Sistem berdiri sendiri (stand-alone), multi-tenant, berasaskan AI, dibina dengan Laravel 11+. Sesuai untuk hosting cPanel yang memenuhi keperluan teknikal.  
**Keperluan wajib:** AI BIJAN dibangunkan sebagai sistem sendiri. **ERPNext bukan dependency**, bukan backend, bukan pangkalan data aplikasi, bukan UI, dan bukan enjin operasi yang diperlukan untuk menjalankan AI BIJAN. Frappe dan Frappe HR juga bukan dependency.

------------------------------------------------------------------------

## 1. Ringkasan Eksekutif

AI BIJAN ialah ekosistem perisian perniagaan bersepadu yang
menggabungkan operasi dalaman, jualan, pelanggan, pemasaran, stok,
pengeluaran, COGS, kewangan, dokumen, logistik, analitik dan automasi AI
dalam satu platform SaaS.

Platform ini menyasarkan perniagaan mikro, SME, retail, e-dagang, F&B,
perkhidmatan, agensi dan pengilang. Setiap pelanggan SaaS mempunyai
ruang kerja, pengguna, peranan, data, tetapan, dokumen, ejen AI dan
penggunaan AI yang diasingkan daripada pelanggan lain.

AI BIJAN hendaklah menjadi produk milik sendiri dengan kod aplikasi,
skema pangkalan data, API, antaramuka, pengiraan, aliran kerja dan
sistem kebenaran tersendiri. Konsep atau keperluan perniagaan boleh
dikaji daripada pasaran dan perisian ERP secara umum, tetapi jangan
menyalin kod proprietari, aset, teks, UI atau bahan berlesen tanpa
kebenaran. Jangan menggunakan ERPNext, Frappe atau Frappe HR sebagai
komponen runtime atau bergantung pada pemasangan mereka.

### Hasil yang dikehendaki

1.  Backend pentadbir platform (Master Admin → Super Admin).
2.  Backend SaaS multi-tenant dengan white-label support.
3.  Frontend pemilik perniagaan, pengurus, pekerja/staf dan pengguna
    akhir.
4.  Modul perniagaan bersepadu dengan satu identiti pengguna dan satu
    sistem kebenaran.
5.  B-COGS Engine yang mengira kos jualan dan margin berdasarkan
    transaksi sebenar.
6.  Sistem dokumen rasmi dengan templat Malaysia dan eksport pelbagai
    format.
7.  B-Brain Hub untuk AI, ejen khusus, automasi, analisis dan ramalan.
8.  API dan webhook untuk integrasi pihak ketiga.
9.  Pakej aplikasi stand-alone yang boleh dipasang pada cPanel yang
    serasi, tanpa kebergantungan kepada ERPNext/Frappe.
10. Dokumentasi pemasangan, konfigurasi, operasi, keselamatan, sandaran
    dan pemulihan.
11. Multi-tier admin hierarchy (Master/Super/White-label/Customer).
12. AI Token Wallet system berasingan daripada langganan SaaS.
13. Industry Blueprint Engine untuk onboarding automatik.

------------------------------------------------------------------------

## 2. Prinsip Produk dan Batasan

### 2.1 Prinsip wajib

-   **Stand-alone:** AI BIJAN boleh beroperasi sebagai aplikasi sendiri.
-   **Multi-tenant:** Setiap syarikat/pelanggan mempunyai tenant dan
    data yang diasingkan.
-   **AI pilihan tetapi bersepadu:** Fungsi teras perniagaan tidak boleh
    gagal hanya kerana penyedia AI luar tidak tersedia. AI menggunakan
    adapter penyedia yang boleh ditukar.
-   **API-first:** Modul berkomunikasi melalui API dalaman yang disahkan
    dan didokumenkan.
-   **Auditability:** Perubahan kewangan, stok, payroll, akses, dokumen
    dan tindakan AI direkodkan.
-   **Human approval:** Tindakan berisiko tinggi memerlukan kelulusan
    manusia.
-   **Security by design:** Prinsip least privilege, pengesahan input,
    perlindungan rahsia, pengasingan tenant dan ujian keselamatan.
-   **Malaysia-ready:** Format mata wang MYR, zon waktu
    Asia/Kuala_Lumpur, Bahasa Melayu dan Inggeris, serta templat yang
    boleh dikonfigurasi untuk keperluan Malaysia.
-   **Exportability:** Data dan dokumen boleh dieksport dalam format
    terbuka yang munasabah supaya pelanggan tidak terkunci pada
    platform.
-   **No hidden ERP dependency:** Tiada endpoint, jadual, servis atau
    proses AI BIJAN yang memerlukan ERPNext atau Frappe untuk berfungsi.

### 2.2 Batasan pelaksanaan

"Boleh dipasang pada cPanel" bergantung pada pakej hosting sebenar.
Sebelum memilih stack, sahkan versi PHP, pangkalan data, akses
SSH/Composer, cron, had proses, sokongan queue/worker, penyimpanan fail,
SSL, outbound HTTPS dan had memori. Jika hosting tidak menyokong worker
berterusan atau servis yang diperlukan, sediakan mod queue berasaskan
cron atau gunakan hosting yang serasi. Jangan menganggap semua akaun
cPanel menyokong Docker, Redis, Node.js atau proses latar belakang
berterusan.

Untuk skala SaaS besar, cPanel mungkin tidak sesuai sebagai persekitaran
akhir. Seni bina hendaklah membolehkan aplikasi dipindahkan ke VPS/cloud
kemudian tanpa menulis semula logik perniagaan.

### 2.3 Stack Teknologi (v4.5)

| Komponen | Teknologi | Catatan |
|----------|-----------|---------|
| Backend | Laravel 11+, PHP 8.3+ | Modular monolith |
| Database | MySQL 8.x / MariaDB 10.6+ | Transactional data |
| Cache/Queue | Redis + Laravel Horizon | Background jobs |
| Realtime | Laravel Reverb | Chat, status, notifications |
| Frontend | Next.js 15 + React 19 + Tailwind CSS | User-facing screens |
| Admin Panel | Filament PHP | Platform HQ & tenant admin |
| Object Storage | Cloudflare R2 / AWS S3 | Files, images, documents |

------------------------------------------------------------------------

## 3. Struktur Ekosistem AI BIJAN SaaS

``` text
AI BIJAN SaaS
├── 1. Operasi Dalaman & Pasukan
│   ├── B-Virtual Office
│   ├── B-HRMS
│   ├── B-Sign & Docs
│   ├── B-Projects
│   └── B-Assets
│
├── 2. Jualan, Saluran & Pertumbuhan
│   ├── B-POS System
│   ├── B-Site
│   ├── B-Link
│   ├── B-Marketplace
│   └── B-Affiliate
│
├── 3. Pelanggan, Pemasaran & Sokongan
│   ├── B-Loyalty
│   ├── B-Omnichannel
│   ├── B-Booking
│   ├── B-Marketing
│   └── B-Helpdesk / B-Desk
│
├── 4. Stok, Pengeluaran & COGS
│   ├── B-COGS Engine
│   ├── B-Inventory & Warehouse
│   └── B-BOM & Manufacturing
│
├── 5. Kewangan, Pembelian & Logistik
│   ├── B-Finance
│   ├── B-Invoicing
│   ├── B-Procurement
│   └── B-Logistics
│
├── 6. Kebolehcapaian & Infrastruktur
│   ├── B-Connect / API Gateway
│   └── B-Auth & Security
│
└── 7. Dashboard & Intelligence
    ├── B-Analytics
    └── B-Brain Hub (AI Engine)
```

Modul hendaklah dibangunkan berperingkat. Setiap modul mempunyai skop,
model data, API, kebenaran, audit trail, ujian dan dokumentasi sendiri,
tetapi menggunakan platform teras yang sama.

------------------------------------------------------------------------

## 4. Jenis Pengguna dan Portal

### 4.1 Portal Super Admin AI BIJAN

Pentadbir platform mengurus:

-   Tenant/pelanggan SaaS, status akaun dan pakej langganan.
-   Pelan harga, had pengguna, storan, penggunaan AI dan kuota API.
-   Pengaktifan/penyahaktifan modul mengikut pakej.
-   Penyedia AI, model yang dibenarkan, had token, had kos dan fallback.
-   Kesihatan sistem, ralat, queue, penggunaan sumber dan log
    keselamatan.
-   Tiket sokongan dan pengumuman platform.
-   Tetapan global, bahasa, mata wang, zon waktu dan konfigurasi e-mel.
-   Audit tindakan pentadbir dan akses sokongan yang terkawal.

Super Admin tidak boleh melihat kandungan perniagaan tenant secara
automatik tanpa kebenaran yang sah. Akses sokongan luar biasa perlu
bersebab, terhad masa, dilog dan boleh diaudit.

### 4.2 Portal Pemilik/Pentadbir Tenant

-   Daftar syarikat dan tetapkan profil perniagaan.
-   Jemput pengguna, tetapkan peranan dan lokasi.
-   Pilih modul dan konfigurasi operasi.
-   Tetapkan carta akaun, cukai, mata wang, kaedah kos stok dan tempoh
    kewangan.
-   Urus integrasi, API key, webhook dan tetapan AI tenant.
-   Lihat dashboard, laporan, dokumen dan audit tenant.
-   Tetapkan polisi kelulusan dan akses data.

### 4.3 Portal Pengurus

-   Mengurus pasukan, tugasan, cawangan, inventori dan laporan mengikut
    kebenaran.
-   Meluluskan pembelian, pelarasan stok, cuti, payroll, dokumen dan
    tindakan AI.
-   Melihat KPI yang dibenarkan.

### 4.4 Portal Staf

-   Clock-in/out dan rekod kehadiran.
-   Melihat tugasan, jadual, projek dan pengumuman.
-   Menghantar permohonan cuti, tuntutan dan dokumen.
-   Melihat slip gaji sendiri dengan kawalan akses yang ketat.
-   Memuat turun dokumen sendiri atau dokumen yang diberikan kepadanya.

### 4.5 Portal Pelanggan dan Pengguna Akhir

-   Membuat pesanan, tempahan, pembayaran atau permintaan servis.
-   Melihat invois, status pesanan dan rekod yang dibenarkan.
-   Mengurus profil, keahlian, baucar dan mata ganjaran.
-   Berkomunikasi dengan sokongan.

### 4.6 Portal Ejen/Affiliate dan Tapak Awam

-   Pautan rujukan dan prestasi komisen.
-   Katalog dan storefront awam.
-   Borang lead, tempahan, checkout dan landing page.
-   Akses awam hanya kepada data yang sengaja diterbitkan.

------------------------------------------------------------------------

## 5. Modul Perniagaan: Skop Fungsian

### 5.1 B-Virtual Office

-   Ruang kerja syarikat, pasukan, pengumuman, chat dan pengurusan
    tugasan.
-   Paparan kehadiran online dan aktiviti mengikut polisi privasi.
-   Pengurusan mesyuarat, nota, fail dan tindakan susulan.
-   AI boleh merumuskan nota, mencadangkan tugasan dan menyediakan draf.
-   Integrasi video/komunikasi pihak ketiga melalui adapter jika
    diperlukan.

### 5.2 B-HRMS

-   Rekod pekerja, jawatan, jabatan, cawangan dan sejarah pekerjaan.
-   Kehadiran, jadual syif, cuti, tuntutan dan kelulusan.
-   Rekod payroll, elaun, potongan, pendahuluan dan sejarah pembayaran.
-   Slip gaji individu dan laporan payroll.
-   Konfigurasi caruman/potongan mengikut peraturan yang berkenaan dan
    tarikh kuat kuasa.
-   Clock-in berasaskan lokasi/geofencing hanya dengan notis, tujuan,
    akses dan polisi penyimpanan yang sesuai.
-   Kawalan akses ketat kepada data peribadi, gaji dan maklumat
    sensitif.
-   Pengiraan payroll mesti boleh disemak, diuji dan disahkan; AI tidak
    boleh menjadi satu-satunya enjin pengiraan statutori.

### 5.3 B-Sign & Docs

-   Templat dokumen, nombor rujukan, versi, metadata dan aliran
    kelulusan.
-   Tandatangan elektronik melalui kaedah/integrasi yang sesuai.
-   Rekod siapa menandatangani, masa, versi dokumen dan jejak audit.
-   Pengurusan kontrak, surat, polisi, borang dan lampiran.
-   Kawalan akses, tempoh simpanan dan eksport.
-   Tandatangan elektronik tidak semestinya memenuhi semua kategori
    tandatangan atau formaliti undang-undang; jenis dokumen dan proses
    perlu disemak mengikut undang-undang yang terpakai.

### 5.4 B-Projects

-   Projek, papan Kanban, milestone, carta Gantt, tugasan, komen dan
    lampiran.
-   Pemilik tugasan, keutamaan, tarikh akhir dan kebergantungan.
-   Time tracking dan laporan produktiviti yang telus.
-   AI mencadangkan pecahan tugasan, risiko kelewatan dan ringkasan
    status.

### 5.5 B-Assets

-   Daftar aset, kategori, nombor siri, lokasi dan pemilik.
-   Pembelian, pelupusan, pemindahan, penyelenggaraan dan jaminan.
-   Jadual susut nilai boleh dikonfigurasi mengikut polisi perakaunan.
-   Peringatan penyelenggaraan dan laporan aset.

### 5.6 B-POS System

-   POS retail/F&B, barcode, variasi produk, modifier, diskaun dan
    refund.
-   Pelbagai kaedah pembayaran melalui integrasi yang disahkan.
-   Resit, invois, shift cashier, cash drawer dan rekonsiliasi.
-   Operasi berbilang outlet dan pemindahan stok.
-   Integrasi jualan dengan B-Inventory, B-COGS, B-Loyalty dan
    B-Finance.
-   Transaksi pembayaran perlu menggunakan integrasi payment gateway;
    sistem tidak boleh menyimpan data kad sensitif yang tidak
    diperlukan.

### 5.7 B-Site

-   Pembina laman web, landing page dan sales funnel.
-   Templat responsif, SEO asas, domain tersuai dan borang.
-   Kawalan penerbitan, draf, versi dan rollback.
-   Pengasingan aset dan domain antara tenant.

### 5.8 B-Link

-   Micro-site pautan bio, katalog, butang tindakan dan analitik klik.
-   Tema, domain/subdomain dan pautan kempen.
-   Perlindungan terhadap pautan berbahaya dan penyalahgunaan.

### 5.9 B-Marketplace

-   Storefront, katalog, troli, checkout, kupon, pesanan dan refund.
-   Pengurusan stok dan harga.
-   Penghantaran dan status pesanan.
-   Integrasi B-COGS dan B-Finance.

### 5.10 B-Affiliate

-   Pendaftaran ejen/affiliate, pautan rujukan dan atribusi jualan.
-   Peraturan komisen yang boleh dikonfigurasi.
-   Tempoh pengesahan, pembatalan komisen bagi refund dan laporan
    pembayaran.
-   Kelulusan pembayaran komisen dan jejak audit.

### 5.11 B-Loyalty

-   Profil pelanggan, mata ganjaran, tier, baucar dan promosi.
-   Peraturan perolehan/penebusan dan tarikh luput.
-   Pencegahan penyalahgunaan mata dan audit perubahan.

### 5.12 B-Omnichannel

-   Peti masuk berpusat untuk saluran yang disokong, seperti e-mel dan
    API saluran mesej.
-   Penyatuan profil pelanggan apabila dibenarkan.
-   Tugasan perbualan, tag, SLA dan templat jawapan.
-   Integrasi WhatsApp, Instagram dan saluran lain bergantung pada akses
    API rasmi, kebenaran platform dan kelulusan akaun.

### 5.13 B-Booking

-   Perkhidmatan, staf, slot, jadual, kapasiti, deposit dan pembatalan.
-   Peringatan melalui saluran yang dikonfigurasi.
-   Kalendar, zon waktu, cuti dan waktu operasi.
-   Pencegahan tempahan berganda.

### 5.14 B-Marketing

-   Segmen pelanggan, kempen, jadual hantaran dan analitik.
-   E-mel/SMS/mesej melalui penyedia yang dibenarkan.
-   Pengurusan consent, unsubscribe, suppression list dan had
    penghantaran.
-   AI boleh mencadangkan kandungan; penerbitan dan mesej pukal perlu
    mematuhi polisi dan kelulusan.

### 5.15 B-Helpdesk / B-Desk

-   Tiket, kategori, keutamaan, SLA, status dan eskalasi.
-   Tugasan kepada ejen/staf, komen dalaman dan lampiran.
-   Portal pelanggan dan pangkalan pengetahuan.
-   AI untuk cadangan jawapan dan pengelasan tiket, dengan semakan
    manusia jika perlu.

------------------------------------------------------------------------

## 6. Stok, Pengeluaran dan COGS

### 6.1 B-COGS Engine

B-COGS Engine ialah servis pengiraan kos jualan dan margin yang menjadi
sumber pengiraan kos bagi modul POS, marketplace, inventory,
manufacturing dan finance.

#### Keperluan utama

-   Kaedah kos stok boleh ditetapkan mengikut polisi tenant dan
    keperluan perakaunan: FIFO atau Weighted Average Cost, dengan kaedah
    lain hanya jika disokong secara konsisten dan dibenarkan. **LIFO
    tidak boleh diaktifkan sebagai kaedah pelaporan statutori secara
    lalai**; kesesuaian kaedah perlu disahkan dengan piawaian dan
    penasihat perakaunan yang terpakai.
-   Kos belian termasuk kos yang layak dikapitalisasikan seperti
    freight-in, insurans penghantaran dan caj berkaitan, mengikut polisi
    perakaunan.
-   Bezakan COGS daripada margin sumbangan dan keuntungan bersih.
    Payment gateway fee, komisen marketplace, penghantaran keluar,
    pemasaran, overhead dan cukai perlu direkodkan dalam kategori yang
    betul; jangan label semuanya sebagai COGS secara automatik.
-   Pengiraan margin setiap SKU, variasi, pesanan, saluran dan tempoh.
-   Amaran apabila margin atau keuntungan kasar jatuh di bawah sasaran.
-   Pelarasan kos apabila penerimaan pembelian, invois pembekal atau kos
    freight-in disahkan.
-   Pengiraan semula terkawal dengan rekod versi, sebab pelarasan dan
    kesan pada laporan.
-   Rekod kos pada masa transaksi dan jejak perubahan; jangan mengubah
    transaksi sejarah secara senyap.
-   Laporan kos sebenar berbanding kos standard dan analisis varians
    jika diperlukan.

#### Formula asas yang perlu didokumenkan

-   **Untung kasar** = Jualan bersih − COGS.
-   **Margin kasar (%)** = Untung kasar ÷ Jualan bersih × 100, jika
    jualan bersih bukan sifar.
-   **Margin sumbangan** = Jualan bersih − kos berubah yang ditetapkan.
-   **Untung bersih** = hasil − semua perbelanjaan yang diiktiraf dalam
    tempoh, termasuk COGS, operasi, kewangan dan cukai berkaitan.

Dashboard mesti menunjukkan formula dan definisi yang digunakan. Jangan
mendakwa "net margin per SKU" sebagai untung bersih sebenar jika kos
overhead, pemasaran atau kos lain belum diperuntukkan secara munasabah.

### 6.2 B-Inventory & Warehouse

-   Produk, SKU, barcode, variasi, unit ukuran dan kategori.
-   Stok tersedia, ditempah, dalam transit, rosak, kuarantin dan stok
    keselamatan.
-   Penerimaan, pengeluaran stok, pemulangan, pelarasan dan stock count.
-   Pemindahan antara gudang/outlet dengan status penghantaran dan
    penerimaan.
-   Multi-warehouse, multi-outlet dan lokasi stok dropship.
-   Batch/lot, tarikh luput dan kaedah FEFO untuk item yang sesuai.
-   Valuasi stok berdasarkan kaedah kos yang dipilih.
-   Notifikasi stok rendah, luput, perbezaan stok dan item tidak
    bergerak.
-   Setiap transaksi stok perlu mempunyai dokumen sumber, pengguna,
    masa, kuantiti, kos dan sebab.

### 6.3 B-BOM & Manufacturing

-   BOM/recipe untuk produk siap, separa siap dan kit.
-   Kuantiti bahan, unit ukuran, kadar kehilangan/wastage dan versi
    resipi.
-   Work order, penggunaan bahan, hasil pengeluaran, reject dan scrap.
-   Kos bahan, buruh langsung dan overhead pengeluaran mengikut polisi.
-   Kos standard berbanding kos sebenar dan analisis varians.
-   Auto-deduct bahan mentah hanya apabila transaksi jualan/pengeluaran
    mencapai status yang ditetapkan, dengan kawalan idempotency supaya
    stok tidak ditolak dua kali.
-   Contoh: satu burger menggunakan satu roti, 100 g daging, 10 g sos
    dan satu kotak pembungkus. Kos sebenar mesti datang daripada unit
    ukuran, kos lot dan resipi yang sah.
-   Sokongan unit conversion dengan ketepatan perpuluhan yang
    ditetapkan.

------------------------------------------------------------------------

## 7. Kewangan, Pembelian dan Logistik

### 7.1 B-Finance

-   Carta akaun, jurnal bergu, general ledger dan tempoh kewangan.
-   Akaun belum terima/bayar, bank reconciliation dan rekod pembayaran.
-   Penyata untung rugi, kunci kira-kira dan aliran tunai.
-   Posting daripada POS, inventory, procurement, invoicing, payroll dan
    manufacturing.
-   Rekonsiliasi subledger kepada general ledger.
-   Tutup tempoh, jurnal pelarasan dan kawalan transaksi selepas tutup.
-   Audit trail dan eksport laporan.
-   Semua posting perlu seimbang; transaksi kewangan tidak boleh
    dimasukkan terus oleh AI tanpa validasi dan kelulusan yang
    ditetapkan.

### 7.2 B-Invoicing

-   Quotation, sales order jika diperlukan, invois, credit note, debit
    note, resit dan bil berulang.
-   Nombor dokumen mengikut siri/aturan tenant.
-   Status draf, diluluskan, dihantar, dibayar, lewat, dibatalkan dan
    dikreditkan.
-   Medan cukai dan e-Invois boleh dikonfigurasi mengikut spesifikasi
    semasa LHDN.
-   Integrasi e-Invois hendaklah berdasarkan dokumentasi rasmi LHDN yang
    terkini, termasuk validasi, submission, UUID/status, pembatalan dan
    pengurusan ralat apabila berkenaan.
-   Jangan mendakwa integrasi atau format telah diluluskan LHDN sehingga
    diuji dan disahkan.

### 7.3 B-Procurement

-   Senarai pembekal, item, harga, tempoh kredit dan dokumen pembekal.
-   Purchase requisition, RFQ jika perlu, purchase order, goods received
    note dan supplier bill.
-   Aliran kelulusan berdasarkan jumlah dan peranan.
-   Perbandingan harga, sejarah harga dan analisis prestasi pembekal.
-   Penerimaan yang disahkan menghantar transaksi kos ke B-COGS dan
    inventory.
-   Pemisahan tugas: peminta, pelulus, penerima barang dan pembayar
    tidak semestinya individu yang sama.

### 7.4 B-Logistics

-   Penghantaran, kurier, tracking, consignment note dan status
    penghantaran.
-   Kos freight-in berbanding kos penghantaran keluar.
-   Pengagihan kos penghantaran kepada item mengikut polisi yang
    didokumenkan.
-   Pengiraan kos logistik ke laporan margin tanpa mengira kos yang sama
    dua kali.
-   Integrasi kurier melalui API/adapter dan pengendalian kegagalan.

------------------------------------------------------------------------

## 8. B-Connect / API Gateway

-   API REST berversi, autentikasi, kebenaran, rate limit dan audit.
-   Webhook bertandatangan dengan retry, idempotency dan log
    penghantaran.
-   Adapter untuk payment gateway, e-mel, SMS, kurier, platform
    e-dagang, bank atau sistem luaran yang mempunyai API sah.
-   API key disimpan dalam bentuk hash apabila sesuai; rahsia integrasi
    disulitkan dan hanya didedahkan sekali jika reka bentuk
    memerlukannya.
-   Dokumentasi OpenAPI/Swagger dan contoh permintaan.
-   Pengurusan webhook tenant-specific dan pilihan replay yang terkawal.
-   Jangan menyimpan API key dalam kod frontend, repositori awam atau
    log.

------------------------------------------------------------------------

## 9. B-Auth & Security

### 9.1 Identiti dan sesi

-   E-mel/telefon yang disahkan mengikut kaedah yang dipilih.
-   Kata laluan yang di-hash dengan algoritma moden yang sesuai.
-   Reset kata laluan dengan token sekali guna dan tamat tempoh.
-   MFA/2FA untuk pentadbir dan pilihan untuk peranan lain.
-   Pengurusan sesi, logout semua peranti dan revokasi token.
-   SSO hanya melalui protokol dan penyedia yang disokong serta
    dikonfigurasi dengan betul.

### 9.2 RBAC dan pengasingan tenant

-   Peranan contoh: platform_super_admin, tenant_owner, tenant_admin,
    finance_manager, hr_manager, inventory_manager, cashier, staff,
    auditor dan customer.
-   Kebenaran diperincikan mengikut modul, tindakan, lokasi dan rekod.
-   Semua rekod tenant-owned mempunyai tenant_id/organization_id atau
    hubungan skop tenant yang jelas.
-   Backend menentukan tenant daripada sesi/token yang disahkan, bukan
    menerima tenant_id bebas daripada browser sebagai bukti kebenaran.
-   Query, job, eksport, fail, cache, carian dan webhook mesti
    menggunakan skop tenant.
-   Ujian wajib memastikan pengguna tenant A tidak boleh membaca,
    mengubah, memadam atau mengeksport data tenant B.
-   Super Admin menggunakan akses istimewa yang minimum, diaudit dan
    boleh disekat.

### 9.3 Perlindungan data

-   HTTPS, pengurusan rahsia, validasi input, CSRF/XSS/SQL injection
    protections, upload scanning/validation dan kawalan MIME/saiz.
-   Penyulitan data sensitif ketika disimpan dan dihantar jika sesuai.
-   Polisi penyimpanan, arkib, pemadaman dan pemulihan.
-   Audit log dilindungi daripada pengubahan biasa.
-   Sandaran pangkalan data dan fail, ujian restore berkala serta pelan
    pemulihan bencana.
-   Patuhi keperluan undang-undang perlindungan data Malaysia yang
    terpakai, termasuk PDPA jika berkenaan; dapatkan semakan
    undang-undang bagi aliran data, persetujuan, pemprosesan, notis
    privasi, retention dan pemindahan rentas sempadan.

------------------------------------------------------------------------

## 10. B-Brain Hub --- AI Engine

### 10.1 Fungsi

-   Ejen AI khusus mengikut tenant dan peranan: Admin Assistant, Sales
    Analyst, Inventory Planner, COGS Analyst, HR Assistant, Document
    Assistant, Project Coordinator dan Customer Support Assistant.
-   Analisis laporan, ringkasan, ramalan permintaan dan amaran anomali.
-   Draf e-mel, dokumen, tugasan, SOP dan jawapan tiket.
-   Cadangan pesanan belian berdasarkan jualan, lead time dan stok
    keselamatan.
-   Penerangan tentang perubahan margin dan varians kos.
-   Carian pengetahuan berasaskan dokumen tenant dengan kawalan akses.
-   Jadual automasi dan tugasan latar belakang yang boleh dipantau.

### 10.2 Penyedia model

-   Sokong API AI luar melalui adapter, contohnya OpenRouter atau
    penyedia lain yang dikonfigurasi.
-   API key hanya berada di backend/secret store.
-   Model, had token, had belanja, timeout, retry dan fallback boleh
    ditetapkan.
-   Rekod penggunaan dan kos mengikut tenant, pengguna, ejen dan
    tugasan.
-   Redaksi data sensitif sebelum dihantar kepada penyedia luar apabila
    diperlukan.
-   Tenant boleh menetapkan sama ada kandungan tertentu dibenarkan
    dihantar kepada AI luar.
-   Jika AI tidak tersedia, modul perniagaan teras masih boleh
    digunakan.

### 10.3 Kawalan tindakan AI

AI tidak boleh melakukan tindakan kritikal secara bebas hanya
berdasarkan arahan teks. Gunakan aliran:

1.  AI menyediakan cadangan atau draf.
2.  Sistem memeriksa kebenaran pengguna, skop tenant dan peraturan
    perniagaan.
3.  Tindakan berisiko rendah boleh berjalan secara automatik jika
    dibenarkan.
4.  Tindakan kewangan, payroll, pemadaman, perubahan kebenaran, bayaran,
    refund, pembelian dan posting jurnal memerlukan kelulusan yang
    ditetapkan.
5.  Sistem merekod input yang relevan, cadangan, kelulusan, tindakan,
    hasil dan ralat tanpa menyimpan rahsia yang tidak diperlukan.
6.  Sediakan butang berhenti, pembatalan tugasan yang belum dijalankan
    dan had penggunaan.

AI tidak boleh mencipta transaksi palsu, memintas RBAC, menyamar sebagai
pelulus atau mengubah audit log.

------------------------------------------------------------------------

## 11. Dokumen Rasmi, Templat Malaysia dan Eksport Pelbagai Format

### 11.1 Keperluan umum

Setiap dokumen yang relevan perlu mempunyai butang **Pratonton**, **Muat
Turun**, **Cetak**, **Hantar** dan, jika sesuai, **Versi/Sejarah**.

Format yang disokong bergantung pada jenis dokumen:

-   **PDF:** salinan rasmi untuk edaran/cetakan, dengan nombor halaman,
    tarikh, identiti organisasi dan ruang tandatangan jika sesuai.
-   **DOCX:** dokumen yang boleh disunting menggunakan aplikasi serasi
    Microsoft Word.
-   **XLSX:** helaian data dan laporan berstruktur.
-   **CSV:** eksport data tabular untuk analisis/import.
-   **ODT/ODS:** format OpenDocument jika dilaksanakan.
-   **HTML:** pratonton atau cetakan melalui browser.
-   **TXT/JSON/XML:** untuk data teknikal atau integrasi apabila sesuai.
-   **PNG/JPG:** hanya untuk dokumen visual tertentu seperti resit atau
    poster, bukan pengganti fail sumber rasmi.

Jangan menjanjikan setiap dokumen tersedia dalam semua format jika
format itu tidak sesuai. Slip gaji, sebagai contoh, boleh dikeluarkan
sebagai PDF dan DOCX; laporan payroll boleh ditawarkan sebagai XLSX/CSV
dengan akses yang terhad.

### 11.2 Pengurusan templat dan tema

-   Tema neutral, korporat, moden dan ringkas.
-   Tema boleh dikonfigurasi mengikut logo, warna, font, margin, header,
    footer dan susun atur.
-   Templat berasingan mengikut jenis dokumen, bahasa dan versi.
-   Pilihan Bahasa Melayu atau Inggeris.
-   Sokong format A4 dan saiz kertas lain yang diperlukan.
-   Tetapan nombor dokumen, tarikh, nombor halaman, ruangan tandatangan
    dan cop jika berkenaan.
-   Simpan versi templat dan rekod versi yang digunakan bagi dokumen
    yang telah dikeluarkan.
-   Jangan benarkan perubahan templat semasa mengubah salinan dokumen
    lama tanpa jejak versi.
-   Data peribadi dan gaji hanya boleh dimuat turun oleh individu yang
    dibenarkan.
-   Fail eksport menggunakan nama fail yang konsisten, contohnya
    `Slip-Gaji_2026-09_Pekerja-XXXX.pdf`; elakkan mendedahkan nombor
    pengenalan penuh dalam nama fail.

### 11.3 Pustaka dokumen sebenar yang perlu disediakan

**HR & pekerjaan** - Surat tawaran kerja. - Kontrak/perjanjian pekerjaan
dengan medan dan klausa yang boleh dikonfigurasi. - Surat pengesahan
jawatan. - Surat kenaikan pangkat, perubahan gaji dan perubahan
syarat. - Surat amaran dan surat tunjuk sebab. - Surat
kelulusan/penolakan cuti. - Slip gaji. - Ringkasan payroll dan rekod
pembayaran. - Borang tuntutan dan kelulusan tuntutan. - Surat penerimaan
peletakan jawatan dan pengesahan perkhidmatan. - Rekod kehadiran dan
penyata cuti.

**Kewangan & jualan** - Quotation. - Purchase order. - Sales order jika
digunakan. - Invois, resit, credit note dan debit note. - Penyata
pelanggan dan pembekal. - Baucar pembayaran/penerimaan. - Rekonsiliasi
bank. - Laporan untung rugi, kunci kira-kira dan aliran tunai. - Laporan
valuasi stok dan COGS.

**Operasi** - Goods received note. - Stock transfer note. - Stock
adjustment form. - Stock count sheet. - Work order, BOM/recipe sheet dan
laporan pengeluaran. - Delivery order, consignment note dan bukti
penghantaran. - Borang penyelenggaraan aset. - Minit mesyuarat, laporan
projek, SOP dan borang audit.

**Undang-undang/korporat** - NDA dan perjanjian kerahsiaan. - Perjanjian
perkhidmatan. - Surat kuasa atau surat pengesahan yang sesuai. - Polisi
privasi, terma penggunaan dan polisi dalaman. - Minit/resolusi atau
dokumen korporat hanya jika sesuai dengan bentuk entiti dan proses yang
digunakan.

Dokumen di atas ialah pustaka templat produk, bukan pengesahan bahawa
setiap templat sesuai untuk semua situasi. Templat kontrak, surat
pekerjaan, polisi privasi, dokumen cukai dan dokumen statutori perlu
disemak oleh profesional yang layak sebelum digunakan secara rasmi.

### 11.4 Malaysia-ready: keperluan pematuhan

Sediakan konfigurasi dan templat yang boleh dikemas kini apabila
peraturan berubah. Jangan hard-code kadar, ambang, tarikh kuat kuasa
atau klausa undang-undang tanpa versi dan sumber.

-   **Payroll:** struktur gaji, elaun, potongan dan caruman berkaitan
    seperti KWSP, PERKESO, SIP dan PCB mengikut kelayakan pekerja,
    status dan peraturan semasa.
-   **Employment:** templat dan aliran kerja mengambil kira Akta Kerja
    1955 dan undang-undang buruh lain yang terpakai mengikut lokasi,
    kategori pekerja dan keadaan.
-   **Tax/e-Invoice:** medan, status dan integrasi berdasarkan
    spesifikasi semasa LHDN.
-   **PDPA/privacy:** notis privasi, tujuan pemprosesan, hak
    akses/pembetulan, kawalan akses, retention dan pengurusan insiden
    mengikut keperluan yang terpakai.
-   **Accounting:** carta akaun, cukai dan laporan perlu boleh
    disesuaikan dengan entiti dan piawaian yang terpakai.
-   **Electronic signature:** dokumentasikan kaedah pengesahan,
    persetujuan, integriti dokumen dan jejak audit; kenal pasti dokumen
    yang memerlukan formaliti khas.
-   **Records:** konfigurasi tempoh penyimpanan mengikut jenis rekod dan
    nasihat profesional yang berkenaan.

Setiap dokumen statutori perlu mempunyai tarikh semakan, versi templat,
pemilik pematuhan dan pautan/sumber rujukan dalaman. Semakan
undang-undang dan perakaunan mesti dibuat sebelum mendakwa sistem
"mematuhi sepenuhnya" sesuatu akta atau agensi.

### 11.5 Keperluan khusus slip gaji

Slip gaji hendaklah mengandungi medan yang sesuai, tertakluk pada
keperluan undang-undang dan konfigurasi majikan:

-   Nama dan maklumat organisasi.
-   Nama pekerja dan ID pekerja dalaman.
-   Jawatan/jabatan jika diperlukan.
-   Tempoh gaji dan tarikh pembayaran.
-   Gaji asas, elaun, bayaran lebih masa dan pendapatan lain.
-   Potongan yang berkaitan, dengan pecahan jelas.
-   Caruman pekerja dan majikan yang berkaitan.
-   Jumlah pendapatan kasar, jumlah potongan dan gaji bersih.
-   Catatan atau rujukan pembayaran jika sesuai.
-   Tarikh dijana, nombor rujukan dan versi templat.

Slip gaji mesti mempunyai kawalan akses peribadi, log muat turun yang
munasabah, pautan muat turun bertoken/tamat tempoh jika dihantar, dan
tiada akses silang pekerja. PDF ialah format utama untuk edaran; DOCX
boleh disediakan jika diperlukan. XLSX/CSV hendaklah digunakan untuk
laporan payroll berizin, bukan sebagai pautan awam kepada slip individu.

------------------------------------------------------------------------

## 12. B-Analytics

-   Dashboard jualan, hasil bersih, COGS, untung kasar dan margin.
-   Prestasi mengikut SKU, outlet, saluran, staf dan tempoh.
-   Nilai stok, stok perlahan, stok luput dan ramalan kehabisan stok.
-   Prestasi pembekal, perbezaan kos dan varians pengeluaran.
-   Payroll dan KPI HR hanya untuk peranan yang dibenarkan.
-   Aliran tunai, akaun belum terima/bayar dan trend kewangan.
-   Penapis tarikh, lokasi, produk dan kategori.
-   Eksport PDF/XLSX/CSV mengikut kebenaran.
-   Definisi metrik didokumenkan supaya angka antara modul konsisten.

------------------------------------------------------------------------

## 13. Seni Bina Teknikal yang Dicadangkan

Stack akhir mesti dipilih selepas pemeriksaan kod sedia ada dan had
hosting. Cadangan asas yang sesuai untuk aplikasi web PHP/cPanel:

-   **Backend:** Laravel atau rangka kerja PHP matang yang sesuai dengan
    versi hosting.
-   **Frontend:** UI responsif menggunakan teknologi yang boleh dibina
    dan dihoskan pada cPanel; boleh menggunakan frontend yang sama atau
    aplikasi SPA berasingan.
-   **Database:** MySQL/MariaDB jika itu yang disokong oleh hosting;
    gunakan skema yang direka untuk transaksi dan pengasingan tenant.
-   **Queue:** queue database dan cron untuk hosting terhad;
    Redis/worker boleh digunakan apabila disokong.
-   **Fail:** storan peribadi di luar direktori awam jika boleh, atau
    storan objek yang serasi; muat turun melalui endpoint yang
    mengesahkan kebenaran.
-   **AI:** adapter backend ke penyedia model luaran.
-   **Dokumen:** pustaka penjana PDF/DOCX/XLSX yang sesuai, dengan
    templat berversi.
-   **API:** REST berversi, OpenAPI dan webhook.
-   **Deployment:** fail konfigurasi environment, migration, seed data
    minimum, skrip pemasangan dan arahan rollback.

Jangan menambah servis yang tidak diperlukan pada peringkat awal. Modul
yang banyak perlu dibina secara modular dalam satu aplikasi dahulu,
bukannya memecahkan semuanya kepada microservices sebelum skala
memerlukannya.

### 13.1 Lapisan sistem

1.  Presentation layer: portal Super Admin, portal tenant, portal
    staf/pelanggan dan storefront.
2.  Application layer: modul perniagaan dan use case.
3.  Domain layer: peraturan COGS, inventori, payroll, kewangan dan
    kelulusan.
4.  Integration layer: API gateway, AI provider adapters, payment, e-mel
    dan kurier.
5.  Data layer: pangkalan data, storan dokumen, audit log dan queue.
6.  Operations layer: logging, monitoring, backup, restore dan
    deployment.

### 13.2 Contoh struktur direktori

``` text
ai-bijan/
├── app/
│   ├── Modules/
│   │   ├── Auth/
│   │   ├── Tenancy/
│   │   ├── Admin/
│   │   ├── HRMS/
│   │   ├── Documents/
│   │   ├── POS/
│   │   ├── Inventory/
│   │   ├── COGS/
│   │   ├── Manufacturing/
│   │   ├── Finance/
│   │   ├── Procurement/
│   │   ├── Marketing/
│   │   ├── Booking/
│   │   ├── Helpdesk/
│   │   ├── Analytics/
│   │   └── BrainHub/
│   ├── Policies/
│   ├── Jobs/
│   ├── Events/
│   └── Services/
├── routes/
├── resources/
│   ├── views/
│   ├── frontend/
│   └── document-templates/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── storage/
├── tests/
├── docs/
├── public/
├── .env.example
├── composer.json
└── README.md
```

Struktur ini ialah contoh logik, bukan kewajipan untuk menggunakan nama
folder yang sama.

------------------------------------------------------------------------

## 14. Reka Bentuk Multi-Tenant

### 14.1 Model tenancy

Untuk versi awal, pendekatan **shared database dengan tenant_id** boleh
digunakan jika setiap jadual tenant-owned mempunyai skop tenant yang
jelas dan semua akses dipaksa melalui middleware/policy/repository yang
selamat. Untuk pelanggan yang memerlukan pemisahan lebih tinggi, reka
bentuk boleh berkembang kepada pangkalan data berasingan.

### 14.2 Entiti teras

-   tenants/organizations
-   users
-   tenant_memberships
-   roles
-   permissions
-   subscriptions/plans
-   tenant_module_entitlements
-   audit_logs
-   files/documents
-   integrations
-   ai_usage_records
-   ai_agents
-   ai_tasks
-   notifications
-   approval_requests

Setiap modul menambah jadual domainnya sendiri. Foreign key, indeks,
unique constraint dan transaksi perlu direka untuk mengelakkan rekod
tenant bercampur.

### 14.3 Ujian isolasi wajib

-   Tenant A tidak dapat membuka ID rekod milik tenant B.
-   Penggantian ID dalam URL/API tidak membolehkan akses rentas tenant.
-   Eksport dan carian tidak mengandungi data tenant lain.
-   Fail dan pautan muat turun tidak boleh diakses tanpa kebenaran.
-   Queue/job menyimpan tenant context dan mengesahkan semula akses
    ketika berjalan.
-   Cache key memasukkan tenant context apabila berkaitan.
-   Webhook dan integrasi dipadankan kepada tenant yang betul.
-   Ujian ini dijalankan secara automatik dalam CI sebelum release.

------------------------------------------------------------------------

## 15. Pangkalan Data dan Integriti Transaksi

-   Gunakan migration berversi dan boleh diuji.
-   Gunakan transaksi database bagi jualan, pembayaran, stock deduction,
    manufacturing posting dan jurnal kewangan.
-   Gunakan idempotency key untuk permintaan berulang daripada payment
    gateway, webhook dan job.
-   Jangan menghapus transaksi kewangan/stok yang telah dipos secara
    fizikal; gunakan reversal/correction yang boleh diaudit.
-   Simpan nombor dokumen, status, cap masa dan sumber transaksi.
-   Gunakan decimal/fixed precision untuk wang dan kuantiti; elakkan
    floating-point bagi pengiraan kewangan.
-   Simpan timezone secara konsisten dan paparkan masa tempatan mengikut
    tetapan tenant.
-   Tetapkan polisi pembundaran yang jelas bagi cukai, gaji, unit ukuran
    dan mata wang.
-   Uji concurrency bagi stok, checkout dan pembayaran untuk mencegah
    overselling atau posting berganda.

------------------------------------------------------------------------

## 16. Langganan SaaS dan Pengebilan Platform

-   Pelan percuma/trial dan pelan berbayar jika dikehendaki.
-   Had pengguna, lokasi, modul, storan, panggilan API dan penggunaan
    AI.
-   Status langganan: trial, active, past_due, suspended dan cancelled.
-   Rekod invois langganan, pembayaran dan sejarah perubahan pelan.
-   Grace period dan proses penggantungan yang telus.
-   Tenant tidak boleh menaikkan had sendiri dengan mengubah request
    frontend.
-   Penggunaan AI dicaj berdasarkan meter yang direkodkan server-side.
-   Sediakan eksport data dan proses penamatan tenant yang mempunyai
    semakan kebenaran serta polisi retention.

Integrasi gateway dan kaedah cukai untuk langganan hendaklah dipilih
berdasarkan keperluan perniagaan sebenar dan ketersediaan penyedia.

------------------------------------------------------------------------

## 17. Deployment pada cPanel

### 17.1 Pra-syarat untuk disahkan

-   Versi PHP yang serasi dengan framework dan dependency.
-   Extension PHP yang diperlukan.
-   MySQL/MariaDB dan versi yang disokong.
-   Composer atau kaedah build di luar server.
-   Cron Jobs.
-   SSL/HTTPS.
-   Had CPU, RAM, proses, execution time dan saiz fail.
-   Akses outbound HTTPS untuk API AI dan integrasi.
-   Storan peribadi dan permission fail yang betul.
-   Sokongan untuk queue/worker atau alternatif cron.
-   Polisi hosting bagi aplikasi SaaS, e-mel dan penggunaan sumber.

### 17.2 Proses pemasangan

1.  Sediakan domain/subdomain dan SSL.
2.  Upload release yang telah dibina, tanpa `.env`, credential atau fail
    development.
3.  Letakkan document root pada direktori `public` jika hosting
    membenarkan.
4.  Cipta database dan user dengan permission minimum.
5.  Tetapkan environment variables melalui kaedah selamat.
6.  Jalankan migration menggunakan arahan deployment yang didokumenkan.
7.  Cipta Super Admin pertama melalui installer/CLI selamat; jangan
    gunakan kata laluan lalai.
8.  Tetapkan cron untuk scheduler dan queue jika digunakan.
9.  Uji e-mel, fail, backup, restore dan integrasi.
10. Jalankan security checklist dan ujian pengasingan tenant.
11. Aktifkan production mode, matikan debug dan semak permission.
12. Simpan release sebelumnya untuk rollback.

### 17.3 Keperluan keselamatan deployment

-   Jangan meletakkan `.env`, backup SQL, log, fail eksport atau dokumen
    peribadi dalam direktori awam.
-   Jangan beri permission `777` sebagai penyelesaian umum.
-   Jangan paparkan stack trace kepada pengguna.
-   Rahsia produksi tidak boleh dimasukkan dalam Git atau ZIP yang
    dikongsi.
-   Sediakan prosedur rotate API key, reset akses dan respons insiden.
-   Pastikan backup disimpan berasingan daripada server produksi.

------------------------------------------------------------------------

## 18. Ujian, QA dan Kriteria Penerimaan

### 18.1 Ujian fungsi

-   Login, reset password, 2FA dan RBAC.
-   Tenant creation, subscription dan module entitlements.
-   POS checkout, refund dan resit.
-   Stock-in/out, transfer, batch/expiry dan stock count.
-   FIFO/Weighted Average Cost dengan set ujian yang diketahui.
-   BOM, work order, penggunaan bahan dan kos pengeluaran.
-   Posting jurnal dan rekonsiliasi kewangan.
-   Payroll, slip gaji dan akses dokumen.
-   Penjanaan PDF/DOCX/XLSX/CSV mengikut jenis dokumen.
-   AI task, kuota, timeout, fallback dan approval workflow.
-   Integrasi webhook, retry dan idempotency.

### 18.2 Ujian keselamatan

-   Ujian akses silang tenant.
-   SQL injection, XSS, CSRF, upload berbahaya dan authorization bypass.
-   Rate limiting dan perlindungan brute force.
-   Ujian muat turun fail tanpa kebenaran.
-   Semakan kebocoran API key dalam frontend/log.
-   Semakan audit log dan akses Super Admin.
-   Ujian pemulihan backup.

### 18.3 Kriteria "siap untuk produksi"

Sistem tidak boleh dianggap production-ready hanya kerana halaman boleh
dibuka. Ia mesti mempunyai ujian automatik, log, pengendalian ralat,
migrasi, backup/restore yang diuji, dokumentasi deployment, pengasingan
tenant yang diuji, polisi akses, pengurusan rahsia, pemantauan dan
semakan keselamatan.

------------------------------------------------------------------------

## 19. Pelan Pembangunan Berfasa

### Fasa 0 --- Audit dan spesifikasi

-   Periksa semua ZIP/kod sedia ada yang akan digunakan.
-   Kenal pasti dependency, lesen, stack, skema dan fungsi yang boleh
    dikekalkan.
-   Keluarkan semua kebergantungan ERPNext yang tidak dibenarkan.
-   Sahkan had cPanel dan pilih stack.
-   Hasil: laporan audit, seni bina, skema awal, backlog dan pelan
    migrasi.

### Fasa 1 --- Platform teras

-   Authentication, tenancy, RBAC, tenant settings dan audit logs.
-   Super Admin, onboarding tenant, pengguna dan peranan.
-   Upload/download fail secara selamat.
-   Logging, konfigurasi, migration, backup dan deployment asas.
-   Hasil: asas SaaS boleh diuji dengan tenant berasingan.

### Fasa 2 --- Dokumen dan HRMS asas

-   Template engine dan eksport PDF/DOCX/XLSX/CSV mengikut dokumen.
-   Profil pekerja, kehadiran, cuti dan slip gaji asas.
-   Payroll hanya selepas formula statutori dan konfigurasi disahkan.
-   Hasil: dokumen dan HR flow dengan kawalan akses.

### Fasa 3 --- POS, Inventory dan COGS

-   Produk, SKU, transaksi POS, stock ledger dan multi-location.
-   B-COGS dengan FIFO/Weighted Average Cost.
-   Penerimaan pembelian, kos freight-in dan amaran margin.
-   Hasil: jualan, stok dan COGS boleh direkonsiliasi.

### Fasa 4 --- Finance, Procurement dan Manufacturing

-   Carta akaun, jurnal, laporan kewangan dan subledger.
-   PO, penerimaan barang, supplier bill dan approval.
-   BOM, work order, kos buruh/overhead dan varians.
-   Hasil: transaksi operasi dipadankan dengan kewangan.

### Fasa 5 --- Sales, customer dan operations

-   B-Marketplace, B-Site, B-Link, B-Booking, B-Loyalty, B-Affiliate.
-   B-Projects, B-Assets, B-Helpdesk, B-Omnichannel dan B-Marketing.
-   Hasil: peluasan modul selepas platform teras stabil.

### Fasa 6 --- B-Brain Hub dan automasi

-   AI provider adapters, ejen per tenant, kuota, penggunaan dan kos.
-   RAG/carían dokumen berasaskan akses tenant jika diperlukan.
-   AI task queue, kelulusan dan audit tindakan.
-   Hasil: automasi terkawal dan boleh dipantau.

### Fasa 7 --- Hardening dan skala

-   Ujian beban, pemantauan, backup/restore, pentest dan penambahbaikan
    UX.
-   Persediaan migrasi ke VPS/cloud jika had cPanel dicapai.
-   Hasil: release yang dinilai berdasarkan kriteria penerimaan.

------------------------------------------------------------------------

## 20. Deliverables yang Wajib Disediakan

1.  Source code lengkap AI BIJAN.
2.  Backend Super Admin.
3.  Backend tenant SaaS.
4.  Frontend pemilik, pengurus, staf dan pelanggan mengikut skop.
5.  Modul dan database migrations.
6.  `.env.example` tanpa rahsia.
7.  Installer atau arahan pemasangan.
8.  Dokumentasi deployment cPanel.
9.  Dokumentasi API/OpenAPI.
10. Dokumentasi RBAC dan tenant isolation.
11. Template dokumen Malaysia dengan versi dan bahasa.
12. Eksport dokumen mengikut format yang sesuai.
13. Test suite dan laporan ujian.
14. Sample data/demo yang tidak mengandungi data sebenar.
15. Backup/restore guide dan rollback guide.
16. Panduan Super Admin, pemilik tenant dan staf.
17. Senarai dependency dan lesen.
18. Senarai integrasi luar, keperluan credential dan status ujian.
19. Senarai ciri yang siap, belum siap dan mempunyai had.
20. ZIP release yang boleh dipasang, hanya selepas dibina dan diuji pada
    persekitaran sasaran.

Jangan melabelkan modul sebagai "siap" jika ia hanya mockup, butang
kosong, data contoh statik atau API yang belum disambungkan.

------------------------------------------------------------------------

## 21. Keperluan UI/UX

-   Antaramuka premium, moden, responsif dan mudah digunakan pada
    telefon.
-   Navigasi modul berdasarkan role dan entitlement tenant.
-   Dashboard yang membezakan metrik sebenar, anggaran dan ramalan AI.
-   Status transaksi jelas: draf, menunggu kelulusan, berjaya, gagal,
    dibatalkan atau dipulangkan.
-   Borang dengan validasi, mesej ralat yang boleh difahami dan
    pengesahan tindakan kritikal.
-   Bahasa Melayu dan Inggeris; reka bentuk sedia untuk bahasa tambahan.
-   Aksesibiliti asas: label input, kontras, navigasi keyboard dan mesej
    status.
-   Tema dokumen tidak semestinya sama dengan tema dashboard;
    kedua-duanya boleh dikonfigurasi secara berasingan.

------------------------------------------------------------------------

## 22. Risiko dan Mitigasi

  -----------------------------------------------------------------------
  Risiko                              Mitigasi
  ----------------------------------- -----------------------------------
  Data tenant bercampur               Tenant scoping di backend, policies
                                      dan ujian isolasi automatik

  Kos AI tidak terkawal               Kuota, had belanja, rate limit dan
                                      laporan penggunaan

  AI melakukan tindakan salah         Approval workflow, allowlist tools,
                                      audit dan rollback

  COGS tidak tepat                    Stock ledger, polisi kos berversi,
                                      ujian rujukan dan rekonsiliasi

  Payroll/statutori berubah           Konfigurasi berversi, sumber rasmi
                                      dan semakan profesional

  cPanel tidak menyokong worker       Cron/queue alternatif atau pindah
                                      ke VPS serasi

  Eksport membocorkan data            Authorization, fail peribadi,
                                      pautan bertoken dan expiry

  Transaksi berganda                  Idempotency, transaksi DB dan
                                      kawalan concurrency

  Integrasi luar gagal                Retry terkawal, dead-letter/log,
                                      status dan manual recovery

  Skop terlalu besar                  Pembangunan berfasa dan kriteria
                                      penerimaan yang jelas
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## 23. Takrif Selesai (Definition of Done)

Satu modul dianggap selesai apabila:

-   Fungsi sebenar dibina dan tidak sekadar paparan demo.
-   Pengguna, role dan tenant scope disahkan di backend.
-   Validasi, pengendalian ralat dan audit trail tersedia.
-   Migration dan rollback yang sesuai disediakan.
-   Ujian unit/integrasi dan ujian keselamatan berkaitan lulus.
-   Eksport/import yang dijanjikan diuji.
-   Dokumentasi pengguna dan teknikal dikemas kini.
-   Integrasi luar diuji atau dilabel jelas sebagai belum dikonfigurasi.
-   Tiada kebergantungan ERPNext diperlukan untuk menjalankan fungsi
    tersebut.
-   Status release dan had yang diketahui direkodkan.

------------------------------------------------------------------------

## 24. Arahan Induk untuk Pasukan Pembangunan / AI Coding Agent

Gunakan arahan berikut sebagai ringkasan pelaksanaan:

> Bangunkan **AI BIJAN SaaS** sebagai platform perniagaan stand-alone,
> multi-tenant dan AI-enabled. ERPNext tidak boleh digunakan sebagai
> runtime, backend, pangkalan data aplikasi atau dependency. Jangan
> menyalin kod proprietari atau aset berlesen tanpa kebenaran; bina
> implementasi BIJAN sendiri berdasarkan keperluan fungsian dalam
> dokumen ini.
>
> Mulakan dengan audit kod dan hosting, kemudian bina platform teras:
> authentication, tenancy, RBAC, audit log, konfigurasi dan pengurusan
> fail. Seterusnya bangunkan Super Admin, portal tenant dan sistem
> dokumen. Selepas pengasingan tenant dan asas keselamatan lulus ujian,
> bina POS, Inventory, COGS, Finance dan Procurement. Tambah
> BOM/Manufacturing, HRMS, modul jualan/pelanggan dan B-Brain Hub secara
> berfasa.
>
> Semua data mesti diasingkan mengikut tenant di backend. Jangan percaya
> tenant_id yang dihantar dari frontend. Semua tindakan kewangan,
> payroll, pemadaman, perubahan akses dan transaksi kritikal perlu
> mengikuti approval policy. API key AI hanya berada di server. Sediakan
> eksport dokumen PDF, DOCX, XLSX, CSV dan format lain apabila sesuai.
> Sediakan templat Malaysia yang berversi dan boleh dikonfigurasi,
> tetapi jangan dakwa pematuhan undang-undang tanpa pengesahan terhadap
> sumber rasmi semasa dan semakan profesional.
>
> Sasarkan pemasangan cPanel hanya selepas mengesahkan keserasian
> hosting. Sertakan source code, migration, `.env.example`, panduan
> pemasangan, dokumentasi API, ujian, backup/restore guide dan ZIP
> release. Jangan mendakwa projek siap atau production-ready sehingga
> kriteria penerimaan dalam dokumen ini lulus.

------------------------------------------------------------------------

## 25. Keputusan yang Perlu Dikunci Sebelum Pembangunan Penuh

Perkara berikut boleh dimuktamadkan selepas audit teknikal, tanpa
mengubah prinsip wajib dokumen ini:

-   Stack akhir berdasarkan codebase dan versi cPanel.
-   MySQL/MariaDB atau pangkalan data lain yang benar-benar disokong.
-   Strategi queue dan scheduler.
-   Kaedah tenant isolation dan pilihan pangkalan data berasingan untuk
    pelanggan besar.
-   Penyedia payment gateway, e-mel, SMS, kurier dan AI.
-   Pelan SaaS, had penggunaan dan harga.
-   Kaedah tandatangan elektronik.
-   Kaedah pematuhan e-Invois dan tarikh pelaksanaan yang terpakai.
-   Butiran payroll/statutori berdasarkan sumber rasmi terkini.
-   Modul yang masuk MVP berbanding fasa seterusnya.

**Prinsip penutup:** AI BIJAN mesti menjadi sistem sendiri, bukan kulit
hadapan kepada ERPNext. Pengiraan kewangan dan stok mesti deterministik
serta boleh diaudit; AI membantu analisis dan automasi dalam sempadan
kebenaran. Semua dokumen rasmi mesti mempunyai versi, kawalan akses dan
format eksport yang sesuai. Pematuhan Malaysia perlu dikekalkan melalui
konfigurasi berversi, sumber rasmi dan semakan profesional.
