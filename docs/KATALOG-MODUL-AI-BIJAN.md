# AI BIJAN — Katalog Penuh Modul (v9.4)

> **58 modul** · **346 jadual** · **1,382 laluan API** · **963 ujian lulus** (0 gagal, 4 skip, 5,034 assertion)
>
> ⚠️ **Pembetulan 26 Okt 2026:** kiraan lama **1,518** adalah SALAH — ia termasuk
> **154 laluan berganda** (`api/v1/finance/finance/*` dsb.) akibat 5 modul mendaftar
> prefix DUA KALI (provider + `routes/api.php` masing-masing). Laluan berganda telah
> dibuang. `php skrip/semak-dokumen.php` kini memantau `laluan_berganda` dan
> `laluan_wildcard` (kedua-duanya mesti 0).
> Blueprint: `AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` → **v9.4**, **102 bab / 26 PART**
> Tarikh audit: 26 Oktober 2026
>
> **UI:** Filament 3.3 di `/admin` — **66 resource** (liputan semua 58 modul),
> **6 widget** dashboard, halaman Tetapan Syarikat. **212 kebenaran** merentas
> 52 namespace. Pengesahan visual: halaman ADE + POS + Tetapan = HTTP 200 + jadual render.
>
> ⚠️ **Semua angka di atas disahkan daripada kod sebenar** pada 26 Okt 2026 melalui
> `php artisan test`, `php artisan route:list`, dan semakan `app/Modules/`.
> Jangan tukar angka tanpa jalankan semula pengesahan (lihat `AUDIT-DOKUMEN.md`).

Semua modul adalah **multi-tenant** (`BelongsToTenant`: global scope + auto-fill `tenant_id` + halang ubah).
Semua laluan guna middleware `perm:{module}.{action}`.

---

## Ringkasan Mengikut Kategori

| Kategori | Modul | Bilangan |
|---|---|---|
| Jualan & Pemasaran | CRM, Marketing, Blast, Prospecting, SalesCopilot, Loyalty, Affiliate, Link | 8 |
| AI & Automasi | AIAgents, AIGateway, PersonalAssistant, Boardroom, FlowAutomation, ExecutiveAI, AgentStudio, McpStudio, WorkflowStudio, BusinessIntel | 10 |
| Operasi & Logistik | Inventory, Manufacturing, SCM, Logistics, Fleet, Cod, Booking, POS, Orders, Reminder | 10 |
| Kewangan & People | Finance, Invoicing, Payroll, COGS, HRMS, Projects, Assets | 7 |
| Platform & Komunikasi | Site, Marketplace, Omnichannel, SignDocs, DocumentAI, Helpdesk, IndustryPacks, LiveSupport, Community | 9 |
| Analitik & Metrik | Analytics, BrainHub, ProofOfValue, Vision | 4 |
| Media & Kandungan | MediaStudio, MediaEditor, ContentStudio | 3 |
| SEO | Seo | 1 |
| Perkhidmatan | ServiceOS | 1 |
| Perbelanjaan | Expense | 1 |
| Pembangunan & Automasi Agen | **ADE** | 1 |
| **Jumlah** | | **58** |

---

## 1. Jualan & Pemasaran (8)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **CRM** | `/api/v1/crm` | Pipeline jualan penuh: pelanggan, kenalan, lead, peluang, sebut harga + penukaran ke pesanan |
| **Marketing** | `/api/v1/marketing` | Automasi pemasaran: kempen, kontak, skor lead, segment, jujukan, baucar, aliran kerja + **atribusi 5 model** |
| **Blast** | `/api/v1/blast` | Enjin blast omnichannel (e-mel/SMS/WhatsApp): kempen, audiens, templat merge tag, opt-out, had kadar, deliverability + auto-pause bounce |
| **Prospecting** | `/api/v1/prospecting` | Cari prospek & kekal consent: sumber, senarai, import CSV, **scraper hormat robots.txt**, deduplikasi, penskoran + laporan consent PDPA |
| **SalesCopilot** | `/api/v1/sales-copilot` | Pembantu jualan AI: klasifikasi balasan (minat/tolak/negasi), brief prospek, barisan susulan |
| **Loyalty** | `/api/v1/loyalty` | Program kesetiaan: tahap & member, poin, tebus, ganjaran, ledger |
| **Affiliate** | `/api/v1/affiliate` | Program affiliate: rujukan, kod, klik, komisen, payout |
| **Link** | `/api/v1/link` | Pautan pendek & atribusi: jejak pautan, kod, halaman pendaratan |

---

## 2. AI & Automasi (10)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **AIAgents** | `/api/v1/ai-agents` | Sistem multi-ejen: definisi ejen, orkestrasi, memori, matriks (**BAB 16**) |
| **AIGateway** | `/api/v1/ai-gateway` | Pintu tunggal semua model AI: dompet kredit, routing, **imbas prompt injection**, redaksi PII, log, circuit breaker |
| **PersonalAssistant** | `/api/v1/personal-ai` | Pembantu peribadi: memori, tugas, keutamaan, jadual, RAG vector store (**BAB 17 + 17A**) |
| **Boardroom** | `/api/v1/boardroom` | Mesyuarat eksekutif AI: majlis, hujah, perselisihan, **keputusan berstruktur** dengan sumber (**BAB 20**) |
| **FlowAutomation** | `/api/v1/flow` | Automasi aliran kerja: pencetus, workflow, execute, retry, dead letter, idempotency, **kill switch** (**BAB 28**) |
| **ExecutiveAI** | `/api/v1/executive-ai` | Pembantu eksekutif multimodal: **tahap otonomi 0–4**, command centre, briefing automatik, video blast (**BAB 82**) |
| **AgentStudio** | `/api/v1/agent-studio` | **B-Ejen**: 16 templat Zoho-style, persona, ikatan alat + pengetahuan, **4 tahap otonomi**, ujian + skor, publish gating (**BAB 85**) |
| **McpStudio** | `/api/v1/mcp` | **AI MCP Studio**: protokol MCP `2025-06-18` — Claude Desktop/Cursor/Windsurf/n8n/Zapier; tools, resources, prompts, sessions; **allowlist + human-in-the-loop + audit** (**BAB 85**) |
| **WorkflowStudio** | `/api/v1/workflows` | **Visual automation** web-based: 18 jenis node, dry-run lalai, template marketplace, traversal graf (**BAB 86**) |
| **BusinessIntel** | `/api/v1/business-intel` | **AI self-improve**: skor kesihatan 6 dimensi, kajian AI, idea feed, competitor watch, sinyal pasaran, **learning loop** (**BAB 86**) |

---

## 3. Operasi & Logistik (10)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **Inventory** | `/api/v1/inventory` | Stok & gudang: barang, FIFO/FEFO, lejar, pemindahan, pelarasan, harga belian, laporan stok rendah |
| **Manufacturing** | `/api/v1/manufacturing` | Pembuatan: BOM, kos standard, work order, kit, stok bahan, variance kos |
| **SCM** | `/api/v1/scm` | Rantaian bekalan: pembekal, pesanan beli, terimaan, KPI |
| **Logistics** | `/api/v1/logistics` | Logistik: penghantaran, kurier, gudang, tracking, kos penghantaran |
| **Fleet** | `/api/v1/fleet` | **Armada & GPS**: kenderaan, pemandu, lokasi GPS, **tingkah laku memandu**, 9 jenis amaran, perjalanan, skor keselamatan (**BAB 85**) |
| **Cod** | `/api/v1/cod` | **COD Engine**: COD untuk PKS Malaysia — kelayakan, semak alamat MY, aliran status, rekod kutipan, **analisis kegagalan + reconciliation** (**BAB 85**) |
| **Booking** | `/api/v1/booking` | Tempahan servis: slot, temujanji, pengingat, servis, penyedia |
| **POS** | `/api/v1/pos` | Jualan kaunter: daftar, jualan, item, bayaran, diskaun, pulangan, syif |
| **Orders** | `/api/v1/orders` | **Pesanan unified**: SATU tempat untuk semua saluran, 14 status kanonikal, **rizab stok berpusat**, ringkasan merentas saluran (**BAB 86**) |
| **Reminder** | `/api/v1/reminders` | **Pusat peringatan**: 14 jenis, 5 saluran, jadual berulang, eskalasi automatik, **kotak masuk AI** (**BAB 86**) |

---

## 4. Kewangan & People (7)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **Finance** | `/api/v1/finance` | Kewangan: akaun, jurnal, transaksi, saman bank, untung rugi, kunci akaun |
| **Invoicing** | `/api/v1/invoicing` | Sebut harga → invois → e-Invois LHDN: nota kredit, resit, bil berulang |
| **Payroll** | `/api/v1/payroll` | **Gaji berstatut Malaysia**: EPF (11%), EIS, SOCSO, KWSP, HRDF, PCB, overtime berperingkat, slip gaji, eksport |
| **COGS** | `/api/v1/cogs` | Kos barang dijual: snapshot kos, margin, perbandingan |
| **HRMS** | `/api/v1/hrms` | Sumber manusia: pekerja, jabatan, shift, cuti, kehadiran, **biometrik + consent PDPA**, dokumen pekerja |
| **Projects** | `/api/v1/projects` | Projek & tugas: dependency, timesheet, milestone, sumber daya |
| **Assets** | `/api/v1/assets` | Aset & susut nilai: aset, susut garis lurus/berkurangan, penjagaan, pelupusan |

---

## 5. Platform & Komunikasi (9)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **Site** | `/api/v1/site` | **Pembina laman** (Shopify ganti): halaman, tema, domain, katalog, AI copy |
| **Marketplace** | `/api/v1/marketplace` | Marketplace multi-vendor: produk, pesanan, vendor, cart, review, komisen |
| **Omnichannel** | `/api/v1/omnichannel` | Omnichannel: penyatuan kedai + web + sosial, stok unified, pesanan hibrid |
| **SignDocs** | `/api/v1/sign-docs` | Tandatangan elektronik: dokumen, signature, audit trail, templat |
| **DocumentAI** | `/api/v1/document-ai` | Pemprosesan dokumen AI: ekstrak, klasifikasi, OCR, understanding, embedding |
| **Helpdesk** | `/api/v1/helpdesk` | Meja bantuan: tiket, SLA, kategori, artikel, portal awam |
| **IndustryPacks** | `/api/v1/industry-packs` | Pakej industri: retail, F&B, servis, pembuatan, kesihatan |
| **LiveSupport** | `/api/v1/live-support` | **Customer service live AI + eSIM**: 4 jenis saluran, transkripsi, **3 pencetus eskalasi**, ringkasan AI (**BAB 86**) |
| **Community** | `/api/v1/community` | **Komuniti AI BIJAN**: forum/academy/showcase/help, jawapan diterima, reputasi (**BAB 86**) |

---

## 6. Analitik & Metrik (4)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **Analytics** | `/api/v1/analytics` | Analitik: metrik, dashboard, widget, laporan, export |
| **BrainHub** | `/api/v1/brainhub` | Otak ramalan: insight, ramalan, cadangan, anomali |
| **ProofOfValue** | `/api/v1/proof-of-value` | **Metrik hasil per modul** ala ServiceTitan: katalog 8 modul, bacaan harian, baseline, sasaran, verifikasi, eksport (**BAB 85**) |
| **Vision** | `/api/v1/vision` | CCTV & penglihatan: kamera, RTSP, peristiwa, amaran, pengakuan biometrik, retensi |

---

## 7. Media & Kandungan (3)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **MediaStudio** | `/api/v1/media` | Studio media AI: muat naik, **jana video**, transcript, understanding, persona + moderasi, consent gate, retensi (**BAB 82**) |
| **ContentStudio** | `/api/v1/content` | **Jana → jadual → terbit**: brand voice, adaptasi 9 platform, auto-publish opt-in, **komen inbox terpusat**, chat/DM, moderation, sentiment, analytics (**BAB 86**) |
| **MediaEditor** | `/api/v1/media-editor` | **Canva/CapCut-class**: 4 jenis projek, 5 resolusi, 7 jenis lapisan, templat, render job (**BAB 86**) |

---

## 8. SEO (1)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **Seo** | `/api/v1/seo` | **SEO untuk SEMUA objek**: profil tenant, metadata polymorphic (6 jenis), **13 peraturan audit**, skor 0–100, cadangan AI, Schema.org JSON-LD, sitemap XML, robots.txt, Core Web Vitals, kata kunci, preview SERP (**BAB 86**) |

---

## 9. Perkhidmatan (1)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **ServiceOS** | `/api/v1/service-os` | **Service Business OS**: pelanggan, pakej, projek + aliran status 8 peringkat, sesi, sebut harga berversi, kontrak, kru + kemahiran, penugasan + **penolakan konflik**, tugas/checklist (5 templat industri), peralatan + tempahan, penyerahan, **outbox peristiwa domain idempoten**, margin & kos sebenar — **TIDAK** memproses wang (**BAB 87**) |

---

## 10. Perbelanjaan (1)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **Expense** | `/api/v1/expenses` | **Perbelanjaan & tuntutan staff**: kategori + COA, perbelanjaan multi-mata-wang, resit + OCR (guna semula `DocumentAI`), **kesan pendua**, cadangan kategori AI/heuristik, tuntutan (reimbursement/mileage/per-diem), jejak kelulusan, **post jurnal melalui `Finance`** — staff tidak boleh lulus sendiri (**BAB 89**) |

---

## 11. Pembangunan & Automasi Agen (1)

| Modul | Prefix | Ringkasan |
|---|---|---|
| **ADE** | `/api/v1/ade` | **Agentic Development Environment**: **A1 Skills Engine** (10 skill bina dalam — 7 langkah `obra/superpowers` + content-brief/market-research/financial-review; pencetus automatik; marketplace; kawalan lesen MIT/Apache/BSD), **A2 Worktree Isolation** (git worktree SEBENAR + baseline ujian bersih; 4 jenis sandbox; path traversal dilindungi), **A3 Sub-Agent Coordinator** (4 peranan implementer/tester/reviewer/researcher; executor callback; semak silang), **A4 Review Surface** (diff walkthrough + test runner + komen baris; **kritikal menyekat kelulusan automatik**) (**BAB 92**) |

> **Salinan kod vs spesifikasi:** ADE mengurus *orkestrasi*, *pengasingan* dan *semakan*.
> Pelaksanaan kod sebenar (sub-ejen menulis kod) memerlukan harness luar — sambungan
> kepada `AgentStudio`/`AIGateway` masih P2.

---

## Statistik Terkini

| Metrik | Nilai |
|---|---|
| Modul | **58** |
| Model | **292** |
| Service | **166** |
| Controller (modul) | **127** |
| Jadual pangkalan data | **346** |
| Laluan API (`api/v1`) | **1,382** |
| Ujian | **962 lulus** (4 dilangkau, 0 gagal) |
| Assertion | **5,033** |
| Resource Filament | **66** |
| Kebenaran | **212** |
| Blueprint | **102 bab / 26 PART** |
| Versi | **v9.4** |
| Laravel | 12.70.1 |
| Advisori keselamatan | **0** |

---

## Modul Baharu v8.9–v9.0 (15 modul)

### BAB 85 (v8.9) — 5 modul

| Modul | Idea | Jadual | Laluan | Ujian |
|---|---|---|---|---|
| `Cod` | #6 COD (jurang MY) | 4 | 11 | 30 |
| `Fleet` | #10 Armada & GPS | 6 | 13 | 26 |
| `ProofOfValue` | #5 Metrik hasil | 4 | 9 | 27 |
| `McpStudio` | #1 MCP server | 6 | 11 | 40 |
| `AgentStudio` | #8 B-Ejen 16 templat | 5 | 11 | 37 |

### BAB 86 (v9.0) — 9 modul

| Modul | Permintaan | Jadual | Laluan | Ujian |
|---|---|---|---|---|
| `Orders` | Order mgmt + stok sync | 6 | 12 | 27 |
| `Seo` | SEO semua AI BIJAN | 7 | 16 | 37 |
| `ContentStudio` | Auto-gen/post/jaga komen+chat | 10 | 21 | 35 |
| `Reminder` | Reminder | 5 | 18 | 33 |
| `BusinessIntel` | AI self-improve + kajian + pesaing | 7 | 16 | 40 |
| `WorkflowStudio` | n8n-style, web-based | 3 | 9 | 25 |
| `MediaEditor` | Canva/CapCut | 3 | 11 | 18 |
| `LiveSupport` | Call center AI + eSIM | 3 | 8 | 9 |
| `Community` | Komuniti AI BIJAN | 3 | 8 | 9 |

### BAB 87 (v9.0) — 1 modul

| Modul | Permintaan | Jadual | Laluan | Ujian |
|---|---|---|---|---|
| `ServiceOS` | Service business OS (RekaDesk audit) | 13 | 33 | 63 |

**Jumlah baharu:** 15 modul · 80 jadual · 217 laluan · **~463 ujian baharu**.

---

## 11. Ringkasan Modul Baharu v9.2

| Modul | Idea | Jadual | Laluan | Ujian |
|---|---|---|---|---|
| `Expense` | Pengurusan perbelanjaan + resit + tuntutan staff | 7 | 33 | 48 |

**Modul baharu (v9.2):** 1 modul · **7 jadual** · **33 laluan** · **48 ujian**.

---

## 12. Ringkasan Modul Baharu v9.4

| Modul | Idea | Jadual | Laluan | Resource UI | Ujian |
|---|---|---|---|---|---|
| `ADE` | Agentic Development Environment (IDE → ADE) | 6 | 24 | 4 | 48 |

**Modul baharu (v9.4):** 1 modul · **6 jadual** · **24 laluan** · **4 resource** · **48 ujian** (191 assertion).

**Jadual:** `ade_skills`, `ade_skill_invocations`, `ade_sandboxes`, `ade_coordinations`,
`ade_coordination_tasks`, `ade_reviews`.

**Migration:** `2026_10_26_002200_create_ade_tables.php`

**4 kebenaran baharu:** `ade.view` · `ade.manage` · `ade.execute` · `ade.approve`.

**UI:** `/admin/ade-skill` · `/admin/ade-sandbox` · `/admin/ade-coordination` · `/admin/ade-review`

---

## 📋 Dirancang — v9.3 (BAB 90-91) — BELUM DIKODKAN

> ⚠️ **Status: spesifikasi sahaja.** Tiada jadual, laluan atau ujian wujud untuk kedua-dua
> modul ini. Jangan tandakan sebagai siap. Spesifikasi penuh dalam blueprint PART XXV.

| Modul | Ringkasan | Jadual | Laluan | Fasa |
|---|---|---|---|---|
| **B-Receipt & B-Statement** | Hantar **semua** dokumen transaksi (resit, invois, e-Invois, sebut harga, nota kredit/debit, penyata akaun, bil, peringatan) ke WhatsApp pelanggan. Satu enjin untuk **58 modul**. **WhatsApp Business Platform sahaja** — templat Meta, consent, opt-out. | 9 | 19 | P1–P4 |
| **B-Vision AI** | Platform operasi visual: analisis video muat naik + kamera langsung (RTSP/ONVIF) → data berstruktur untuk modul. Laravel + worker Python/FFmpeg + adapter model. **8 kawalan keselamatan** termasuk SSRF protection. | 15 | 16 | 5 fasa |

**Keputusan belum selesai:** B-Vision — self-hosted computer vision vs gabungan self-hosted + API cloud (mempengaruhi kos, privasi, skalabiliti, harga).

---

## Peta Keperluan → Modul

| Keperluan pelanggan | Modul |
|---|---|
| "Saya jual di Shopee/Lazada/TikTok" | **Orders**, Marketplace, POS, Cod, Omnichannel, Inventory |
| "Saya nak satu tempat nampak semua jualan" | **Orders** (`/summary`) |
| "Saya nak hantar blast" | Blast, Marketing, Prospecting, SalesCopilot |
| "Saya tak jumpa prospek" | Prospecting, Blast, SalesCopilot |
| "Ejen WhatsApp jawab 24/7" | AgentStudio, ContentStudio, Helpdesk, LiveSupport |
| "Sambung Claude Desktop ke sistem saya" | **McpStudio** |
| "Bukti AI BIJAN menjana untung" | **ProofOfValue**, Analytics, BrainHub, **BusinessIntel** |
| "AI kaji bisnes saya & bagi idea" | **BusinessIntel** |
| "AI kaji pesaing saya" | **BusinessIntel** (competitor watch) |
| "SEO untuk semua produk/halaman" | **Seo** |
| "Auto-post ke 9 platform" | **ContentStudio** |
| "Jaga komen & chat semua platform" | **ContentStudio** |
| "Peringatan bayaran/susulan" | **Reminder** |
| "Automasi tanpa VPS" | **WorkflowStudio** |
| "Edit gambar/video sendiri" | **MediaEditor** |
| "Customer service live 24/7" | **LiveSupport** |
| "Komuniti & sokongan pengguna" | **Community** |
| "Perniagaan perkhidmatan (fotografi, wedding, kontraktor)" | **ServiceOS** |
| "Sebut harga, kontrak, jadual kru" | **ServiceOS** |
| "Lori/penghantaran" | Fleet, Logistics, Cod |
| "Invois automatik + gaji" | Invoicing, Finance, Payroll, Cod |
| "White-label" | Site, Omnichannel, SignDocs, DocumentAI, IndustryPacks |
| "AI tolong buat keputusan" | AIAgents, Boardroom, FlowAutomation, ExecutiveAI, PersonalAssistant |
| "Bina ciri sendiri dengan ejen" | **AgentStudio**, **ADE** |
| "IDE → ADE (ejen tulis/semak kod)" | **ADE** (A1 skills, A2 sandbox, A3 coordinator, A4 review) |
| "Kod saya mesti disemak sebelum merge" | **ADE** (A4 — kritikal menyekat kelulusan) |

---

## Integrasi B-POS System (BAB 88)

> **B-POS System** (`C:\ai-bijan\AI-Bijan\b-pos-system`) ialah **produk kasir berasingan**, bukan modul AI BIJAN.
> Audit penuh: blueprint **BAB 88**. **Keputusan: kekal berasingan**, ciri dipindahkan satu per satu (T-76 → T-84).

| Metrik | B-POS | AI BIJAN |
|---|---|---|
| Migration | 109 | **49** |
| Model | 61 | 292 |
| Service | 30 | 166 |
| Controller | 73 | 127 |
| Multi-tenant | ❌ Tiada | ✅ `BelongsToTenant` |
| AI | ❌ Tiada | ✅ **10 modul** |

> ⚠️ Angka AI BIJAN disahkan 26 Okt 2026. Migration **49 fail** menghasilkan **346 jadual**
> (satu fail migration boleh mencipta banyak jadual).

**6 jurang B-POS yang perlu dipindahkan ke AI BIJAN:**

| Ciri | Sasaran | Tugasan |
|---|---|---|
| Dine-in + Kitchen Display | `POS/` | T-76 |
| Modifier / Add-on | `POS/` + `Inventory/` | T-77 |
| Service Charge | `POS/` + `Invoicing/` | T-78 |
| Multi-Tender Split Payment | `POS/` | T-79 |
| Cashier Shift + Cash Movement | `POS/` | T-80 |
| Offline PWA + sync fingerprint | `POS/` | T-81 |

**Penilaian:** POS **8/10** · Keselamatan **4/10** · AI **0/10** · UI **7/10**.
**Lesen:** asas `aryadwiputra/point-of-sales` = MIT (perlu atribusi); lapisan Bijan = proprietari.

---

## Modul Dirancang (Belum Dikodkan)

| Modul | Sumber | Halangan |
|---|---|---|
| **B-Migration Wizard** | Dolibarr, Matrixify | Risiko data — perlu dry-run simulator |
| **B-ServiceOS** | RekaDesk (BAB 87) | Perlu lapisan domain baharu |
| **ADE (A1–A4)** | `obra/superpowers` (BAB 86.11) | Skills engine, worktree, coordinator, review |
| **B-Sandbox** | ADE keperluan | Isolation container/worktree |
| **Kalodata-class affiliate analytics** | kalodata.com | Data awam sahaja |
| **Compliance per negara** | Odoo | Nasihat guaman |
| **Connector Pack 20+** | ServiceTitan | Kajian API per vendor |
| **White-label OEM** | Tweak Odoo | Branding engine |
| **FilamentPHP UI** | aureuserp (MIT) | `composer require filament/filament` — **UI 2 → 7** |
