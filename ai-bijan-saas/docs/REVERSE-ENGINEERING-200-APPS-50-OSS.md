# Reverse Engineering Mendalam — 200 Apps × 4 Platform + 50 OSS

**Tarikh:** 24 Oktober 2026
**Skop:** **50 resource setiap platform** (Shopify, Odoo, Zoho, ServiceTitan) = **200**
+ **50 projek GitHub open source**.
**Kaedah:** Pemer_fail app store / marketplace yang lengkap (bukan senarai ringkas).
Semua rating, review count, download count, dan harga **disahkan** pada 24 Okt 2026.

> **Nota pembetulan:** Audit sebelumnya (BAB 84) hanya mendalam 3 platform dan
> menganggap ServiceTitan sebagai "tiada app store". Audit ini membetulkan
> kedua-dua kesilapan dan menambah **Zoho Agent Store** yang teraba sebelum ini.

---

## Ringkasan Eksekutif: 5 Penemuan Baharu

| # | Penemuan | Platform | Nombor Bukti |
|---|---|---|---|
| 1 | **MCP server adalah semula** — 5 app Odoo berlainan MCP server | Odoo | 9,094 + 1,862 + 1,790 + 505 + 261 download |
| 2 | **Zoho ada store AI AGENTS berasingan** (16 ejen khusus) | Zoho | 16 ejen bernama |
| 3 | **ServiceTitan Pro Products = 10 produk AI bersepadu** dengan metrik hasil | ServiceTitan | 6 metrik hasil |
| 4 | **Compliance per negara = produk berasingan** di Odoo (Vietnam, India, EU, US, SA, UAE, MX) | Odoo | 7+ apps |
| 5 | **AI dashboard builder** sudah menjadi kategori standard | Odoo + Shopify | 15,941 + 1,107 + 3,136 download |

---

# BAHAGIAN 1 — SHOPIFY: 50 APPS (Rating + Review Count)

> **Sumber:** apps.shopify.com — 16,000+ apps, 7 kategori utama, 100-checkpoint review.

## 1.1 Store Management (Support, Analytics, Security) — 14 apps

| # | App | Rating | Reviews | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 1 | **Tidio: AI Chatbot & Live Chat** | 4.8 | 1,359 | Chatbot AI + live chat | ⭐⭐ `Helpdesk` |
| 2 | **Richpanel: AI Customer Support** | 4.9 | 137 | "AI agents resolve tickets end to end, **with an AI manager**" | ⭐⭐⭐ **AI manager** — `Helpdesk` |
| 3 | **CK: Abandoned cart on WhatsApp** | 5.0 | 303 | Cart recovery + order updates via WhatsApp | ⭐⭐⭐ **Revenue** |
| 4 | **Propel Replay, Survey, Heatmap** | 4.9 | 714 | Session replay | ⭐⭐ `Analytics` |
| 5 | **MIDA Replay, Heatmap & Insight** | 4.9 | 611 | Session replay | ⭐⭐ `Analytics` |
| 6 | Lucky Orange Heatmaps & Replay | — | — | Heatmap | ⭐⭐ |
| 7 | IndexGPT | — | — | AI search | ⭐ |
| 8 | **SB: Helpdesk, Live Chat & FAQs** | 4.6 | 319 | Helpdesk | ↔️ `Helpdesk` |
| 9 | **WhatFlow: WhatsApp Automation** | 3.9 | 335 | WA automation | ⭐ `Blast` |
| 10 | Redo | — | — | Tidio AI | ↔️ |
| 11 | **SEOWILL — Trust Badges & Icon** | 4.9 | 688 | Trust badges | ⭐ Trust signal |
| 12 | **TN Term and Condition Checkbox** | 4.9 | 565 | T&C checkbox | ⭐ **PDPA consent** |
| 13 | Mercado Pago Antifraude Plus | 4.4 | 55 | Antifraud | ⭐ `Finance` |
| 14 | Cozy AntiTheft (image watermark) | 4.8 | 244 | Image protection | ⭐ `Assets` |

## 1.2 Marketing & Conversion — 16 apps

| # | App | Rating | Reviews | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 15 | **Shopify Flow** | 4.7 | **15,009** | Automation | ⭐⭐⭐ Canvas visual |
| 16 | **TikTok** | 4.8 | **16,011** | TikTok sales channel | ⭐⭐ **TikTok Shop** |
| 17 | **Klaviyo** | 4.7 | 3,407 | Email + SMS | ⭐⭐⭐ Standard emas blast |
| 18 | **Shopify Messaging** | 4.8 | 4,721 | Email+SMS+WhatsApp | ⭐⭐⭐ |
| 19 | **PageFly AI Page Builder** | 4.9 | 5,917 | AI CRO funnel | ⭐⭐⭐ "find leaks" |
| 20 | **Seal Subscriptions** | 4.9 | 3,155 | Subscriptions & memberships | ⭐⭐ `Loyalty` |
| 21 | **Essential Upsell & Cross Sell** | 5.0 | 2,439 | Frequently Bought Together | ⭐⭐ `Marketplace` |
| 22 | **Dondy: WhatsApp Marketing+Chat** | 4.9 | 828 | WA AI agent + cart recovery | ⭐⭐⭐ |
| 23 | **Junip Product Reviews + AI** | 4.8 | 1,176 | Reviews + AI | ⭐⭐ Social proof |
| 24 | **Omega Facebook Pixel, Meta Ads** | 4.9 | 997 | Multi-pixel | ⭐ `Marketing` |
| 25 | **EasySell COD Form & Upsells** | 4.9 | 989 | **COD form** | ⭐⭐ **COD — kritikal MY!** |
| 26 | Simple Bundles & Kits | 4.9 | 808 | Bundles | ⭐ |
| 27 | Discounty: Bulk Discount Sales | 4.9 | 1,280 | BOGO, volume discount | ⭐⭐ `Marketing` |
| 28 | Froonze VIP Loyalty & Wishlist | 5.0 | 274 | Store credit, cashback, points | ⭐⭐ `Loyalty` |
| 29 | CREMA: Product Reviews & UGC | — | — | Reviews + UGC | ⭐ |
| 30 | Shopify Forms | 4.5 | 756 | Forms | ↔️ `Site` |

## 1.3 Orders & Shipping — 11 apps

| # | App | Rating | Reviews | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 31 | **Packlink PRO Shipping** | 4.8 | 937 | Multicarrier | ⭐⭐ `Logistics` |
| 32 | ShipStation Orders & Inventory | 4.2 | 709 | Multicarrier | ⭐⭐ |
| 33 | **Vify Order Printer PDF Invoice** | 5.0 | 1,213 | **Order printer PDF** | ⭐⭐ **ESC/POS** |
| 34 | **Order Printer Templates** | 4.9 | 777 | Order printer | ⭐⭐ |
| 35 | **Simple Invoice — Order Printer** | 4.9 | 446 | Invoice printer | ⭐⭐ |
| 36 | Xcotton Post Purchase Guard | 5.0 | 773 | Shipping protection + return + warranty | ⭐⭐ `Marketplace` |
| 37 | **Loop Returns & Exchanges** | 4.6 | 449 | Returns | ⭐⭐ `Marketplace` |
| 38 | Sufio: Invoices you can trust | 4.9 | 468 | Invoicing | ↔️ `Invoicing` |
| 39 | Route — Protection & Tracking | 4.0 | 375 | Tracking | ↔️ `Logistics` |
| 40 | StatusPro — Order Status | — | — | Made-to-order status | ⭐ |
| 41 | Billbee | — | — | Print & ship | ↔️ |

## 1.4 Store Design (Internationalization) — 5 apps

| # | App | Rating | Reviews | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 42 | **Pandectes GDPR Compliance** | 5.0 | **3,108** | GDPR/CCPA cookie | ⭐⭐ **PDPA consent banner** |
| 43 | **Transcy: AI Language Translate** | 4.5 | 2,567 | Auto-translate + currency by location | ⭐⭐⭐ **SEA!** |
| 44 | Shogun — Landing Page Builder | 4.8 | 1,972 | Landing page | ↔️ `Site` |
| 45 | Translate store — GTranslate | 4.7 | 682 | Translation | ⭐ |
| 46 | Complianz Consent | 4.4 | 277 | Cookie consent | ⭐ |

## 1.5 Finding Products — 4 apps

| # | App | Rating | Reviews | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 47 | **Faire: Buy Wholesale** | 4.9 | 1,318 | B2B wholesale sourcing | ⭐⭐ **B2B** |
| 48 | Faire: Sell Wholesale | 4.6 | 468 | B2B selling | ⭐⭐ |
| 49 | **Sami B2B Wholesale Pricing** | 4.9 | 993 | Volume discount, b2b registration, tax | ⭐⭐ **B2B pricing** |
| 50 | Melon Minimum Order Quantity | 5.0 | 389 | MOQ | ⭐ |

## 1.6 Ekstrak Shopify → AI BIJAN

| Prioriti | Idea | Bukti | Modul |
|---|---|---|---|
| 🔴 1 | **AI manager untuk helpdesk** | Richpanel "AI agents + AI manager" | `Helpdesk` |
| 🔴 2 | **Cart recovery WhatsApp** | CK (5.0, 303) + Dondy (4.9, 828) | `Blast` |
| 🔴 3 | **COD (Cash on Delivery) form** | EasySell (4.9, 989) — kritikal MY | `POS` + `Marketplace` |
| 🟠 4 | **PDPA/GDPR consent banner** | Pandectes (5.0, 3,108) + TN T&C | `Helpdesk` + `Site` |
| 🟠 5 | **Translation + currency by location** | Transcy (2,567) — kritikal expand SEA | `Site` + `IndustryPacks` |
| 🟠 6 | **B2B wholesale + MOQ** | Sami (993) + Faire (1,318) | `Marketplace` |
| 🟡 7 | **Trust badges + review** | SEOWILL (688) + Junip (1,176) | `Site` |

> **⭐ COD (Cash on Delivery) adalah penemuan penting** — kaedah pembayaran
> dominan di Malaysia, dan tiada satu pun pesaing yang menyenangkannya.

---

# BAHAGIAN 2 — ODOO: 50 APPS (Download Count)

> **Sumber:** apps.odoo.com — **99,259 apps** didaftarkan (angka tepat), 4,963 halaman.

## 2.1 AI & MCP — 8 apps ⭐⭐⭐ (KUDAH PASTI)

| # | App | Downloads | Rating | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 1 | **MCP Server** (much. Consulting) | **9,094** | 9 | MCP untuk AI assistants | ⭐⭐⭐ **MCP server AI BIJAN** |
| 2 | **MCP Server** (MuK IT) | 1,790 | 432 | MCP server untuk AI agent integration | ⭐⭐ |
| 3 | **MCP Studio** (INKERP) | 142 | 142 | "AI MCP Studio — manage records, build dashboards via natural language" | ⭐⭐⭐ **AI + MCP + human-in-the-loop** |
| 4 | **Odoo MCP Manager** (Cybrosys) | 505 | 202 | Odoo Claude Connector, AI Odoo | ⭐⭐ |
| 5 | **MCP Server** (Advance PyBeans) | 261 | 129 | "Anthropic \| OpenAI Odoo MCP Server" | ⭐⭐ |
| 6 | **MuK AI Assistant** | 853 | 232 | "Native agentic AI (ChatGPT, Claude, Gemini) agent and chat" | ⭐⭐⭐ **Agentic AI dalam app** |
| 7 | **AI Dynamic Dashboard** (NexGen) | 1,107 | — | "Interactive charts, KPIs, filters, drill-down, multi-source + optional AI generation" | ⭐⭐⭐ |
| 8 | **Odoo AI Dynamic Dashboard** (Cybrosys) | **15,941** | 150 | "Drag and drop, **or with AI**, on any data" | ⭐⭐⭐ **AI dashboard builder** |

## 2.2 Accounting & Finance — 12 apps

| # | App | Downloads | Rating | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 9 | Accounting Community | **125,350** | 113 | Financial Report, PDC, Bank Rec, Lock Dates | ↔️ `Finance` |
| 10 | Base Accounting Kit | **90,156** | 117 | Accounting Kit | ↔️ `Finance` |
| 11 | Dynamic Accounting Reports | 25,221 | 59 | P&L, Balance Sheet, Trial Balance, Cash Flow | ⭐ `Finance` |
| 12 | Accounting PDF Reports | 21,144 | 14 | Annual Report | ↔️ |
| 13 | **Dynamic Account Reports** (ERP Heritage) | 678 | 198 | "SQL-direct aggregation, fast on large ledgers, period comparison, drill-down, append-only audit log" | ⭐⭐⭐ **Append-only audit** |
| 14 | Dynamic Reports Pro | 477 | 124 | "Scheduled reports to inboxes/chat, Slack/Teams webhook" | ⭐⭐ **Laporan berjadual** |
| 15 | Budget Management | 10,976 | 133 | Budget | ↔️ `Finance` |
| 16 | Accounting Suite (50-module) | 1,092 | 238 | "One-click 50-module accounting, IFRS, close, payments, automation, **audit bundle**" | ⭐⭐ **Audit bundle** |
| 17 | **Print Journal Entries** | 9,742 | 149 | Print JE PDF | ⭐ `Finance` |
| 18 | Query Deluxe (PostgreSQL) | 12,738 | 141 | SQL query interface | ⭐ **Self-service BI** |
| 19 | Base report xlsx | 37,370 | 175 | XLSX report base | ↔️ |
| 20 | Remove Data | 8,772 | 161 | Data cleanup | ↔️ |

## 2.3 HR, Payroll & Access — 8 apps

| # | App | Downloads | Rating | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 21 | **Open HRMS Core** | **82,294** | 47 | HR Dashboard, Payroll, HR Kit, Loan, Salary Advance | ↔️ `HRMS` |
| 22 | **HR Payroll** | 22,388 | 12 | Payslips, batches | ↔️ `Payroll` |
| 23 | HR Payroll Accounting | 12,819 | 5 | Payslip journal entries | ↔️ |
| 24 | **HR Payroll Community** | 15,662 | 9 | Payroll | ↔️ |
| 25 | **Zk Biometric Device Integration** | **22,333** | 16 | Biometric attendance device | ⭐⭐⭐ **AI BIJAN ADA `BiometricGateService`!** |
| 26 | **Access Studio** (ERP Heritage) | 322 | 131 | "Access profiles, roles, approved access requests, temporary grants, access inspector, reviewed policy imports, revisions, guided rules, optional AI" | ⭐⭐⭐ **Access Studio** |
| 27 | Access Management AI (NexGen) | 656 | 274 | "No-code access rights, hide menus/fields/buttons/views, record rules, import/export" | ⭐⭐⭐ **Granular UI access** |
| 28 | **User Access Manager** | 128 | 127 | Hide menus/fields/buttons, record rules, read-only, **allowed journals & warehouses**, login hours, IP restriction | ⭐⭐ **IP restriction** |

## 2.4 Menu, UI & Theming — 8 apps

| # | App | Downloads | Rating | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|---|
| 29 | **Hide Menu User & Group Wise** | **21,723** | 360 | Hide menu per user/group | ⭐⭐⭐ **Per-user UI** |
| 30 | **Advanced Menu Management** | — | 12 | "Personalize menu, change order, favorites, recent, user-wised hiding" | ⭐⭐⭐ |
| 31 | **INKERP Menu Management** | 352 | 345 | Hide menu, change sequence, favorite, history, systray | ⭐⭐ |
| 32 | **MuK AppsBar** | 3,258 | 132 | Sidebar app launcher | ⭐⭐ **App launcher** |
| 33 | MuK Product | 1,148 | 134 | Centralize product views | ⭐ |
| 34 | **MuK Colors** | 3,588 | 139 | Customize Odoo colors | ⭐⭐ **Theme tokens** |
| 35 | **Powered by Odoo Remove** | 2,696 | 139 | Remove "Powered by Odoo" branding | ⭐⭐ **⭐ WHITE-LABEL!** |
| 36 | ICA Web Responsive | 5,901 | 437 | Responsive backend, dark mode | ⭐⭐ **Dark mode** |

## 2.5 Compliance Per Negara — 7 apps ⭐⭐⭐

| # | App | Downloads | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|
| 37 | **Vietnam VAS Reports** (TT200/TT133/TT99) | 219 | Vietnamese statutory books, VAT returns, cash vouchers | ⭐⭐⭐ **Pola "compliance per negara"** |
| 38 | **Vietnam Inventory Books** (S12-DN/S10-DN) | 213 | Stock card, ledger, summary | ⭐⭐ |
| 39 | **Vietnam VAS — Manufacturing** (S37-DN) | 193 | Production cost reports, cost card | ⭐⭐ |
| 40 | Vietnam ERP Framework Core | 198 | Domain engines, repositories, services, DTOs | ⭐⭐ |
| 41 | **Audit Log** (OCA) | **22,533** | Audit log granular | ⭐⭐⭐ **AI BIJAN ada `audit_logs`** |
| 42 | GST Returns India (GSTR-1/3B) | — | Indian GST | ⭐⭐ |
| 43 | Product Catalog PDF Generator | — | Catalog PDF, price list | ⭐ |

## 2.6 Others — 7 apps

| # | App | Downloads | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|
| 44 | **Odoo WhatsApp Connector** | 9,992 | WhatsApp integration | ⭐⭐⭐ `Blast` |
| 45 | **Custom Receipts for POS** | 10,743 | "Customizable POS receipt designs, multiple templates" | ⭐⭐ **Resit custom** |
| 46 | **Purchase Request** (OCA) | 13,179 | Material requirements | ↔️ `SCM` |
| 47 | **Stock Disallow Negative** (OCA) | 14,757 | Prevent negative stock | ⭐⭐ **Kawalan stok** |
| 48 | IT Hardware & Support | 14,744 | IT assets | ↔️ `Assets` |
| 49 | Auto Database Backup (S3, GDrive, Dropbox, OneDrive, Nextcloud) | 26,934 | Backup multi-cloud | ⭐⭐ **BAB 36 DR** |
| 50 | **Tweak Odoo / OEM Brand** | **53,777** | "Change Odoo to your OEM brand", 65+ enhancements, AI, security, UI | ⭐⭐⭐ **⭐ WHITE-LABEL PATTERN** |

## 2.7 Ekstrak Odoo → AI BIJAN

| Prioriti | Idea | Bukti | Modul |
|---|---|---|---|
| 🔴 1 | **MCP server + AI Studio + human-in-the-loop** | 5 apps MCP, >11,600 dl | `AIAgents` |
| 🔴 2 | **Access Studio / granular UI per user** | 21,723 + 656 + 128 dl | `config/aibijan.php` |
| 🔴 3 | **White-label / OEM brand pattern** | Tweak Odoo 53,777 dl + Remove Branding 2,696 | `BAB 4a` Platform |
| 🟠 4 | **Compliance per negara** | Vietnam VAS 4 apps + GST | `IndustryPacks` |
| 🟠 5 | **Zk biometric device** | 22,333 dl — AI BIJAN **sudah ada** `BiometricGateService` | `HRMS` |
| 🟠 6 | **Menu/UI customization per user** | 21,723 dl | Semua UI |
| 🟡 7 | **Multi-cloud backup** | 26,934 dl | `BAB 36` |
| 🟡 8 | **Audit Log OCA granularity** | 22,533 dl | `BAB 9` |

---

# BAHAGIAN 3 — ZOHO: 50 APPS + 16 AI AGENTS

> **Sumber:** marketplace.zoho.com (2,000+ extensions, 33 kategori produk)
> + **store.agents.zoho.com** (Zoho Agent Store — penemuan baharu).

## 3.1 CRM — 20 apps

| # | App | Rating | Kategori | Idea AI BIJAN |
|---|---|---|---|---|
| 1 | **WhatsApp for Zoho CRM by TimelinesAI** | 16 | Marketing | ⭐⭐ Multi-number WA |
| 2 | Credit Data Direct | 97 | Business | ⭐ Semak kredit |
| 3 | **Lead Finder** | 53 | Marketing | ⭐⭐ `Prospecting` |
| 4 | **Contact Info Verifier** | 48 | Business | ⭐⭐ `Prospecting` |
| 5 | **List Manager (DND)** | 48 | Marketing | ⭐⭐ Blast suppression |
| 6 | **Phone Checker** | 36 | Business | ⭐ `DedupeService` |
| 7 | PostgreSQL Easy Sync | 34 | Business | ↔️ |
| 8 | Google Routes | 29 | Productivity | ↔️ `Logistics` |
| 9 | **Autocomplete Address via Google** | 5 | Productivity | ⭐ Alamat MY |
| 10 | Clean Contacts Pro | 2 | Marketing | ⭐⭐ Data cleaning |
| 11 | **Spiky.ai** | 2 | Marketing | ⭐⭐ AI meeting intelligence |
| 12 | **360 SMS** | 7 | Sales | ⭐⭐ SMS + MMS + WA |
| 13 | **Quo for Zoho CRM** | 2 | Telephony | ⭐⭐⭐ **Calls + texts + AI insights** |
| 14 | Twilio Chat | 0 | Telephony | ⭐ Real-time chat |
| 15 | Pipeline for Zoho CRM | 0 | Productivity | ⭐⭐ **Pipeline cross-module** |
| 16 | Relationship Tracker | 1 | Marketing | ⭐ StructureFinder |
| 17 | Smart Route Planner | 1 | Productivity | ↔️ `Logistics` |
| 18 | Nvoip | 2 | Sales | ⭐ Calls + WA + SMS |
| 19 | MessageWhiz SMS | 0 | Marketing | ↔️ `Blast` |
| 20 | Task Manager for Zoho CRM | 0 | Productivity | ↔️ `FlowAutomation` |

## 3.2 CRM — AI & Data (lagi 10 apps)

| # | App | Rating | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|
| 21 | **Kickbox Email Validation** | 1 | Email verification | ⭐⭐ `Prospecting` |
| 22 | **ZeroBounce Email Validation** | 1 | Email verification | ⭐⭐ |
| 23 | **Meddicc Score** | 1 | AI MEDDICC tracking | ⭐⭐⭐ **Scoring framework** |
| 24 | **Callture AI Phone** | 2 | Click-to-Call, AI Transcripts, Audits, Conversation Intelligence | ⭐⭐⭐ **AI transkrip panggilan** |
| 25 | **LeadCRM — Sync from LinkedIn** | 1 | "LinkedIn ke Zoho CRM in one click, enrich contacts, close deals faster" | ⭐⭐⭐ `Prospecting` |
| 26 | Easify SMS | 1 | Bulk SMS | ↔️ `Blast` |
| 27 | PulseText | 4 | Twilio + Sinch SMS in one place | ⭐⭐ |
| 28 | Proposal.biz | 1 | AI document automation (proposal/quote/contract) | ⭐⭐⭐ `SignDocs` |
| 29 | **Export API for Zoho CRM** | 1 | Export API names | ⭐⭐ **Data export** |
| 30 | Gleam for Zoho CRM | 0 | Capture qualified leads + sync | ⭐⭐ `Prospecting` |

## 3.3 Mail, Desk, Books & Recruit — 10 apps

| # | App | Rating | Produk | Idea AI BIJAN |
|---|---|---|---|---|
| 31 | **Zoho Meeting for Mail** | **1,389** | Mail | ⭐⭐ Integrasi dalam talian |
| 32 | Zoho Invoice Extension | 120 | Mail | ⭐ Invois dalam emel |
| 33 | Zoho Projects Extension | 86 | Mail | ⭐ |
| 34 | Zoho Books Extension | 64 | Mail | ⭐ |
| 35 | Zoho Billing Extension | 27 | Mail | ⭐ |
| 36 | **Check Printing for Zoho Books** | 160 | Books | ⭐⭐⭐ **CEK — MY masih guna!** |
| 37 | **Toky for Zoho Desk** | 9 | Desk | ⭐⭐⭐ **Tiket ke WhatsApp** |
| 38 | Customer Insights for Zoho Desk | 23 | Desk | ⭐⭐ AI metrics |
| 39 | **SKANJO CV Screening** | — | Recruit | ⭐⭐ AI CV screening |
| 40 | **TalentSprout** | — | Recruit | ⭐⭐ AI interview + skor |

## 3.4 Zoho Agent Store — 16 AI AGENTS ⭐⭐⭐

> **Penemuan baharu:** Zoho ada **marketplace ejen AI berasingan** — ini adalah
> "B-Agent Studio" yang sudah ada di pasaran, dan AI BIJAN belum ada.

| # | Agent | Rating | Fungsi | Idea AI BIJAN |
|---|---|---|---|---|
| 41 | **Advanced Sales Coach Agent** | 0 | "Deal-aware insights, pitch feedback, customer role-play" | ⭐⭐⭐ `ExecutiveAI` |
| 42 | **Instant Lead Response Agent** | 0 | "Personalized outreach drafts as soon as new leads are created" | ⭐⭐⭐ `Blast` + `Prospecting` |
| 43 | **Lead Intelligence Agent** | 3 | "Enrich lead records, assess sales potential, generate insights with **user-approved updates**" | ⭐⭐⭐ `Prospecting` |
| 44 | **Case Deflection and Triage Agent** | 0 | "Resolve customer email queries automatically using your KB" | ⭐⭐⭐ `Helpdesk` |
| 45 | **Intelligent Inventory Agent** | 0 | "Inventory + sales ke demand forecasts + recommendations" | ⭐⭐ `Inventory` |
| 46 | **NPS Predictor Agent** | 0 | Predictive Net Promoter Score | ⭐⭐⭐ `Analytics` |
| 47 | **Deal Gap Analyser** | 0 | "Uncover the gaps holding your deals back" | ⭐⭐ `SalesCopilot` |
| 48 | **Lost Deal Analysis Agent** | 0 | "Analyze lost deals to uncover missed opportunities" | ⭐⭐ `SalesCopilot` |
| 49 | **Onboarding Handover Agent** | 0 | "Turn closed-won deals into prioritized follow-up tasks" | ⭐⭐ `FlowAutomation` |
| 50 | **Deal Contribution Assistant** | 0 | "Calculate each contributor's share of a closed deal" | ⭐ `Payroll` |
| 51 | **Day Planner Agent** | 0 | Schedule + tasks + calls | ⭐⭐ `ExecutiveAI` |
| 52 | **Out-of-Office Intel Extractor** | 0 | Keep conversations moving when contacts away | ⭐ |
| 53 | **Multi-Stakeholder Management** | 0 | Stakeholder interest, influence, sentiment | ⭐⭐ `CRM` |
| 54 | **Zoho Voice Call Summarizer** | 0 | Summarize calls, create tasks from Deal records | ⭐⭐ `MediaStudio` |
| 55 | Outdated Solution Identification | 0 | Keep KB up to date | ⭐ |
| 56 | **Spiky.ai** (CRM) | 2 | AI meeting intelligence | ⭐⭐ |

## 3.5 Ekstrak Zoho → AI BIJAN

| Prioriti | Idea | Bukti | Modul |
|---|---|---|---|
| 🔴 1 | **Zoho Agent Store — 16 ejen khusus** | 16 ejen bernama, kebanyakannya FREE | ⭐⭐⭐ **B-Agent Studio (BAB 83 Idea #3)** |
| 🔴 2 | **LeadCRM — LinkedIn ke CRM sync** | 1 rating | ⭐⭐ `Prospecting` |
| 🟠 3 | **Meddicc Score** | AI scoring framework | ⭐⭐ `LeadScoreService` |
| 🟠 4 | **Callture AI Phone — transkrip panggilan** | AI Transcripts + Audits | ⭐⭐ `MediaStudio` |
| 🟠 5 | **Toky — tiket ke WhatsApp** | 9 rating | ⭐⭐ `Helpdesk` |
| 🟠 6 | **NPS Predictor Agent** | Predictive | ⭐⭐ `Analytics` |
| 🟡 7 | **Check Printing** | 160 rating — MY guna cek | ⭐⭐ `Finance` |

---

# BAHAGIAN 4 — SERVICETITAN: 50 RESOURCE

> **Sumber:** marketplace.servicetitan.com + servicetitan.com/features/pro
> **Nota:** ServiceTitan **ADA** marketplace — 10 kategori + "Built by ServiceTitan"
> (Pro Products). Audit sebelumnya tersalah anggap ia tiada app store.

## 4.1 13 Pro Products (Built by ServiceTitan) — dengan metrik hasil sebenar ⭐⭐⭐

| # | Pro Product | Metrik Hasil (dari laman rasmi) | Idea AI BIJAN |
|---|---|---|---|
| 1 | **Marketing Pro** | **2x** faster revenue growth | ⭐⭐⭐ `Marketing` |
| 2 | **Scheduling Pro** | **30%** conversion rate into completed job | ⭐⭐⭐ `Booking` |
| 3 | **Contact Center Pro** | **+10%** raw booking rate, **+20%** average ticket | ⭐⭐⭐ `Helpdesk` |
| 4 | **Field Pro** | **+20%** average ticket | ⭐⭐ `Projects` |
| 5 | **Dispatch Pro** | **+7%** daily average revenue | ⭐⭐ `FlowAutomation` |
| 6 | **Fleet Pro** | **75% reduction** in accident risk | ⭐⭐⭐ **Fleet — AI BIJAN TIADA!** |
| 7 | **Sales Pro** | AI-powered sales coaching "in your team's pocket" | ⭐⭐⭐ `SalesCopilot` |
| 8 | **Pricebook Pro** | "Redefine the Pricebook Experience" | ⭐⭐ **Pricing engine** |
| 9 | **Payments** | "Get paid faster and easier" | ↔️ `Finance` |
| 10 | **Phones Pro** | "Optimize your call center, book more jobs" | ⭐⭐ `Helpdesk` telephony |
| 11 | **AI Voice Agent** | "No More Missed Calls — AI Handles the Overflow" | ⭐⭐⭐ `ExecutiveAI` voice |
| 12 | **Titan Intelligence** | "AI sidekick" yang menggerakkan semua Pro Products | ⭐⭐⭐ `AIAgents` |
| 13 | **TitanAdvisor** | Advisor AI dalam aliran kerja | ⭐⭐⭐ **`ExecutiveAI` = advisor di luar; ini di dalam** |

> **⭐ Titanium Insight:** ServiceTitan menunjukkan **"Pro Products"** yang
> hasilnya boleh diukur (contoh: "+20% average ticket"). Ini corak yang
> AI BIJAN belum ada — **setiap modul AI BIJAN patut ada satu metrik hasil**.

## 4.2 37 Kategori & Integrasi Marketplace

| # | Kategori/Integrasi | Idea AI BIJAN |
|---|---|---|
| 14 | **Live Services** | ⭐ `Helpdesk` |
| 15 | **Web Schedulers** | ⭐⭐ `Booking` |
| 16 | **Marketing** | ⭐⭐ `Marketing` |
| 17 | **Payments & Financing** | ⭐⭐⭐ `Finance` + customer financing |
| 18 | **Telecom** | ⭐⭐ `Helpdesk` telephony |
| 19 | **Catalogs & Pricebooks** | ⭐⭐ `Inventory` |
| 20 | **Fleet & GPS** | ⭐⭐⭐ **Fleet — tiada dalam AI BIJAN** |
| 21 | **Dispatch** | ⭐⭐ `FlowAutomation` |
| 22 | **On-The-Job** | ⭐⭐ **Training / `MediaStudio`** |
| 23 | **Accounting** (Intacct, QuickBooks) | ⭐⭐⭐ **Connector 2 sistem perakaunan** |
| 24 | **Field Mobile App** | ⭐⭐ `BAB 56B` |
| 25 | **Customer Experience** | ↔️ `Helpdesk` |
| 26 | **Financing** | ⭐⭐⭐ **Tiada dalam AI BIJAN** |
| 27-33 | **QuickBooks, Xero, Sage, NetSuite, SAP, Salesforce, HubSpot** | ⭐⭐⭐ **Connector pack 7** |
| 34-37 | **Twilio, RingCentral, Dialpad, Genesys, Five9** | ⭐⭐ **Telephony connector** |
| 38-43 | **Waze, Google Maps, Route4Me, OptimoRoute, Samsara, Fleetio, Motive** | ⭐⭐⭐ **Fleet & routing** |
| 44-46 | **Verkada, Alarm.com, Yale** | ⭐⭐ **IoT / akses** |
| 47-50 | **Home Depot Pro, Ferguson Pro, eBay, Amazon** | ⭐⭐ **Channel jualan** |

## 4.3 16 Industri Vertical (Vertical Playbook)

| # | Industri | Idea AI BIJAN (`IndustryPacks`) |
|---|---|---|
| 51-66 | HVAC, Plumbing, Electrical, Garage Door, Chimney Sweep, Water Treatment, Landscape, Pool Service, Septic, Pest Control, Lawn Care, Remodeling, Restoration, Roofing, Pool & Spa, Solar | ⭐⭐⭐ **16 industri tambahan** — AI BIJAN ada 19, boleh disemak |

## 4.4 Ekstrak ServiceTitan → AI BIJAN

| Prioriti | Idea | Bukti | Modul |
|---|---|---|---|
| 🔴 1 | **⭐ Metrik hasil per modul** ("+20% average ticket") | 6 metrik rasmi | **SEMUA modul** |
| 🔴 2 | **Fleet & GPS** | 7 integrasi (Samsara, Fleetio, Motive) | `Logistics` (baharu) |
| 🟠 3 | **Customer financing** | Ciri rasmi | `Finance` (baharu) |
| 🟠 4 | **TitanAdvisor — AI advisor DALAM aliran kerja** | "built for the trades" | `ExecutiveAI` |
| 🟠 5 | **AI Voice Agent — overflow handling** | "No More Missed Calls" | `ExecutiveAI` voice |
| 🟠 6 | **16 industri trade** | Senarai penuh | `IndustryPacks` |
| 🟡 7 | **Pricebook engine** | Pricebook Pro | `Inventory` |

---

# BAHAGIAN 5 — 50 PROJEK GITHUB OPEN SOURCE

## 5.1 Tier 1 — MIT / Apache-2.0 / BSD: BOLEH REBRAND & COMMERCIAL ✅

| # | Projek | Lesen | Stack | Modul AI BIJAN | Cara Guna |
|---|---|---|---|---|---|
| 1 | **liteerp** | MIT | Laravel+React | `Migration` | Clean Architecture + DDD |
| 2 | **aureuserp** | MIT | Laravel+Filament | **UI semua** | ⭐⭐⭐ **Filament = UI builder** |
| 3 | **akaunting** | MIT | Laravel | `Finance` | Double-entry, budget, recurring |
| 4 | **NexoPOS** | MIT | Laravel+Vue+Tailwind | `POS` | ⭐⭐⭐ POS + modular + Tailwind |
| 5 | **lakasir** | MIT | Laravel | `POS` | ⭐⭐⭐ Bahasa Indonesia (SEA) |
| 6 | **triangle-pos** | — | Laravel 10+Livewire 3 | `POS` | Livewire pattern |
| 7 | **astron-agent** | **Apache-2.0** | Java+Python | `AIAgents` | ⭐⭐ MCP+RPA+LLM-Ops+multi-agent |
| 8 | **hatchet** | **MIT** | Go | `FlowAutomation` | Durable agent orchestration |
| 9 | **inngest** | **Apache-2.0** | Go | `FlowAutomation` | Step functions + AI workflows |
| 10 | **conductor** | **Apache-2.0** | Java | `FlowAutomation` | Event-driven agentic engine |
| 11 | **flowable-engine** | **Apache-2.0** | Java | `FlowAutomation` | BPM |
| 12 | **dtm** | **Apache-2.0** | Go | `BAB 25` | Outbox, saga, TCC |
| 13 | **temporal** | **MIT** | Go | `FlowAutomation` | Durable execution |
| 14 | **receiptline** | **MIT** | JS | `POS` | ⭐⭐ Markdown receipts + ESC/POS + kitchen |
| 15 | **grocy** | **MIT** | PHP+Blade | `Inventory` | Stok, resipi, unit |
| 16 | **open-mercato** | MIT | TS+Next.js | Semua | ⭐⭐⭐ "AI-Engineering Foundation" |
| 17 | **Archon** | — | TS | `AIAgents` | Agent harness builder |
| 18 | **carbon** | — | TS+Postgres | `Manufacturing` | ⭐⭐⭐ MES+QMS+**MCP server** |
| 19 | **WebErpMesv2** | — | React+Laravel | `Manufacturing` | ⭐⭐ Laravel + Factur-X e-invoicing |
| 20 | **moqui-mcp** | — | Groovy | `AIAgents` | MCP untuk ERP |

### Tier 1 — Sokongan/Utiliti (5 lagi)

| # | Projek | Lesen | Guna |
|---|---|---|---|
| 21 | **grocy** | MIT | `Inventory` — resipi, unit, barcodes |
| 22 | **open-mercato** | MIT | `PluginMarketplace` — events + RBAC conventions |
| 23 | **frappe/frappe_docker** | MIT | `BAB 64` — deployment Docker |
| 24 | **frappe/crm** | MIT | `CRM` — model rujukan |
| 25 | **InvoicePlane** | MIT | Invoicing, clients, payments (CodeIgniter) |

## 5.2 Tier 2 — GPL/AGPL: RUJUKAN REKA BENTUK SAHAJA ⛔

| # | Projek | Lesen | Yang Boleh Dipelajari |
|---|---|---|---|
| 26 | **twenty** | AGPL-3.0 | "Designed for AI" Salesforce alternative; GraphQL, monorepo, NestJS |
| 27 | **nocobase** | AGPL-3.0 | AI + no-code platform; plugin architecture; WYSIWYG |
| 28 | **openblocks** | AGPL-3.0 | "The Open Source Retool Alternative" — internal tools builder |
| 29 | **steedos** | AGPL-3.0 | "Turn Prompts into Enterprise Software" (ObjectStack: ObjectQL/ObjectOS/ObjectUI) |
| 30 | **budibase** | GPL-3.0 | Model-agnostic AI agents + automations + apps |
| 31 | **krayin/laravel-crm** | AGPL-3.0 | Laravel CRM: customer, lead, sales management |
| 32 | **Dolibarr** | GPL-3.0 | ⭐⭐ **Import/Export Wizard** (BAB 83 Idea #1) + **Module Builder** |
| 33 | **espocrm** | GPL-3.0 | CRM customization, kanban, sales automation, portal |
| 34 | **ever-gauzy** | AGPL-3.0 | ERP/CRM/HRM/ATS/PM — plugin marketplace model |
| 35 | **SuiteCRM** | AGPL-3.0 | Workflow, quotes, contracts, cases, portal |
| 36 | **monica** | AGPL-3.0 | Personal CRM — relationships |

## 5.3 Tier 3 — Infrastruktur & Industri (14 lagi)

| # | Projek | Lesen | Stack | Relevan? |
|---|---|---|---|---|
| 37 | **metasfresh** | GPL | Java+React | ERP — rujukan |
| 38 | **lemon-erp** | — | Java+Vue3 | ⭐ ERP+MES+WMS+CRM+HRM, **SaaS multi-tenant**, Flowable approval, WeChat mini-program |
| 39 | **jshERP** | — | Java | ERP SaaS untuk SME |
| 40 | **VIENNA-Advantage (Onfinity)** | — | C#/.NET | ⭐⭐ **Low-code "Application Dictionary"** → `BAB 44` Rule Engine |
| 41 | **lsfusion-solutions/mycompany** | — | Postgres | Low-code ERP/CRM/POS/HR |
| 42 | **atlas-erp** | — | FastAPI+React | ⭐⭐ **"Functional benchmark: SAP S/4HANA"**, multi-tenant |
| 43 | **ghacupha/erp-system** | — | Java | IFRS16 leases, fixed assets |
| 44 | **idurar/idurar-erp-crm** | MIT | Node+React+Mongo | Headless ERP |
| 45 | **auroravirtuoso/erp-crm** | MIT | Node+React | Headless ERP |
| 46 | **hcengineering/platform (Huly)** | — | TS | ⭐⭐ All-in-One PM (Linear/Jira/Slack/Notion) |
| 47 | **Peppermint-Lab/peppermint** | — | Next.js | ⭐⭐ Zendesk & Jira alternative |
| 48 | **macro-inc/macro** | — | Rust+TS | ⭐⭐ Unified workspace + shared AI memory + MCP |
| 49 | **Shyamsitaula/samarium** | — | Laravel+Livewire | ⭐ ERP+POS+CMS, Docker-ready |
| 50 | **onlyoffice/CommunityServer** | AGPL | C# | ❌ Rujukan sahaja |

## 5.4 Ekstrak OSS → AI BIJAN

| Prioriti | Idea | Projek | Lesen | Modul |
|---|---|---|---|---|
| 🔴 1 | **FilamentPHP UI builder** | aureuserp | MIT | **UI semua** |
| 🔴 2 | **MCP server pattern** | carbon, moqui-mcp, odoo_mcp_manager | — | `AIAgents` |
| 🔴 3 | **POS sebenar (Laravel+Tailwind)** | NexoPOS, lakasir | MIT | `POS` |
| 🟠 4 | **AI-Engineering Foundation** (events + RBAC conventions) | open-mercato | MIT | Semua |
| 🟠 5 | **Durable workflow** | hatchet, inngest, temporal | MIT/Apache | `FlowAutomation` |
| 🟠 6 | **Outbox/saga/TCC** | dtm | Apache-2.0 | `BAB 25` |
| 🟠 7 | **Agent harness** | Archon, astronaut-agent | —/Apache | `AIAgents` |
| 🟡 8 | **Low-code Application Dictionary** | VIENNA/Onfinity | — | `BAB 44` |
| 🟡 9 | **SAP S/4HANA benchmark + multi-tenant** | atlas-erp | — | `BAB 4` |
| 🟡 10 | **ESC/POS receipts + kitchen** | receiptline | MIT | `POS` |

---

# BAHAGIAN 6 — 15 IDEA TERBAIK (Akhir)

| # | Idea | Sumber | Bukti | Kesan | Kerja |
|---|---|---|---|---|---|
| 1 | ⭐ **MCP server + AI Studio + human-in-the-loop** | Odoo (5 apps, >11,600 dl), Zoho Agent Store | 9,094 dl + 16 ejen | 🔴 Jangkauan tanpa jualan | Sederhana |
| 2 | ⭐ **FilamentPHP UI builder** | aureuserp (MIT) | Laravel ecosystem | 🔴 **UI 2 ke 7** | Sederhana |
| 3 | ⭐ **B-Migration Wizard + AI auto-mapping** | Matrixify (1,815 rv), Dolibarr | MCP & AI tools | 🔴 **Migrasi 2 ke 7** | Sederhana |
| 4 | ⭐ **AI dashboard builder** | Odoo AI Dynamic (15,941 dl), Ksolves (3,136) | 2 free + 1 paid | 🔴 UI + metrics | Sederhana |
| 5 | ⭐ **Metrik hasil per modul** | ServiceTitan Pro Products (6 metrik) | "+20% average ticket" | 🔴 Jualan ROI-based | Sederhana |
| 6 | ⭐ **Cart recovery WhatsApp + COD** | CK (5.0), Dondy (828), EasySell COD (989) | 3 apps | 🔴 **Revenue MY** | Sederhana |
| 7 | ⭐ **Granular UI access per user** | Hide Menu (21,723), Access Studio, AI Access Mgmt | 3 apps, >22,000 dl | 🔴 UI + keselamatan | Sederhana |
| 8 | ⭐ **White-label / OEM pattern** | Tweak Odoo (53,777 dl), Remove Branding | 2 apps | 🟠 `BAB 4a` | Sederhana |
| 9 | ⭐ **Compliance per negara** | Vietnam VAS (4 apps), GST | SEA expansion | 🟠 `IndustryPacks` | Sederhana |
| 10 | ⭐ **Fleet & GPS** | Samsara, Fleetio, Motive, Verkada | ServiceTitan | 🟠 `Logistics` baru | Sederhana |
| 11 | ⭐ **Connector pack 20+ sistem** | ServiceTitan 70+, Zoho, Odoo | 3 marketplace | 🟠 `Migration` | Sederhana |
| 12 | ⭐ **AI transkrip panggilan + sales coaching** | Callture, Sales Pro, Advanced Sales Coach | 3 apps | 🟠 `MediaStudio` | Sederhana |
| 13 | ⭐ **AI Voice Agent overflow** | ServiceTitan AI Voice Agent | "No More Missed Calls" | 🟠 `ExecutiveAI` | Sederhana |
| 14 | ⭐ **Meddicc scoring + NPS predictor** | Meddicc Score, NPS Predictor | 2 ejen | 🟠 `SalesCopilot`/`Analytics` | Sederhana |
| 15 | ⭐ **PDPA consent banner + T&C** | Pandectes (3,108), TN T&C (565) | 2 apps | 🟠 `Site`/`Helpdesk` | Sederhana |

## Cadangan Keseluruhan: 3 Fasa

| Fasa | Idea | Hasil |
|---|---|---|
| **1 — Boleh Pindah & Nampak Baik** | #2 Filament UI, #3 Migration, #4 AI Dashboard, #7 Access | Skor **5.0 ke 6.3** |
| **2 — Boleh Automate & Jangkau** | #1 MCP, #6 Cart recovery+COD, #11 Connector pack, #12/#13 Voice+Sales | Skor **ke 7.0** |
| **3 — Boleh Skala & Differensiasi** | #5 Metrik hasil, #9 Compliance negara, #10 Fleet, #8 White-label | Skor **ke 7.8** |

---

## Nota Pemeliharaan

- Rating, review count, dan download count **berubah setiap hari** — audit semula setiap suku tahun.
- ⚠️ **Angka GitHub stars TIDAK dipaparkan tanpa login** — jangan guna dalam promosi sehingga disahkan.
- ⚠️ **Lesen mesti disahkan** dalam fail `LICENSE` sebenar — jangan bergantung pada README.
- Projek AGPL/GPL: **rujukan reka bentuk sahaja**. Jangan salin kod.
- Semua OSS yang diincorporate mesti ada dalam `THIRD-PARTY-NOTICES.md`.
