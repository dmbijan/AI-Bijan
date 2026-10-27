# Peta Pesaing — 34 Kategori Modul AI BIJAN

> ⚠️ **Nota penamaan (26 Okt 2026):** "34" merujuk bilangan **kategori modul**
> dalam peta pesaing ini — serta angka modul dalam kod pada masa penulisan (24 Okt).
> Kod kini mempunyai **59 modul**; lihat `KATALOG-MODUL-AI-BIJAN.md`.
> Peta ini kekal sah sebagai **peta kategori pesaing**, bukan senarai modul kod.

**Tarikh:** 24 Oktober 2026
**Kaedah:** Setiap modul dipetakan dengan pesaing sebenar. Data harga/ciri yang
disahkan dari laman rasmi ditanda ✅. Selebihnya ditanda ⚠️ (belum disahkan).

> **Nota skop:** Dokumen ini ialah **peta pesaing**, bukan analisis mendalam.
> 34 modul × 100 pesaing tidak boleh dianalisis secara mendalam dalam satu dokumen.
> Untuk analisis mendalam, rujuk `ANALISIS-KOMPETITIF.md` (5 pesaing teras) dan
> pilih modul untuk kajian lanjut.

---

## Ringkasan Status Modul

| Modul | Saiz | Kematangan | Pesaing utama |
|---|---|---|---|
| POS | 17 php, 3 ctrl | 🟡 Backend lengkap, UI rangka | StoreHub, Loyverse, Shopify POS |
| Invoicing | 23 php, 4 ctrl | 🟡 Backend lengkap | Bukku, StoreHub, Xero |
| CRM | 16 php, 4 ctrl | 🟡 Backend lengkap | HubSpot, Zoho CRM, Pipedrive |
| HRMS | 32 php, 6 ctrl | 🟢 Terbesar | Zoho People, BambooHR, Employment Hero |
| Payroll | 21 php, 5 ctrl | 🟢 Kiraan + eksport | PayrollPanda, Swingvy, Talenox |
| Marketing | 33 php, 10 ctrl | 🟢 Terbesar | HubSpot, Mailchimp, ActiveCampaign |
| Inventory | 4 php, 2 ctrl | 🟡 Baharu | Zoho Inventory, Cin7, TradeGecko |
| Marketplace | 20 php, 5 ctrl | 🟡 | Shopee, Lazada, SiteGiant |
| Site | 27 php, 7 ctrl | 🟢 | Wix, Squarespace, Shopify |
| VirtualOffice | 23 php, 4 ctrl | 🟢 | Monday, Asana, ClickUp |
| Analytics | 16 php, 3 ctrl | 🟡 | Looker Studio, Metabase, Power BI |
| Loyalty | 17 php, 5 ctrl | 🟢 | Loyverse, Stampme, Belly |
| Healthcare/Helpdesk | 1 php, 0 ctrl | 🔴 Service sahaja | Zendesk, Freshdesk |
| Manufacturing | 1 php, 0 ctrl | 🔴 Service sahaja | Katana, MRPeasy |
| Vision (CCTV) | 7 php, 1 ctrl | 🟡 Fasa 1 | Hikvision, Milestone, Verkada |

---

## 1. POS (Point of Sale)

### Status AI BIJAN
Backend **lengkap** (25 endpoint): register, cash shift, sale draft, add/update/remove
item, complete, cancel, refund, summary. UI **rangka sahaja** (7.1 KB).

### Pesaing Malaysia (disahkan)

| # | Pesaing | Harga | Kekuatan | Celah |
|---|---|---|---|---|
| 1 | **StoreHub** ✅ | RM122–471/bulan | e-Invois terbina, 20k pelanggan, multi-cawangan | Tiada HR/payroll |
| 2 | **Loyverse** ✅ | **Percuma** | 350k pedagang, offline, loyalty, 30+ bahasa | Tiada e-Invois MY |
| 3 | **Shopify POS** ✅ | USD29–299 + POS Pro | Ekosistem terbesar, Sidekick AI | Tiada e-Invois MY, harga USD |
| 4 | **SiteGiant** ✅ | Tersembunyi | Multichannel (Shopee/Lazada/TikTok) | Tiada POS fizikal teras |
| 5 | **Grits** ⚠️ | ~RM99–299 | POS + loyalty MY | Tiada kewangan |
| 6 | **Slurp** ⚠️ | ~RM150+ | F&B khusus | Sempit |
| 7 | **Eats365** ⚠️ | ~RM200+ | F&B, KDS | F&B sahaja |
| 8 | **Qashier** ⚠️ | ~RM99+ | POS + pembayaran bersepadu | Tiada HR |
| 9 | **iBranch** ⚠️ | ~RM100+ | POS MY | Kecil |
| 10 | **Bpos** ⚠️ | ~RM80+ | POS MY, murah | Lama |

### Pesaing Global
Lightspeed Retail, Square POS, Clover, Revel, Vend, Erply, Hike, Bindo, ShopKeep,
Ambur, Korona, uniCenta, Odoo POS, ERPNext POS, WooCommerce POS, Magento POS,
BigCommerce POS, Toast (F&B), TouchBistro, Revel Systems, NCR Silver, Micros,
Ingenico, Verifone, Pax, SumUp, Zettle, iZettle, myPOS, Epos Now, Vend HQ,
Kounta, Tyro, Impos, SwiftPOS, Idealpos, H&amp;L, PosBytz, Casio, Citizen,
Epson (perkakasan), Zebra, Honeywell, Datalogic (pengimbas).

**Jumlah pesaing dikenal pasti: 50+**

### Idea Yang Boleh Digabung ke AI BIJAN

| Idea | Sumber | Nilai |
|---|---|---|
| **Mod offline penuh** | Loyverse ✅ | Kedai MY kerap putus talian |
| **Customer display** | Loyverse ✅ | Papar troli kepada pelanggan |
| **Kitchen display** | Loyverse, Eats365 | F&B perlu |
| **Loyalty terbina** | Loyverse, StoreHub | AI BIJAN sudah ada B-Loyalty — sambung |
| **Multi-cawangan dari satu akaun** | StoreHub ✅ | AI BIJAN ada branch scope |
| **Sidekick AI** | Shopify ✅ | AI BIJAN ada Personal Assistant — sambung |
| **Kadar tukar + harga serantau** | Shopify | Untuk pengembangan SEA |
| **Tip pooling** | Toast | F&B Malaysia mula guna |
| **Split bill** | Toast, Eats365 | Restoran perlu |
| **Table management** | StoreHub ✅ | F&B |
| **Modifier/topping** | Semua F&B POS | Burger: tambah cheese +RM2 |
| **Resit WhatsApp** | Tiada pesaing | **Pembeza unik** |

---

## 2. Invoicing & e-Invois

### Status AI BIJAN
Backend lengkap (23 php, 9 model): quotation, invoice, credit/debit note, receipt,
recurring billing. e-Invois **belum disambung** ke LHDN.

### Pesaing Malaysia (disahkan)

| # | Pesaing | Harga | Kekuatan | Celah |
|---|---|---|---|---|
| 1 | **Bukku** ✅ | **RM0–135 + SST** | e-Invois **percuma**, pengguna tanpa had | Perakaunan sahaja |
| 2 | **StoreHub** ✅ | Termasuk | e-Invois terbina | POS sahaja |
| 3 | **Xero** ⚠️ | ~RM60–180 | Bank feed global | e-Invois MY melalui app pihak ketiga |
| 4 | **QuickBooks** ⚠️ | ~RM60–200 | Global, matang | e-Invois MY terhad |
| 5 | **Financio** ⚠️ | ~RM50–150 | Perakaunan MY, e-Invois | Perakaunan sahaja |
| 6 | **SQL Account** ⚠️ | RM1k–5k sekali | Desktop, popular MY | Tiada cloud, tiada AI |
| 7 | **AutoCount** ⚠️ | RM2k–10k sekali | Desktop MY, matang | Lama, tiada AI |
| 8 | **UBS** ⚠️ | RM500–2k | Legacy MY | Sangat lama |
| 9 | **MYOB** ⚠️ | ~RM80–200 | Global | Lemah di MY |
| 10 | **Wave** ⚠️ | Percuma | Percuma | Tiada e-Invois MY |

### Pesaing Global
FreshBooks, Zoho Books, Sage, NetSuite, Odoo Accounting, ERPNext, Invoice Ninja,
Invoice2go, Bill.com, Tipalti, AvidXchange, Coupa, SAP Concur, Certinia,
Accounting Seed, Sage Intacct, Microsoft Dynamics 365 Finance, Oracle Financials,
Infor, Epicor, Acumatica, Deltek, Unit4, IFS, Workday Financials, Zuora (langganan),
Chargebee, Recurly, Stripe Invoicing, PayPal Invoicing, Square Invoices,
PandaDoc, DocuSign (e-sign), Adobe Sign.

**Jumlah pesaing dikenal pasti: 40+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **e-Invois percuma** pada pelan masuk | Bukku ✅ | Bukku guna ini sebagai umpan |
| **Pengesahan MyInvois masa nyata** | LHDN | Wajib |
| **Pautan bayaran dalam invois** | Stripe, Xero | Kurang geseran |
| **Peringatan automatik** | FreshBooks | AI BIJAN ada B-Flow |
| **Deposit / ansuran** | Chargebee | Untuk projek |
| **Portal pelanggan** | FreshBooks | Lihat & bayar |
| **Invois berbilang bahasa** | Shopify | MY perlu BM/EN |
| **Template invois AI** | Invoicing AI startups | Pembeza |

---

## 3. CRM

### Status AI BIJAN
16 php, 6 model: customer, contact, lead, opportunity, quotation, quotation line.

### Pesaing (disahkan + global)

| # | Pesaing | Harga | Kekuatan |
|---|---|---|---|
| 1 | **HubSpot** ✅ | RM135–1,350/pengguna | Terbaik untuk pemasaran |
| 2 | **Zoho CRM** ⚠️ | ~RM60–300 | Murah, luas |
| 3 | **Pipedrive** ⚠️ | ~RM60–200 | Pipeline visual |
| 4 | **Salesforce** ⚠️ | RM110k+/tahun | Terdalam |
| 5 | **Freshsales** ⚠️ | ~RM50–300 | Mudah |
| 6 | **Copper** ⚠️ | ~RM60+ | Integrasi Google |
| 7 | **Monday CRM** ⚠️ | ~RM50+ | Visual |
| 8 | **Capsule** ⚠️ | ~RM40+ | Ringkas |
| 9 | **Insightly** ⚠️ | ~RM60+ | Projek+CRM |
| 10 | **Nimble** ⚠️ | ~RM40+ | Sosial |

**Global:** Dynamics 365 Sales, Oracle CX, SugarCRM, SuiteCRM, Vtiger, Agile CRM,
Keap, Infusionsoft, ActiveCampaign CRM, Klaviyo, Brevo, Mailchimp CRM, Attio,
Folk, Clay, Airtable, Notion (CRM), Close, Outreach, Salesloft, Gong, Apollo,
ZoomInfo, Clearbit, Lusha, Hunter.

**Jumlah: 30+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **Pipeline drag-drop** | Pipedrive | UI lebih baik |
| **Timeline aktiviti bersatu** | HubSpot | Semua interaksi di satu tempat |
| **Skor prospek automatik** | HubSpot, AI BIJAN sudah ada | Sambung ke UI |
| **Draf mesej AI** | Close, AI BIJAN | Pembeza |
| **Peta wilayah** | Salesforce | Untuk sales lapangan |
| **Kutipan WhatsApp** | Tiada pesaing MY | Pembeza |
| **Kesan pendua automatik** | Dedupe tools | Data bersih |

---

## 4. HRMS

### Status AI BIJAN
**Modul terbesar** (32 php, 17 model): employee, department, designation, attendance,
leave types, leave requests, leave allocation, shift, overtime, holidays, documents.

### Pesaing

| # | Pesaing | Harga | Kekuatan |
|---|---|---|---|
| 1 | **Employment Hero** ⚠️ | ~RM30–60/pekerja | MY payroll + HR |
| 2 | **PayrollPanda** ⚠️ | ~RM10–30/pekerja | MY, murah |
| 3 | **Swingvy** ⚠️ | ~RM15–40/pekerja | MY, mudah |
| 4 | **Talenox** ⚠️ | ~RM15–40/pekerja | MY payroll |
| 5 | **Zoho People** ⚠️ | ~RM10–40 | Global, murah |
| 6 | **BambooHR** ⚠️ | ~RM30–60 | Terbaik UX |
| 7 | **HiBob** ⚠️ | ~RM50+ | Moden |
| 8 | **Personio** ⚠️ | ~RM40+ | Eropah |
| 9 | **Kakitangan** ⚠️ | ~RM10–25 | MY |
| 10 | **HReasily** ⚠️ | ~RM15–35 | MY/SEA |

**Global:** Workday, SuccessFactors, Oracle HCM, ADP, Paycom, Paylocity, Gusto,
Rippling, Deel, Remote, Velocity Global, Papaya, Multiplier, Factorial, Kenjo,
Sesame, Sage HR, Access PeopleHR, Cezanne, BrightHR, Breathe, CharlieHR,
RotaCloud, Deputy, When I Work, Homebase, Connecteam, Skello, Planday,
Calamari, Timetastic, LeaveMonitor.

**Jumlah: 35+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **Portal pekerja layan diri** | BambooHR | Kurang kerja HR |
| **Aplikasi mudah alih pekerja** | Deputy, Homebase | Staf lapangan |
| **Papan syif drag-drop** | RotaCloud, Deputy | F&B/retail perlu |
| **Onboarding / offboarding checklist** | BambooHR | Wajib |
| **Kalendar cuti pasukan** | Timetastic | Mudah difahami |
| **Clock-in geolokasi** | Deputy | Untuk lapangan |
| **e-Sign dokumen HR** | AI BIJAN ada SignDocs | Sambung |
| **EA Form automatik** | PayrollPanda | Wajib MY |

---

## 5. Payroll

### Status AI BIJAN
21 php, 5 model. Kiraan EPF/SOCSO/EIS/PCB/HRDF **betul dan diuji** (10 ujian).
Eksport CSV **siap** (9 ujian). Belum hantar terus ke portal KWSP/PERKESO/LHDN.

### Pesaing

| # | Pesaing | Harga | Kekuatan | Celah |
|---|---|---|---|---|
| 1 | **PayrollPanda** ⚠️ | ~RM10–30/pekerja | MY, murah, tepat | Tiada HR penuh |
| 2 | **Swingvy** ⚠️ | ~RM15–40 | MY, mudah | Terhad |
| 3 | **Talenox** ⚠️ | ~RM15–40 | MY | Terhad |
| 4 | **Employment Hero** ⚠️ | ~RM30–60 | HR+payroll penuh | Mahal |
| 5 | **Bukku Payroll** ⚠️ | Integrasi | Bersama perakaunan | Terhad |
| 6 | **SQL Payroll** ⚠️ | RM1k–3k sekali | Desktop MY | Tiada cloud |
| 7 | **AutoCount Payroll** ⚠️ | RM1k–5k | Desktop MY | Lama |
| 8 | **Zoho Payroll** ⚠️ | ~RM15+ | Global | Payroll MY terhad |
| 9 | **Gusto** ⚠️ | USD40+ | US sahaja | Tak relevan MY |
| 10 | **ADP** ⚠️ | Enterprise | Global | Mahal |

**Global:** Paycom, Paylocity, Rippling, Deel, Papaya, Multiplier, CloudPay,
Ceridian Dayforce, UKG, SAP SuccessFactors EC Payroll, Oracle Payroll, Workday
Payroll, Nmbrs, Loket, Sympa, Hibob Payroll, Factorial, Sesame HR, Kenjo.

**Jumlah: 25+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **Penyerahan terus e-PCB** | PayrollPanda | Kurang kerja |
| **EA Form PDF automatik** | PayrollPanda | Wajib MY |
| **Form 8A / Borang E** | SQL Payroll | Wajib MY |
| **Payslip WhatsApp** | Tiada pesaing | **Pembeza unik** |
| **Pinjaman & potongan berjadual** | PayrollPanda | Koperasi MY |
| **Kiraan PCB berbilang pekerjaan** | LHDN | Kes tepi penting |
| **Prorata gaji masuk/keluar** | Swingvy | Biasa berlaku |

---

## 6. Marketing

### Status AI BIJAN
**Modul terbesar** (33 php, 10 controller, 11 model): campaign, segment, contact,
voucher, deal, sequence, workflow, lead score, ad integration, analytics.

### Pesaing

| # | Pesaing | Harga | Kekuatan |
|---|---|---|---|
| 1 | **HubSpot Marketing** ✅ | RM135–1,350/pengguna | Terbaik |
| 2 | **Mailchimp** ⚠️ | ~RM50–2,000 | E-mel mudah |
| 3 | **ActiveCampaign** ⚠️ | ~RM60+ | Automasi |
| 4 | **Klaviyo** ⚠️ | USD20+ | E-commerce |
| 5 | **Brevo** ⚠️ | ~RM25+ | Murah |
| 6 | **ConvertKit** ⚠️ | ~RM40+ | Kreator |
| 7 | **MailerLite** ⚠️ | ~RM25+ | Murah |
| 8 | **Sendinblue** ⚠️ | Sama Brevo | |
| 9 | **Customer.io** ⚠️ | USD100+ | Teknikal |
| 10 | **Ortto** ⚠️ | ~RM100+ | Bersatu |

**Global:** Marketo, Pardot, Eloqua, Adobe Campaign, Salesforce Marketing Cloud,
Braze, Iterable, MoEngage, CleverTap, Insider, WebEngage, Sailthru, Listrak,
Dotdigital, Campaign Monitor, Emma, Constant Contact, GetResponse, AWeber,
Drip, Omnisend, Attentive, Postscript, SlickText, ManyChat, Chatfuel, Tidio.

**Jumlah: 35+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **WhatsApp marketing** | ManyChat | MY guna WhatsApp |
| **Segmentasi tingkah laku** | Klaviyo | AI BIJAN ada segment |
| **Automasi terbina** | ActiveCampaign | AI BIJAN ada Flow |
| **Kad hadiah digital** | Voucher systems | AI BIJAN ada voucher |
| **Ujian A/B** | Semua | Belum ada |
| **AI tulis kandungan** | Jasper, Copy.ai | AI BIJAN ada AIGateway |
| **Atribusi hasil** | HubSpot | Belum ada |

---

## 7. Inventory

### Status AI BIJAN
4 php, 2 controller (**baharu**). `StockEngine` lengkap (FIFO/WAC, batch, expiry).
`ItemController` + `WarehouseController` baharu (10 ujian).

### Pesaing

| # | Pesaing | Harga | Kekuatan |
|---|---|---|---|
| 1 | **Zoho Inventory** ⚠️ | ~RM40–400 | Murah, bersepadu |
| 2 | **Cin7** ⚠️ | USD299+ | Multichannel |
| 3 | **TradeGecko/QuickBooks Commerce** ⚠️ | USD99+ | (tutup 2022) |
| 4 | **Ordoro** ⚠️ | USD59+ | Dropship |
| 5 | **Fishbowl** ⚠️ | USD1,000+ | Enterprise |
| 6 | **inFlow** ⚠️ | USD89+ | Mudah |
| 7 | **Katana** ⚠️ | USD179+ | Manufacturing |
| 8 | **MRPeasy** ⚠️ | USD49+ | Manufacturing |
| 9 | **Unleashed** ⚠️ | USD99+ | NZ |
| 10 | **DEAR** ⚠️ | USD199+ | Multichannel |

**Global:** NetSuite, SAP B1, Odoo Inventory, ERPNext, Microsoft Dynamics 365 SCM,
Infor CloudSuite, Epicor Kinetic, Sage X3, Acumatica, IQMS, Global Shop,
Plex, Rootstock, SYSPRO, JobBOSS, E2 MFG, Prodsmart, Tulip, Airtable Inventory.

**Jumlah: 25+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **Imbasan barcode mudah alih** | inFlow | Guna telefon |
| **Ramalan stok** | NetSuite | AI BIJAN ada BrainHub |
| **Kit/bundle** | Cin7 | Jual set |
| **Pengiraan stok berbilang lokasi** | Semua | AI BIJAN ada warehouse |
| **PO automatik** | inFlow | Bila bawah paras |
| **Notifikasi WhatsApp stok** | Tiada pesaing | **Pembeza** |

---

## 8. Marketplace

### Status AI BIJAN
20 php, 5 controller: product, vendor, order, cart, review.

### Pesaing

| # | Pesaing | Kekuatan |
|---|---|---|
| 1 | **Shopee** ⚠️ | Terbesar MY |
| 2 | **Lazada** ⚠️ | Kedua MY |
| 3 | **TikTok Shop** ⚠️ | Berkembang pantas |
| 4 | **Zalora** ⚠️ | Fesyen |
| 5 | **PG Mall** ⚠️ | Tempatan |
| 6 | **Youbeli** ⚠️ | Tempatan |
| 7 | **Mudah** ⚠️ | Iklan terpilih |
| 8 | **Carousell** ⚠️ | C2C |
| 9 | **Senheng** ⚠️ | Elektronik |
| 10 | **PrestoMall** ⚠️ | (tutup) |

**Global:** Amazon, eBay, Etsy, Walmart, Alibaba, AliExpress, Temu, Wish,
Facebook Marketplace, Instagram Shop, Pinterest Shop, TikTok Shop (global),
Flipkart, Rakuten, Mercado Libre, OLX, Tokopedia, Bukalapak, Zalora, Qoo10.

**Jumlah: 25+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **Segerak stok multichannel** | SiteGiant ✅ | Wajib |
| **Segerak harga** | Channel engines | Wajib |
| **Segerak pesanan** | SiteGiant | Wajib |
| **Kiraan untung selepas komisen** | SiteGiant ✅ | Jarang ada |

---

## 9. Site (Website Builder)

### Status AI BIJAN
27 php, 7 controller: site, page, theme, post, form, domain, analytics.

### Pesaing

| # | Pesaing | Harga | Kekuatan |
|---|---|---|---|
| 1 | **Wix** ⚠️ | RM30–200 | Termudah |
| 2 | **Squarespace** ⚠️ | RM60–250 | Reka bentuk |
| 3 | **Shopify** ✅ | USD29+ | E-commerce |
| 4 | **WordPress + WooCommerce** ⚠️ | Percuma+ | Fleksibel |
| 5 | **Webflow** ⚠️ | USD23+ | Pro |
| 6 | **Carrd** ⚠️ | RM0–100 | Halaman tunggal |
| 7 | **Google Sites** ⚠️ | Percuma | Asas |
| 8 | **SiteGiant Webstore** ✅ | Termasuk | MY |
| 9 | **Durable** ⚠️ | RM50+ | AI |
| 10 | **10Web** ⚠️ | USD10+ | AI WordPress |

**Global:** Weebly, GoDaddy Builder, Strikingly, Jimdo, Framer, Bubble, Glide,
Softr, Notion Sites, Canva Websites, Hostinger Builder, Elementor Cloud.

**Jumlah: 25+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **AI bina laman dari perihalan** | Durable, 10Web | Pembeza |
| **Template mengikut industri MY** | Practical | Restoran, klinik, kontraktor |
| **Borang → CRM terus** | AI BIJAN sudah ada | Kukuh |
| **Domain sendiri + SSL auto** | Semua | Ada |
| **WhatsApp butang** | MY-specific | **Pembeza** |

---

## 10. Virtual Office

### Status AI BIJAN
23 php, 4 controller: agent, board, card, column, schedule.

### Pesaing

| # | Pesaing | Harga | Kekuatan |
|---|---|---|---|
| 1 | **Monday.com** ⚠️ | RM40–150/pengguna | Visual |
| 2 | **Asana** ⚠️ | RM50–120 | Ringkas |
| 3 | **ClickUp** ⚠️ | RM30–120 | Semua-dalam-satu |
| 4 | **Notion** ⚠️ | RM40+ | Fleksibel |
| 5 | **Trello** ⚠️ | RM25+ | Kanban |
| 6 | **Jira** ⚠️ | RM40+ | Dev |
| 7 | **Basecamp** ⚠️ | RM50+ | Ringkas |
| 8 | **Wrike** ⚠️ | RM50+ | Enterprise |
| 9 | **Smartsheet** ⚠️ | RM50+ | Spreadsheet |
| 10 | **Airtable** ⚠️ | RM60+ | Pangkalan data |

**Global:** Linear, Height, Motion, Sunsama, Todoist, Things, OmniFocus, TickTick,
Loop, Coda, Nuclino, Slite, Confluence, SharePoint, Zoho Projects, Odoo Project.

**Jumlah: 25+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **AI susun tugasan automatik** | Motion | Pembeza |
| **Ringkasan harian AI** | Sunsama | AI BIJAN ada Boardroom |
| **Pandangan papan + senarai + kalendar** | ClickUp | Fleksibel |
| **Automasi tanpa kod** | Monday | AI BIJAN ada Flow |

---

## 11. Analytics

### Status AI BIJAN
16 php, 3 controller: metric, report, dashboard.

### Pesaing

| # | Pesaing | Harga | Kekuatan |
|---|---|---|---|
| 1 | **Looker Studio** ⚠️ | Percuma | Google |
| 2 | **Metabase** ⚠️ | Percuma+ | Open source |
| 3 | **Power BI** ⚠️ | RM50+/pengguna | Microsoft |
| 4 | **Tableau** ⚠️ | RM200+ | Terbaik |
| 5 | **Databox** ⚠️ | USD47+ | Papan pemuka |
| 6 | **Geckoboard** ⚠️ | USD49+ | TV dashboard |
| 7 | **Klipfolio** ⚠️ | USD100+ | |
| 8 | **Zoho Analytics** ⚠️ | ~RM50+ | Murah |
| 9 | **Google Analytics** ⚠️ | Percuma | Web |
| 10 | **Mixpanel** ⚠️ | USD25+ | Produk |

**Global:** Amplitude, Heap, Segment, Snowplow, Fivetran, dbt, Preset, Superset,
Redash, Grafana, Kibana, ThoughtSpot, Sisense, Domo, Qlik, GoodData, Mode.

**Jumlah: 25+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **Tanya dalam bahasa biasa → laporan** | ThoughtSpot | AI BIJAN boleh buat |
| **Papan pemuka TV** | Geckoboard | Kedai/kilang guna |
| **Amaran metrik** | Databox | AI BIJAN ada Flow |
| **Laporan berjadual WhatsApp** | Tiada pesaing | **Pembeza** |

---

## 12. Loyalty

### Status AI BIJAN
17 php, 5 controller: member, points, reward, tier, analytics.

### Pesaing

| # | Pesaing | Kekuatan |
|---|---|---|
| 1 | **Loyverse Loyalty** ✅ | Percuma, POS bersepadu |
| 2 | **Stampme** ⚠️ | Kad setem digital |
| 3 | **Belly** ⚠️ | Apple Wallet |
| 4 | **Loyalzoo** ⚠️ | Kad digital |
| 5 | **Smile.io** ⚠️ | Shopify |
| 6 | **Yotpo** ⚠️ | Ulasan + loyalty |
| 7 | **Punchh** ⚠️ | Enterprise F&B |
| 8 | **Paytronix** ⚠️ | Enterprise |
| 9 | **Fivestars** ⚠️ | Kecil |
| 10 | **Square Loyalty** ⚠️ | Bersepadu |

**Global:** Kangaroo, CandyBar, LoyaltyLion, Stamped, Annex Cloud, Antavo,
OpenLoyalty, Perkville, Zinrelo, Como, Capillary, Cheetah.

**Jumlah: 20+**

### Idea Yang Boleh Digabung

| Idea | Sumber | Nilai |
|---|---|---|
| **Apple/Google Wallet kad** | Belly | Tiada app perlu |
| **Kad setem digital** | Stampme | Mudah untuk kopitiam |
| **Loyalty terbina dalam POS** | Loyverse ✅ | Wajib |
| **Peringatan WhatsApp** | Tiada pesaing | **Pembeza** |

---

## 13-34. Modul Selebihnya

### 13. Booking (15 php, 3 ctrl)
**Pesaing:** Calendly, Cal.com, Acuity Scheduling, Square Appointments, Booksy,
Fresha, Vagaro, Mindbody, SimplyBook.me, Setmore, 10to8, Appointlet, YouCanBookMe,
TimeTrade, Doodle, Bookeo, Checkfront, Peek, Rezdy, FareHarbor, Xola, Bókun,
TrekkSoft, Regiondo, Bookinglayer, Anna.money.

**Idea:** tempahan berulang, deposit, no-show policy, kalendar pasukan,
pautan tempahan WhatsApp, pembayaran di muka.

### 14. Helpdesk 🔴 (service sahaja)
**Pesaing:** Zendesk, Freshdesk, Intercom, Help Scout, Front, Gorgias, Kayako,
Zoho Desk, HappyFox, Teamwork Desk, Groove, Re:amaze, LiveAgent, Crisp, Tidio,
Drift, Olark, Chatwoot, Papercups, Gladly, Kustomer, Dixa, Ultimate, Supportbench.

**Idea:** SLA automatik (ada), KB terbina (ada), AI cadang jawapan (ada di AIGateway),
tiket → WhatsApp, CSAT automatik.

### 15. Manufacturing 🔴 (service sahaja)
**Pesaing:** Katana, MRPeasy, Fishbowl Manufacturing, Odoo MRP, ERPNext Manufacturing,
Prodsmart, Tulip, Plex, IQMS, Global Shop, E2 MFG, JobBOSS, Epicor, Infor,
SYSPRO, Rootstock, OpenBOM, Craft, Cleverence, MES systems.

**Idea:** BOM berbilang peringkat, routing, QC, kos sebenar (ada),
jadual pengeluaran, integrasi mesin (IoT).

### 16. Projects (19 php, 4 ctrl)
**Pesaing:** Asana, Monday, ClickUp, Jira, Wrike, Basecamp, Teamwork, Zoho Projects,
Odoo Project, Smartsheet, Airtable, Notion, Linear, Height, Motion, ProofHub,
FunctionFox, Mavenlink, Kantata, Deltek, Planview, Celoxis, Scoro, ProWork.

**Idea:** gantt (ada), timesheet (ada), keuntungan projek, pengebilan berdasarkan jam.

### 17. Assets (17 php, 3 ctrl)
**Pesaing:** EZOfficeInventory, Asset Panda, Cheqroom, UpKeep, Fiix, Limble,
Hippo, MaintainX, Fiix, AssetCloud, Wasp, Sortly, GoCodes, TrackVia, Intellect.

**Idea:** QR kod aset, penyelenggaraan pencegahan, susut nilai (ada), imbasan telefon.

### 18. SCM (15 php, 2 ctrl)
**Pesaing:** SAP Ariba, Coupa, Jaggaer, Ivalua, GEP, Zycus, Precoro, ProcureDesk,
Odoo Purchase, ERPNext Buying, NetSuite SCM, Infor, Epicor, Oracle SCM.

**Idea:** RFQ (ada), perbandingan pembekal, penilaian pembekal, PO automatik.

### 19. Logistics (11 php, 3 ctrl)
**Pesaing:** ShipStation, Shippo, EasyPost, EasyParcel (MY) ✅, J&T (MY),
Pos Laju (MY), Lalamove (MY), Ninja Van, DHL, FedEx, UPS, Onfleet, Bringg,
Tookan, Track-POD, Route4Me, OptimoRoute, Circuit.

**Idea:** penjejakan berbilang kurier, label automatik, kos penghantaran.

### 20. Omnichannel (14 php, 3 ctrl)
**Pesaing:** Zendesk Talk, Intercom, Freshchat, Crisp, Tidio, Gorgias, Re:amaze,
WATI (MY), Respond.io (MY), 360dialog, Twilio, MessageBird, Vonage, Infobip,
Trengo, Take Blip, Zenvia, ManyChat, Chatfuel.

**Idea:** inbox bersatu (ada), chatbot AI (sambung AIGateway), template WhatsApp.

### 21. Link (9 php, 2 ctrl)
**Pesaing:** Linktree, Bio.link, Beacons, Later, Koji, Campsite, Milkshake,
Carrd, Taplink, Shorby, Komi, Hopp, Bento.

**Idea:** pautan bio (ada), kad digital (ada), analitik klik, QR kod.

### 22. SignDocs (13 php, 3 ctrl)
**Pesaing:** DocuSign, Adobe Sign, HelloSign, PandaDoc, SignNow, Dropbox Sign,
Zoho Sign, SignWell, Eversign, YouSign, SignRequest, OneSpan, Nitro, Foxit.

**Idea:** e-tandatangan (ada), templat (ada), audit trail, MyDigitalID (MY).

### 23. Affiliate (17 php, 5 ctrl)
**Pesaing:** Refersion, Post Affiliate Pro, Tapfiliate, AffiliateWP, ShareASale,
Impact, PartnerStack, FirstPromoter, Rewardful, Tolt, Everflow, HasOffers.

**Idea:** pautan rujukan, komisen, pembayaran (ada), pencegahan fraud.

### 24. BrainHub (12 php, 3 ctrl)
**Pesaing:** Tableau AI, Power BI Copilot, ThoughtSpot, Qlik, Sisense, Domo,
DataRobot, H2O.ai, Dataiku, Alteryx, KNIME, RapidMiner.

**Idea:** ramalan (ada), insight (ada), cadangan (ada), penjelasan AI.

### 25. COGS (8 php, 2 ctrl)
**Pesaing:** Odoo Accounting, Cin7, Fishbowl, Katana, inFlow, Unleashed, DEAR.

**Idea:** FIFO/WAC (ada), margin (ada), kos sebenar vs standard.

### 26. AIAgents (9 php, 1 ctrl)
**Pesaing:** Relevance AI, Stack AI, CrewAI, LangChain, AutoGen, AgentGPT,
Zapier AI, Make AI, n8n AI, MindStudio, Lindy, Lindy AI, Beam AI, Aomni.

**Idea:** ejen (ada), tool allowlist (ada), kelulusan manusia (ada).

### 27. PersonalAssistant (16 php, 2 ctrl)
**Pesaing:** ChatGPT, Claude, Gemini, Copilot, Perplexity, You.com, Poe,
Character.ai, Pi, Meta AI, Grok, DeepSeek.

**Idea:** memori (ada), RAG (ada), model swap (ada), suara (belum).

### 28. Boardroom (6 php, 1 ctrl)
**Pesaing:** Tiada padanan langsung. Terdekat: McKinsey/BCG (manusia),
Gartner, Forrester (laporan).

**Idea:** 7 langkah (ada) — **ini pembeza paling unik AI BIJAN.**

### 29. FlowAutomation (7 php, 1 ctrl)
**Pesaing:** Zapier, Make, n8n, Pabbly, Integromat, Workato, Tray.io,
Microsoft Power Automate, Automate.io, IFTTT.

**Idea:** trigger (ada), kondisi (ada), retry (ada), kill switch (ada).

### 30. AIGateway (11 php, 1 ctrl)
**Pesaing:** OpenRouter, LiteLLM, Portkey, Helicone, LangFuse, Martian,
Requesty, Unify, Cloudflare AI Gateway.

**Idea:** routing (ada), wallet (ada), redaction (ada), guard (ada).

### 31. IndustryPacks (6 php, 1 ctrl)
**Pesaing:** Odoo (50+ modul), ERPNext (industri), Zoho (vertikal),
Toast (F&B), ServiceTitan (servis), Jobber (servis), Housecall Pro.

**Idea:** 19 industri (ada), MSIC (ada), blueprint (ada).

### 32. DocumentAI (12 php, 1 ctrl)
**Pesaing:** Rossum, Klippa, Mindee, Docparser, Nanonets, Veryfi, Hyperscience,
ABBYY, Kofax, Amazon Textract, Google Document AI, Azure Form Recognizer.

**Idea:** OCR (belum), ekstraksi (belum), confidence score (model ada).

### 33. Vision / CCTV (7 php, 1 ctrl)
**Pesaing:** Hikvision, Dahua, Milestone, Genetec, Verkada, Avigilon,
Axis, Bosch, Hanwha, Frigate (open source), Blue Iris, ZoneMinder, Shinobi.

**Idea:** Fasa 1 siap (kamera, peristiwa, HMAC, retensi, audit).
Fasa 2 siap (amaran). Belum: live view HLS, klip, YOLO.

### 34. HRMS Leave/Shift (sebahagian HRMS)
Sudah diliputi dalam modul 4.

---

## Idea Terbaik Yang Patut Digabung — Keutamaan

Berdasarkan semua pesaing di atas, ini 15 idea yang **paling berbaloi** dibina:

| # | Idea | Sumber pesaing | Modul | Impak |
|---|---|---|---|---|
| 1 | **Mod offline penuh** | Loyverse ✅ | POS | 🔴 Kritikal |
| 2 | **Resit & notifikasi WhatsApp** | Tiada pesaing | POS, Payroll, Invoicing | 🔴 Pembeza unik |
| 3 | **e-Invois LHDN** | Bukku ✅, StoreHub ✅ | Invoicing | 🔴 Wajib |
| 4 | **Customer display + Kitchen display** | Loyverse ✅ | POS | 🟠 F&B |
| 5 | **Portal pekerja layan diri** | BambooHR | HRMS | 🟠 |
| 6 | **Papan syif drag-drop** | RotaCloud, Deputy | HRMS | 🟠 |
| 7 | **EA Form + Borang E automatik** | PayrollPanda | Payroll | 🟠 Wajib MY |
| 8 | **Pipeline drag-drop visual** | Pipedrive | CRM | 🟠 |
| 9 | **AI bina laman dari perihalan** | Durable, 10Web | Site | 🟠 |
| 10 | **Kad loyalty Apple/Google Wallet** | Belly | Loyalty | 🟡 |
| 11 | **Segerak multichannel (Shopee/Lazada/TikTok)** | SiteGiant ✅ | Marketplace | 🟠 |
| 12 | **Imbasan barcode mudah alih** | inFlow | Inventory | 🟡 |
| 13 | **Laporan berjadual WhatsApp** | Tiada pesaing | Analytics | 🔴 Pembeza |
| 14 | **Apple/Google Wallet kad digital** | Belly, Stampme | Link | 🟡 |
| 15 | **MyDigitalID** | Tiada pesaing | SignDocs | 🟡 Pembeza MY |

---

## Idea Yang TIDAK Patut Dikejar

| Idea | Sumber | Sebab tolak |
|---|---|---|
| Marketplace sendiri | Shopee, Lazada | Tidak boleh menang rangkaian |
| Enjin carian | Google | Mustahil |
| Pengganti Excel sepenuhnya | Airtable | Terlalu luas |
| Perakaunan penuh | Bukku, Xero | Bukku sudah menang RM0 |
| CRM terdalam | Salesforce | Tidak boleh menang enterprise |
| Video conferencing | Zoom | Tiada kaitan |
| Emel transaksional | SendGrid | Komoditi |

---

## Nota Pemeliharaan

Dokumen ini mesti dikemas kini apabila:
1. Pesaing baharu muncul dalam kategori
2. Harga berubah (semak laman rasmi setiap suku tahun)
3. Modul AI BIJAN tamat atau bermula

**Jangan** tambah pesaing tanpa menyemak laman mereka terlebih dahulu.
Tanda ⚠️ bermaksud "dikenal pasti tetapi belum disahkan".
