## 10. Audit Odoo: 50 app rasmi & reverse engineering

**Sumber (disemak 31/10/2026):**
- Senarai rasmi app: [odoo.com/page/all-apps](https://www.odoo.com/page/all-apps), 50 app dalam 9 kategori.
- Ciri terkini: [Odoo 19 Release Notes (Sept 2025)](https://www.odoo.com/odoo-19-release-notes) dan [Odoo 20 Release Notes (Sept 2026)](https://www.odoo.com/odoo-20-release-notes).
- Harga: [odoo.com/pricing](https://www.odoo.com/pricing). Standard € 19.90–24.90/pengguna/bulan; Custom € 35.90–44.90/pengguna/bulan (Studio, **Agentic AI**, multi-company, external API). Ciri AI memerlukan **kredit IAP** berasingan.

Lajur "AI Bijan sekarang" diambil daripada §3 (status kod). "Jadikan lebih power" ialah cadangan reverse engineering: ambil apa yang Odoo dah buktikan, kemudian tambah apa yang Odoo **tiada** (ejen AI yang bertindak dengan kelulusan, pematuhan Malaysia, WhatsApp-first, migrasi).

### 10.1 Pelajaran utama daripada Odoo

1. **Satu platform, banyak app, satu data.** Odoo menang kerana semua app berkongsi pelanggan, produk dan perakaunan. AI Bijan dah ikut model ni (modular monolith); kekalkan dan jangan pecah.
2. **"One App Free" sebagai pintu masuk.** Pelanggan mula dengan satu app, kemudian bertambah. AI Bijan boleh tiru melalui Super Admin (§2, keputusan harga): pakej satu modul percuma atau murah, contohnya POS sahaja.
3. **AI kini ciri asas, bukan tambahan.** Odoo 20 ada ejen yang cipta dan kemas kini rekod, MCP, *skills*, pemilihan model automatik dan suara. **Bezanya:** di Odoo, Agentic AI hanya dalam pakej Custom dan kredit dibayar berasingan. Di AI Bijan, ejen patut jadi teras dalam semua pakej, dengan dompet kredit yang telus (CORE-005).
4. **Lokalisasi menjual.** Odoo 20 tambah QRIS (Indonesia), FBR (Pakistan), SUNAT (Peru), GoFood/GrabFood. AI Bijan perlu lokalisasi Malaysia: MyInvois, DuitNow, FPX, EPF/SOCSO/EIS/PCB, SST dan GrabFood/Foodpanda.
5. **Studio (tanpa kod) mengunci pelanggan.** Pelanggan yang dah ubah medan dan aliran kerja susah nak pindah. AI Bijan: WorkflowStudio + AgentStudio, dan biar ejen AI yang bina perubahan tu daripada ayat biasa.
6. **Kelemahan Odoo yang jadi peluang AI Bijan:** pelaksanaan mahal (perlukan rakan/konsultan), UI padat untuk pengguna bukan teknikal, AI bergantung pada kredit IAP, lokalisasi MY lemah, dan WhatsApp sebagai saluran sampingan.

### 10.2 Jadual audit 50 app

| # | Kategori | App Odoo | Ciri utama Odoo (19/20) | AI Bijan sekarang (ID · modul · status) | Jurang | Jadikan lebih power (AI Bijan) | Keutamaan |
|---|---|---|---|---|---|---|---|
| 1 | Laman web | Website | Pembina laman; AI Website Assistant (edit blok, cipta halaman/borang, salin halaman dari URL), llms.txt, SEO berstruktur | GRO-005 · Site · diuji | AI assistant, salin halaman dari URL | Ejen "bina laman daripada perbualan WhatsApp/foto kedai"; templat industri MY | P2 |
| 2 | Laman web | eCommerce | Susun atur /shop, Click & Collect, COD semua kaedah, AI sunting produk, cross-sell, minta ulasan | RET-001 · Site+Marketplace · diuji | Click & Collect, cross-sell automatik | Satu stok dengan POS (PR #2), pesan WhatsApp → bayar DuitNow/FPX | P2 |
| 3 | Laman web | Blog | Reka bentuk semula, cadangan pos, jadual pos | GRO-005/014 · Site, ContentStudio · diuji | – | Ejen kandungan tulis + jadual + SEO serentak | P3 |
| 4 | Laman web | Forum | Q&A, FAQ | Community · diuji (luar registry) | Lencana/reputasi | Jawapan dicadang AI daripada pangkalan pengetahuan | P4 |
| 5 | Laman web | eLearning | Kursus, kuiz, portal pelanggan | EVT-009/HR-005 · tiada | Tiada | Latihan staf ringkas (video pendek + kuiz) untuk SOP kedai | P4 |
| 6 | Laman web | Live Chat | Chatbot, Messenger/Instagram DM, ejen AI livechat + jana lead | GRO-012 · Omnichannel+LiveSupport · diuji | Ejen AI jana lead | **WhatsApp-first** inbox + ejen AI jawab, tempah, buat sebut harga | **P1** |
| 7 | Jualan | CRM | Kebarangkalian menang (AI), imbas kad nama, agihan lead berputar, Dun & Bradstreet | BOS-001 · CRM · diuji | Skor AI, imbas kad | Ejen susulan lead automatik di WhatsApp dengan kelulusan | P2 |
| 8 | Jualan | Sales | Templat sebut harga, komisen, harga berkala, diskaun sekali guna | GRO-010 · Invoicing+CRM · diuji | Komisen jualan, harga berjadual | Sebut harga dari suara/WhatsApp → PDF → e-tandatangan | P2 |
| 9 | Jualan | Point of Sale | Kedai & restoran; GoFood/GrabFood, self-order QR, alergen, kursus, berbilang mata wang, pencetak per stesen | BOS-006/HOS-001/002 · POS · diuji (PR #2: offline, DuitNow, QR meja, potong stok) | GrabFood/Foodpanda, pencetak per stesen, alergen | Ejen kaunter: ringkasan tutup kedai di WhatsApp, cadang pesanan semula stok | **P1** |
| 10 | Jualan | Subscriptions | Invois berulang, pembaharuan, peraturan kesetiaan | RET-006 · Billing · diuji | Langganan untuk **pelanggan tenant** (bukan langganan AI Bijan) | Langganan pelanggan kedai (contohnya bunga mingguan) + bayaran auto | P3 |
| 11 | Jualan | Rental | Kontrak sewa, papan pemuka, harga ikut pricelist | EVT-004 · tiada | Tiada | Pakej industri sewa (kereta, peralatan acara) | P4 |
| 12 | Kewangan | Accounting | Rekonsiliasi bank pintas, penyata cukai, AI financial insights, ramalan baris bil, peraturan padanan | FIN-001/011 · Finance · diuji | Auto-jurnal dari POS/invois, rekon bank | Ejen CFO harian (§11) + jurnal automatik + rekon bank DuitNow/FPX | **P1** |
| 13 | Kewangan | Invoicing | Invois, bayaran, susulan | FIN-004/016 · Invoicing · diuji; **e-Invois RANGKA** | MyInvois sebenar | e-Invois LHDN automatik + ejen kutip hutang WhatsApp | **P1** |
| 14 | Kewangan | Expenses | Laporan gabungan, had per jawatan, imbas resit (IAP) | FIN-009 · Expense · diuji | Imbas resit AI | Snap resit di WhatsApp → tuntutan → kelulusan | P2 |
| 15 | Kewangan | Documents | Akses fail/folder, AI susun & namakan, cipta jurnal dari dokumen | TEC-010 · DocumentAI · ujian nipis | Susun automatik, dokumen → jurnal | Peti masuk dokumen: emel/WhatsApp → AI baca → rekod draf | P2 |
| 16 | Kewangan | Spreadsheets | Hamparan terbina, carta, penapis global | TEC-001 · BusinessIntel · diuji | Hamparan dalam app | Tanya data dalam BM ("jualan Disember ikut cawangan") → carta | P3 |
| 17 | Kewangan | Sign | Tandatangan dari aktiviti, templat, itsme® (EU) | GRC-005 · SignDocs · ujian nipis | Penandatangan dinamik | Tandatangan di WhatsApp + jejak audit | P3 |
| 18 | Kewangan | ESG | Jejak karbon GHG Protocol, faktor emisi (AI), metrik sosial | GRC-003/004 · tiada | Tiada | Tangguh: laporan ESG ringkas untuk vendor korporat (Bursa) | P4 |
| 19 | Rantaian bekalan | Inventory | Penilaian stok, peraturan pesanan semula + horizon, pakej dalam pakej, notifikasi WhatsApp | SUP-001 · Inventory · ujian nipis (+PR #2 potong stok POS) | Pesanan semula automatik, kiraan stok | **Ejen stok**: ramal habis → draf PO → minta kelulusan | **P1** |
| 20 | Rantaian bekalan | Manufacturing | Gantt MO, kos operasi, subkontrak, pengeluaran berterusan | MFG-001/SUP-004 · Manufacturing · RANGKA (BOM/kit ada) | MO penuh, kerja | Resipi/kit F&B + kos bahan (COGS) dulu, MES kemudian | P3 |
| 21 | Rantaian bekalan | PLM | Versi produk, ECO | MFG-004 · tiada | Tiada | Tangguh | P5 |
| 22 | Rantaian bekalan | Purchase | Incoterm, tarikh jangka, kadar kualiti vendor, perjanjian | SUP-008 · SCM · diuji | Kualiti vendor | PO dijana ejen stok; perbandingan harga vendor | P2 |
| 23 | Rantaian bekalan | Maintenance | Permintaan, Gantt, peringkat per pasukan | MFG-002/SUP-010 · Assets · ujian nipis | Penyelenggaraan berjadual | Peringatan servis aset/kenderaan di WhatsApp | P3 |
| 24 | Rantaian bekalan | Quality | Pemeriksaan, lokasi kegagalan | GRC-002 · tiada | Tiada | Senarai semak kebersihan/HACCP F&B (HOS-008) | P4 |
| 25 | HR | Employees | Rekod pekerja, kontrak berversi, kerja jauh | HR-001/002 · HRMS · ujian nipis | Versi kontrak | Onboarding staf ikut WhatsApp | P2 |
| 26 | HR | Recruitment | Skor padanan, talent pool, kempen | HR-004 · tiada | Tiada | Ejen saring calon + iklan kerja (Blueprint PART XVI) | P3 |
| 27 | HR | Time Off | Peruntukan & permohonan cuti | HR-001 · HRMS · ujian nipis | – | Mohon cuti di WhatsApp, lulus satu ketik | P2 |
| 28 | HR | Appraisals | Kempen, matlamat, maklum balas 360 | HR-009 · tiada | Tiada | Tangguh | P4 |
| 29 | HR | Payroll | Pay run berpandu, indeks gaji, simulasi net-ke-gross | HR-006 · Payroll · ujian nipis | Statutori MY disahkan | EPF/SOCSO/EIS/PCB + fail bank + slip WhatsApp (perlu semakan profesional) | P2 |
| 30 | HR | Referral | Rujukan pekerja | HR-004 · tiada | Tiada | Tangguh | P5 |
| 31 | HR | Fleet | Kenderaan, kos, pemandu auto dari Payroll | SUP-009 · Fleet · diuji | – | Kekal | P4 |
| 32 | Pemasaran | Marketing Automation | Aliran drag-and-drop, cetus dari event/webhook, AI bantu sediakan kempen | GRO-002 · Marketing+FlowAutomation · diuji | Pembina visual | "Terangkan kempen dalam BM" → ejen bina aliran → owner lulus | P2 |
| 33 | Pemasaran | Email Marketing | Blok bersyarat, senarai dinamik, templat | GRO-002 · Marketing+Blast · diuji | – | Utamakan WhatsApp; emel sebagai saluran kedua | P3 |
| 34 | Pemasaran | SMS Marketing | Reka, hantar, jejak SMS | GRO-002/CORE-003 · Blast · diuji | – | Kekal; WhatsApp didahulukan | P4 |
| 35 | Pemasaran | Social Marketing | Story FB/IG, jadual per platform | GRO-014 · ContentStudio · diuji | Story | Ejen kandungan: jana dari produk + jadual (gaya Repliz) | P2 |
| 36 | Pemasaran | Events | Tiket combo, jualan tiket | EVT-001/003 · tiada | Tiada | Pakej acara (tiket QR) kemudian | P4 |
| 37 | Pemasaran | Survey | Tinjauan, kuiz langsung | GRO-018 · tiada | Tiada | Maklum balas pelanggan lepas beli (WhatsApp 1–5 bintang) | P3 |
| 38 | Servis | Project | Templat projek & tugas, peranan, Gantt portal | BOS-008 · Projects · ujian nipis | Templat | Ejen pecah projek daripada brief | P3 |
| 39 | Servis | Timesheet | Jejak masa per tugas | BOS-008 · Projects · ujian nipis | – | Masa auto dari aktiviti | P4 |
| 40 | Servis | Field Service | Lokasi teknisyen langsung, sejarah peralatan pelanggan, laluan optimum, yuran perjalanan | EVT-006 · ServiceOS · diuji | Laluan & lokasi langsung | Jadual + laluan + laporan kerja + bayar di lokasi (DuitNow) | P3 |
| 41 | Servis | Helpdesk | AI kesan tiket serupa & cadang jawapan, peringatan tutup auto | GRO-011 · Helpdesk · ujian nipis | Cadangan AI | Ejen sokongan: jawab draf dari pangkalan pengetahuan, eskalasi | P2 |
| 42 | Servis | Planning | Auto-rancang syif, Gantt berbilang hari, hantar jadual ikut WhatsApp | BOS-009 · tiada (HRMS sebahagian) | Jadual syif | Jadual syif staf kedai + tukar syif di WhatsApp | P2 |
| 43 | Servis | Appointments | Kapasiti tempahan, produk aksesori, maklum balas | EVT-005 · Booking · ujian nipis | Kapasiti/aksesori | Tempah melalui WhatsApp + deposit FPX + peringatan | P2 |
| 44 | Produktiviti | Discuss | Sembang, transkrip panggilan, undian | VirtualOffice · ujian nipis | Transkrip | Pejabat Maya + ringkasan mesyuarat AI | P4 |
| 45 | Produktiviti | Approvals | Permohonan & kelulusan | CORE-004 · WorkflowStudio · diuji | – | **Peti kelulusan tunggal** untuk semua cadangan ejen AI (§11) | **P1** |
| 46 | Produktiviti | Internet of Things | Kotak IoT: pencetak, penimbang, laci | TNG Soundbox + WebUSB ESC/POS (POS) | Penimbang, paparan pelanggan | WebUSB/WebSerial tanpa kotak IoT (lebih murah) | P3 |
| 47 | Produktiviti | VoIP | Aliran panggilan, softphone, log panggilan | GRO-012 · tiada | Tiada | Tangguh; WhatsApp call log kemudian | P5 |
| 48 | Produktiviti | Knowledge | Artikel, templat, carian | CORE-007 · BrainHub · tiada ujian | Pangkalan pengetahuan | SOP kedai = sumber ejen (RAG) | P2 |
| 49 | Produktiviti | AI | Ejen bertindak (cipta/kemas kini rekod), MCP, skills, suara, kredit IAP | CORE-005/006 · AIGateway, AIAgents, AgentStudio, McpStudio, ExecutiveAI · **alat ejen = stub (lihat §11.2)** | Alat sebenar, kelulusan, eval | §11 Pelaksanaan Agentic AI | **P1** |
| 50 | Penyesuaian | Studio | Medan, borang, laporan, automasi tanpa kod | WorkflowStudio, AgentStudio · diuji | Medan/borang tersuai | "Studio AI": ubah borang/aliran dengan ayat BM, pratonton, kemudian lulus | P3 |

**Keutamaan:** P1 = MVP-1 (kedai berniaga penuh); P2 = dalam 6 bulan; P3 = kemudian; P4/P5 = tangguh, ikut permintaan pelanggan berbayar.

### 10.3 Di mana AI Bijan boleh menang terhadap Odoo

| Dimensi | Odoo | AI Bijan (sasaran) |
|---|---|---|
| Harga | Per pengguna (€ 19.90–44.90/pengguna/bulan) + kredit AI IAP | Harga rata per syarikat, pengguna tanpa had (Super Admin tetapkan) |
| Agentic AI | Hanya pakej Custom | Teras dalam semua pakej; dompet kredit telus |
| Saluran utama | Emel / portal | **WhatsApp-first** (resit, kelulusan, laporan, tempahan) |
| Pematuhan MY | Lokalisasi rakan kongsi | MyInvois, DuitNow, FPX, EPF/SOCSO/EIS/PCB, SST terbina |
| Pelaksanaan | Rakan/konsultan, minggu–bulan | Onboarding < 30 min oleh ejen penyediaan + B-Migration Hub |
| UI | Padat, untuk pengguna terlatih | Mobile-first, BM, satu tindakan utama per skrin (§12) |
| Migrasi | Import CSV | Templat AutoCount/SQL/Bukku/Shopee + jalan selari |
