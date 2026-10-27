# AI BIJAN — Audit Induk Bersepadu, Lengkap dan Terperinci

**Tarikh dokumen:** 27 Oktober 2026 (disemak)  
**Asas semakan:** pakej `docs.zip` + repositori `ai-bijan-saas`  
**Tujuan:** menyatukan audit dokumen, blueprint, inventori modul, seni bina, jurang, pertindihan, risiko, integrasi, pematuhan dan pelan tindakan dalam satu fail Markdown.  
**Status dokumen:** `AUTHORITATIVE` — audit reka bentuk + status platform.  
**Baseline angka:** [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md) — **satu-satunya sumber kebenaran**; jangan salin angka dari mana-mana dokumen lain.

> **Penting — disiplin bukti:** angka seperti “962 ujian lulus”, “346 jadual”, “1,518 endpoint” dan “59 modul dikodkan” dilaporkan dalam dokumen sumber bertarikh 26 Oktober 2026. Dalam audit ini, ia diklasifikasikan sebagai **dilaporkan telah disahkan oleh dokumen sumber**, bukan dakwaan bahawa audit ini sendiri telah menjalankan kod atau ujian tersebut. Modul `Reseller` dinyatakan sebagai spesifikasi dalam dokumen sumber tersebut, tetapi **telah dikodkan** dalam `app/Modules/Billing/` selepas semakan 27 Oktober 2026. Ciri, integrasi luaran, pematuhan dan readiness produksi tidak boleh dianggap siap hanya kerana disebut dalam blueprint.
>
> **Semakan 27 Okt 2026 — baseline disahkan semula.** `docs/AUDIT-BASELINE.md` kini menyenaraikan angka **sebenar** yang diukur daripada repositori. Angka laluan API (1,518) dan ujian (962) dalam dokumen asal **tidak tepat** — kiraan statik menunjukkan **~872** laluan `api/v1` dan **~1,119** kaedah ujian. Kesemua dokumen yang melaporkan angka lain mesti merujuk `AUDIT-BASELINE.md`, bukan menyalin angka secara manual.
>
> **Status platform: `DEVELOPMENT`** — belum production-ready. Lihat [`README.md`](../README.md).

---

## Kandungan

1. Ringkasan eksekutif
2. Skop, kaedah dan had bukti
3. Baseline dan percanggahan dokumen
4. Keputusan seni bina yang patut dikekalkan
5. Inventori modul dan sempadan tanggungjawab
6. Pertindihan modul dan keputusan pemilikan
7. Jurang produk dan keupayaan rentas platform
8. Audit AI, Agentic Development Environment (ADE) dan automasi
9. Audit pesanan, stok, omnichannel dan industri
10. Audit pembayaran, billing, token dan fintech
11. Keselamatan, multi-tenancy, PDPA dan pematuhan Malaysia
12. Dashboard dan pengalaman pengguna
13. Monetisasi, reseller dan risiko margin
14. Audit persaingan dan tuntutan pemasaran
15. Matriks keutamaan P0–P3
16. Pelan pelaksanaan dan kriteria penerimaan
17. Definition of Done dan senarai semak pelepasan
18. Daftar keputusan seni bina
19. Sumber dokumentasi yang digabungkan
20. Lampiran A — katalog modul sumber
21. Lampiran B — metrik dan arahan pengesahan yang dirujuk dokumen

---

## 1. Ringkasan eksekutif

AI BIJAN ditakrifkan sebagai **AI-Native Business Operating System** untuk perniagaan Malaysia, dengan seni bina Laravel standalone, SaaS multi-tenant, modul perniagaan yang saling berintegrasi dan lapisan AI/agen yang berpusat. Arah produk ini kukuh jika platform dibina sebagai satu sistem dengan kontrak domain yang jelas — bukannya sekadar 58 aplikasi yang berkongsi menu.

Dokumentasi yang diberikan menunjukkan satu katalog **59 modul yang dikodkan**, termasuk modul `Billing` yang memuatkan `Reseller` (komisen berulang) — **telah dikodkan** di `app/Modules/Billing/` (9 jadual, 6 laluan API, 32 ujian) selepas semakan 27 Okt 2026. Angka baseline yang dilaporkan dalam dokumen asal (346 jadual, 1,518 laluan API, 962 ujian lulus, 5,033 assertion, 66 resource Filament, 212 permission) **telah disahkan semula daripada repositori** pada 27 Okt 2026 dan **sebahagian besar tidak tepat** — lihat `docs/AUDIT-BASELINE.md` untuk angka sebenar. Ciri, integrasi luaran, pematuhan dan readiness produksi tidak boleh dianggap siap hanya kerana disebut dalam blueprint.

### Kesimpulan utama

1. **Kekuatan strategik:** seni bina Laravel standalone, kawalan tenant, RBAC, AI Gateway tunggal, inventori modul luas, pesanan bersatu, kandungan/SEO dan orientasi Malaysia.
2. **Risiko paling besar:** perbezaan antara *modul wujud* dengan *aliran perniagaan hujung-ke-hujung yang benar-benar berfungsi*. Bilangan route, jadual dan ujian tidak membuktikan integrasi menyeluruh, kestabilan, keselamatan atau kesediaan komersial.
3. **Kawalan kritikal:** satu sumber kebenaran untuk tenant, identiti, entitlement, bil, token AI, audit trail, event dan kontrak API. Jangan biarkan setiap modul membina sistem billing, stok, pelanggan atau penghantaran mesejnya sendiri.
4. **Pertindihan yang perlu dikawal:** CRM vs Marketing vs Prospecting vs SalesCopilot; FlowAutomation vs WorkflowStudio vs ADE; POS vs Orders vs Omnichannel vs Marketplace; Finance vs Invoicing vs Expense vs COGS vs Payroll; AIAgents vs PersonalAssistant vs ExecutiveAI vs AgentStudio vs Boardroom.
5. **Risiko integrasi luaran:** WhatsApp Business Platform, penerbitan sosial, gateway pembayaran, e-Invois LHDN, model AI, MCP dan penyedia eSIM mesti mempunyai adapter, status sambungan, pengurusan secret, retry, idempotency dan ujian kontrak. Dokumentasi sahaja bukan bukti bahawa akaun pengeluaran atau kelulusan penyedia telah diperoleh.
6. **Fintech:** mulakan sebagai integrator/perisian dengan gateway berlesen dan settlement oleh rakan kongsi. Bezakan jelas antara perisian kewangan, fasilitasi pembayaran dan aktiviti kewangan terkawal. Jangan menyimpan atau memindahkan dana pelanggan sendiri tanpa analisis lesen dan struktur undang-undang.
7. **Reseller:** model komisen berulang mesti mempunyai simulasi margin, polisi pembatalan/chargeback, clawback, cukai, payout dan had liabiliti sebelum diaktifkan.
8. **Langkah seterusnya:** tetapkan satu baseline repositori, selesaikan percanggahan angka, tetapkan kontrak modul dan event, uji pengasingan tenant, kemudian laksanakan aliran perniagaan kritikal dari mula hingga akhir.

### Skor kesiapan berdasarkan bukti dokumentasi sahaja

| Dimensi | Penilaian audit dokumen | Maksud |
|---|---|---|
| Arah produk | Jelas | Skop dan aspirasi produk diterangkan |
| Keluasan fungsi | Tinggi pada peringkat katalog | Banyak domain disebut; kedalaman setiap aliran perlu dibuktikan |
| Kejelasan sempadan modul | Sederhana | Banyak modul, beberapa tanggungjawab bertindih |
| Kebolehkesanan bukti | Sederhana | Ada angka dan nama ujian, tetapi audit ini tidak menjalankan repositori |
| Kesiapan integrasi luaran | Belum boleh disahkan | Perlu bukti akaun, webhook, kontrak, ujian sandbox/production |
| Kesiapan pematuhan | Belum boleh disahkan | Perlu penilaian undang-undang, konfigurasi, polisi dan bukti operasi |
| Kesiapan production | Belum boleh disahkan oleh pakej dokumen sahaja | Perlu deployment, monitoring, pemulihan, keselamatan dan ujian beban |

---

## 2. Skop, kaedah dan had bukti

### 2.1 Bahan yang diperiksa

Pakej `docs.zip` mempunyai 113 entri, termasuk dokumen akar dan arkib. Dokumen utama yang digunakan untuk menyusun audit ini:

- `AI-BIJAN-MASTER-BLUEPRINT.md` — blueprint induk (fail sebenar: `AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md`, nama warisan). Metadata di dalam fail menyatakan versi blueprint 9.6 pada 27 Oktober 2026.
- `KATALOG-MODUL-AI-BIJAN.md` — inventori 59 modul dan penerangan ringkas.
- `SISTEM-AI-BIJAN.md` — penerangan sistem.
- `SISTEM-PEMBANGUNAN-DARI-MULA.md` — panduan pembangunan, prinsip seni bina dan senarai semak.
- `BLUEPRINT-SISTEM-LENGKAP.md` — reverse engineering dan struktur sistem.
- `MODUL-AI-INTEGRASI.md` — hubungan modul AI dan peraturan penggunaan AI Gateway.
- `AUDIT-DOKUMEN.md` — audit dokumentasi dan angka yang dilaporkan.
- `TODO.md` — senarai kerja tertangguh dan sejarah status.
- `ANALISIS-KOMPETITIF.md`, `AUDIT-PESAING-LANJUTAN.md`, `competitive-analysis-report.md`, `ai-bijan-vs-world-comparison.md` dan `PETA-PESAING-34-MODUL.md`.
- `RESELLER-KOMISEN-BERULANG.md` — model reseller dan spesifikasi modul Reseller.
- Dokumen khusus: `BUSINESS-GROWTH-ENGINE.md`, `CADANGAN-IMPAK-TINGGI.md`, `KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md`, `PROSPEK-BLAST-CLOSE.md`, `REVERSE-ENGINEERING-200-APPS-50-OSS.md`, `README-OMNICHANNEL.md`, `README-INDUSTRY-PACKS.md`, `README-LOGISTICS.md` dan `README-SIGN-DOCS.md`.

### 2.2 Kaedah pengelasan

Setiap kenyataan perlu diletakkan dalam satu daripada empat kelas:

- **D — Didokumenkan:** dinyatakan dalam dokumen, spesifikasi atau katalog.
- **K — Kod dilaporkan wujud:** dokumen audit menyatakan fail, kelas, jadual, route atau ujian telah diperiksa.
- **T — Telah diuji menurut dokumen:** dokumen menyebut ujian atau bukti runtime tertentu.
- **P — Perancangan / belum disahkan:** spesifikasi, roadmap, integrasi belum dibuktikan atau status bercanggah.

Kelas K/T tidak dianggap disahkan semula oleh audit ini tanpa repositori dan arahan ujian sebenar.

### 2.3 Had audit

Audit ini tidak mengesahkan secara langsung:

- kod aplikasi, commit Git, migration sebenar atau keadaan pangkalan data;
- output semasa `php artisan test`, `php artisan route:list`, `composer audit` atau skrip audit;
- integrasi aktif dengan Meta, WhatsApp, gateway pembayaran, LHDN, penyedia AI atau platform sosial;
- kebenaran komersial, lesen open-source terkini, lesen penyedia atau kontrak reseller;
- penetration test, load test, disaster recovery, backup restore atau konfigurasi production.

Oleh itu, jangan menggunakan laporan ini sebagai pengganti security audit, legal review, ujian acceptance atau pengesahan deployment.

---

## 3. Baseline dan percanggahan dokumen

### 3.1 Baseline — disahkan semula 27 Okt 2026

> **Sumber rasmi:** [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md).
> Nilai "sebenar" di bawah adalah **kiraan statik daripada sistem fail** pada
> 27 Okt 2026. Nilai yang memerlukan PHP/DB diletakkan di baris berlabel
> *"perlu PHP"*.

| Metrik | Nilai lama (dilaporkan) | Nilai sebenar (27 Okt 2026) | Cara diukur |
|---|---:|---:|---|
| Modul dikodkan | 58 | **59** | direktori di `app/Modules/` |
| `Reseller` (dalam `Billing/`) | "spesifikasi sahaja" | **DIKODKAN** | `app/Modules/Billing/` + 32 ujian |
| Jadual DB | 346 | **368** *(kiraan migration)* / **perlu PHP** *(sebenar)* | fail migration / `sqlite_master` |
| Laluan API (`api/v1`) | 1,518 | **~872** | router Laravel |
| Migration | 49 | **58** | fail di `database/migrations/` |
| Kaedah ujian | 962 lulus | **~1,119 kaedah** / **perlu PHP** *(lulus sebenar)* | `test*` + `#[Test]` + `@test` |
| Ujian skip | 4 | **perlu PHP** | setiap skip mesti ada sebab + tarikh tamat |
| Assertion | 5,033 | **perlu PHP** | bukan ukuran liputan fungsi |
| Model | 292 | **310** | `app/Modules/**/Models/*.php` |
| Service | 166 | **174** | `app/Modules/**/Services/*.php` |
| Controller modul | 127 | **134** | `app/Modules/**/Http/Controllers/*.php` |
| Resource Filament | 66 | **65** | direktori di `app/Filament/Resources/` |
| Widget dashboard | 6 | **6** | `app/Filament/Widgets/*.php` |
| Permission | 212 | **212** *(perlu PHP untuk sahkan)* | jadual `permissions` |
| Versi Laravel | 12.70.1 | **12.70.1** | `composer.lock` |
| Advisori Composer | 0 | **0** *(perlu PHP untuk sahkan)* | `composer audit` |

**Cara regenerate:** `php skrip/jana-baseline.php --tulis`. CI akan gagal jika
`AUDIT-BASELINE.md` tidak sepadan dengan kod.

### 3.2 Percanggahan yang mesti dibetulkan

**P0 — nama fail dan versi dokumen tidak sepadan.** *(SEPARUH DISELESAI 27 Okt 2026.)* Fail bernama `AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` tetapi header kini menyatakan **v9.6**. Nama kanonik `AI-BIJAN-MASTER-BLUEPRINT.md` telah dibina sebagai alias rujukan dan semua rujukan dalam `docs/` telah dikemas kini. **Baki:** fail fizikal masih mengandungi nama warisan — perlu `git mv` untuk meminda dan memadam.

**P0 — sejarah TODO bercampur dengan status semasa.** `TODO.md` mempunyai header baseline semasa yang melaporkan 59 modul dan 962 ujian, tetapi seksyen status ringkasnya masih menyebut angka lama seperti 39 modul, ~1,015 endpoint dan 314 ujian. Walaupun dokumen memberi amaran bahawa angka sejarah ialah angka pada masa entri ditulis, bahagian ringkasan yang kelihatan seperti status semasa boleh mengelirukan. Pindahkan semua sejarah ke `CHANGELOG` atau labelkan dengan tarikh yang jelas.

**P1 — kiraan modul berubah mengikut sempadan.** Ada angka 58 modul dikodkan dan 59 apabila `Reseller` dimasukkan. `Reseller` kini **telah dikodkan** dalam `app/Modules/Billing/` (32 ujian), jadi denominator Rasmi ialah **59 modul dikodkan**. Semua dashboard status dan dokumen jualan mesti menyatakan denominator dengan tepat: “59 modul dikodkan (termasuk Reseller)”.

**P1 — nama modul berbeza antara blueprint, katalog dan dokumen lama.** *(SEPARUH DISELESAI 27 Okt 2026.)* `docs/archive/README.md` kini menyatakan `ARCHIVED / NOT AUTHORITATIVE` dan memaut blueprint kanonik. **Baki:** blueprint versi lama dalam `archive/blueprint-lama/` masih mengandungi aksara CJK terbenam (cth `自建`, `提醒`) — perlu cleaned dalam audit bahasa berasingan.

**P1 — status ujian bukan status production.** “~1,119 kaedah ujian” tidak membuktikan semua integrasi sebenar, semua peranan, multi-tenant leakage, concurrency stok, webhook pembayaran atau pemulihan kegagalan telah diuji. Nyatakan skop suite dan ujian yang tidak diliputi.

**P1 — dakwaan “liputan semua modul” melalui 65 resource Filament perlu diperjelas.** Satu resource tidak semestinya bermakna semua aliran modul telah diliputi. Buat matriks modul → API → permission → UI → test → dokumentasi.

### 3.3 Pembetulan dokumentasi wajib

| # | Tindakan | Status 27 Okt 2026 |
|---|---|---|
| 1 | Tetapkan `AI-BIJAN-MASTER-BLUEPRINT.md` sebagai nama kanonik | ✅ Alias dibina; semua rujukan dikemas kini. **Baki:** `git mv` fail fizikal. |
| 2 | Tambah `VERSION`, `LAST_AUDITED_AT`, `GIT_COMMIT`, `AUDIT_SCOPE`, `GENERATED_METRICS`, `STATUS` | ✅ `VERSION` + `TARIKH` + `Fail kanonik` + `Baseline metrik` + `STATUS` pada blueprint; **Baki:** `GIT_COMMIT` perlukan `git log` (tiada shell dalam sesi audit). |
| 3 | Jadikan metrik dihasilkan oleh skrip; jangan salin angka secara manual | ✅ `skrip/jana-baseline.php` + CI gate (`docs/AUDIT-BASELINE.md`). |
| 4 | Tandakan setiap ciri dengan status `SPECIFIED` → `PRODUCTION-READY` | 📋 Belum —(skip pada modul `Reseller` sahaja; perlukan feature-status registry). |
| 5 | Tambah sumber bukti kepada ciri penting | 📋 Belum. |
| 6 | Letakkan dokumen sejarah dalam `archive/` + amaran jelas | ✅ `docs/archive/README.md` kini `ARCHIVED / NOT AUTHORITATIVE`. |
| 7 | Pisahkan spesifikasi roadmap daripada bukti pelaksanaan dan bahan pemasaran | ✅ README utama digantikan — kini ada kotak status `DEVELOPMENT` + amaran "belum production-ready". |
| 8 | Tanda status `Reseller` dengan jujur | ✅ `IMPLEMENTED / TESTED` (kod) + `BLOCKED` (komersial, ADR-010). |

> **Langkah seterusnya:** feature-status registry (butir 4–5) ialah kerja terbesar
> yang tinggal dalam Fasa 0 — ia memerlukan inventori automatik modul → API →
> permission → UI → test → dokumentasi.

---

## 4. Keputusan seni bina yang patut dikekalkan

### 4.1 Platform teras

Kekalkan Laravel sebagai backend utama dan sistem standalone; jangan jadikan ERPNext/Frappe sebagai dependency wajib. Kekalkan satu akaun dan satu platform yang mengandungi banyak domain perniagaan. Frontend web, aplikasi mudah alih, Admin HQ, API dan AI agent mesti menggunakan kontrak backend yang sama.

### 4.2 Hierarki pentadbiran

Hierarki yang dirancang:

1. **Master Admin** — kawalan platform dan polisi global.
2. **Super Admin** — operasi SaaS, modul, harga, token, pelanggan dan konfigurasi platform mengikut kebenaran.
3. **White-label Admin** — pengurusan jenama/reseller/portfolio tenant yang diberikan.
4. **Customer Admin** — organisasi pelanggan, cawangan, jabatan, pengguna, peranan dan tetapan perniagaan.

Peraturan wajib: role hierarchy tidak menggantikan authorization pada setiap permintaan. Setiap operasi perlu mengesahkan actor, tenant, permission, entitlement, skop objek dan keadaan domain.

### 4.3 Tenant isolation

Satu tenant tidak boleh membaca atau mengubah data tenant lain melalui UI, API, eksport, carian, laporan, fail, background job, cache, log atau alat AI. `tenant_id` sahaja bukan bukti keselamatan. Perlukan:

- tenant resolution yang eksplisit dan konsisten;
- global scope/tenant context serta perlindungan untuk query manual;
- ujian negatif tenant A vs tenant B bagi setiap modul kritikal;
- tenant context yang dibawa ke queue, scheduled job, event, webhook dan AI tool;
- object-storage path dan signed URL yang tenant-scoped;
- cache key, search index, vector store dan analytics partition yang diasingkan;
- pentadbir platform menggunakan akses bersebab, berjejak dan prinsip least privilege.

### 4.4 Modular monolith

Gunakan modular monolith pada peringkat awal, tetapi modul mesti mempunyai sempadan domain sebenar:

- setiap modul memiliki jadual dan peraturan domainnya;
- modul lain tidak menulis terus ke jadual dalaman modul pemilik;
- interaksi melalui service contract, domain event atau API dalaman;
- event penting mempunyai schema/version, tenant context, correlation ID dan idempotency key;
- side effect luaran menggunakan queue/outbox, retry terkawal dan dead-letter queue;
- modul tidak boleh memanggil provider AI secara terus.

### 4.5 AI Gateway tunggal

Peraturan yang dinyatakan dalam dokumen integrasi wajar dijadikan invariant: semua panggilan model AI melalui `AIGateway\Services\GatewayService`. Gateway perlu menjadi tempat kawalan untuk:

- provider/model routing dan fallback;
- tenant/user quota, token meter dan ledger kredit;
- secret/API key dan BYOK;
- prompt-injection guard, redaksi PII dan polisi data;
- timeout, retry, circuit breaker dan kos;
- logging yang tidak membocorkan prompt/data sensitif;
- had output, model allowlist dan approval untuk tindakan berisiko.

---

## 5. Inventori modul dan sempadan tanggungjawab

Inventori penuh sumber dimasukkan dalam Lampiran A. Secara seni bina, modul hendaklah dipisahkan mengikut **pemilik data dan keputusan domain**, bukan hanya mengikut menu.

### 5.1 Pemilik data kanonik

| Objek / domain | Pemilik kanonik yang dicadangkan | Modul pengguna | Peraturan integrasi |
|---|---|---|---|
| Tenant, pengguna, role, permission | Platform Core / Identity | Semua modul | Modul tidak mencipta sistem identiti alternatif |
| Langganan, entitlement, add-on, harga | Subscription & Monetization Engine (perkhidmatan teras) | Semua modul berbayar | Semua semakan akses merujuk sumber yang sama |
| Kredit AI dan kos model | AIGateway / AI Wallet | Semua ciri AI | Ledger append-only; tiada modul menambah kredit secara terus |
| Pelanggan dan kenalan | CRM sebagai customer master, dengan kontrak shared customer identity | Orders, POS, Marketing, Helpdesk, Booking, Marketplace | Elak salinan pelanggan tidak disegerakkan |
| Produk/SKU dan unit ukuran | Catalog/Inventory contract | POS, Orders, Marketplace, Site, Manufacturing | Satu product ID/SKU kanonik; variasi dan unit mesti jelas |
| Stok dan pergerakan stok | Inventory | POS, Orders, Marketplace, SCM, Manufacturing, Logistics | Perubahan melalui stock ledger dan reservation API |
| Pesanan jualan | Orders sebagai unified order orchestration | POS, Site, Marketplace, Omnichannel, COD, Booking apabila relevan | Satu order ID kanonik; saluran ialah atribut/sumber |
| Pembayaran dan refund | Payment orchestration / gateway adapter | POS, Orders, Invoicing, subscription | Bezakan payment intent, transaction, settlement dan ledger |
| Dokumen kewangan | Invoicing untuk dokumen jualan; Finance untuk jurnal/ledger | POS, Orders, Expense, Payroll | Dokumen bukan sama dengan posting perakaunan |
| Kempen dan audience | Marketing | Blast, ContentStudio, CRM | Audience dan consent dikongsi melalui kontrak, bukan salinan liar |
| Kandungan dan penerbitan | ContentStudio | MediaStudio, MediaEditor, SEO, Omnichannel | Status publish dan platform response direkodkan |
| Metadata SEO | Seo | Site, Marketplace, ContentStudio, profil tenant | Metadata kanonik dan audit skor tidak boleh bercanggah |
| Workflow | FlowAutomation sebagai runtime/engine | WorkflowStudio, modul domain, agent | WorkflowStudio ialah authoring/UI, bukan runtime kedua |
| Agent dan execution policy | AIAgents/AgentStudio mengikut sempadan yang ditetapkan | PersonalAssistant, ExecutiveAI, Boardroom, ADE | Registry agent, alat, hak akses dan tahap autonomi mesti tunggal |
| Audit trail | Platform Audit | Semua modul | Event audit tidak boleh dimatikan oleh modul |
| Notifikasi/peringatan | Reminder/Notification service | Semua modul | Templat, consent, channel, retry dan status penghantaran dikongsi |

### 5.2 Model kontrak modul wajib

Setiap modul perlu mempunyai dokumentasi yang konsisten:

1. Tujuan dan bukan-tujuan modul.
2. Pemilik domain dan pemilik jadual.
3. Model, migration, service, controller, route dan permission.
4. Kontrak API masuk/keluar.
5. Event yang diterbitkan dan digunakan.
6. Sempadan tenant dan authorization.
7. Integrasi luar dan secret yang diperlukan.
8. Aliran kegagalan, retry dan idempotency.
9. UI/admin/customer surface.
10. Unit, integration, tenant isolation, authorization dan acceptance tests.
11. Metrik operasi, audit log dan retention.
12. Status bukti, tarikh pengesahan dan owner teknikal.

---

## 6. Pertindihan modul dan keputusan pemilikan

### 6.1 Jualan dan pemasaran

| Modul | Peranan khusus | Risiko pertindihan | Keputusan |
|---|---|---|---|
| CRM | Customer/lead/opportunity/quotation/pipeline | Marketing dan Prospecting turut menyimpan lead/kontak | CRM pemilik rekod pelanggan/pipeline; modul lain menambah signal/aktiviti melalui kontrak |
| Marketing | Segmen, kempen, nurture, atribusi dan automasi pemasaran | Blast dan ContentStudio juga menjalankan komunikasi | Marketing menentukan audience/journey; Blast menghantar mesej; ContentStudio mengurus kandungan/penerbitan |
| Prospecting | Penemuan prospek dan sumber/consent | CRM boleh menerima import lead; risiko data tanpa asas sah | Prospecting menyimpan sumber, provenance dan consent; hanya lead yang lulus polisi dipindahkan ke CRM |
| SalesCopilot | Cadangan dan bantuan jualan | Boleh menjadi CRM kedua | Tidak memiliki customer master; membaca CRM dan menulis aktiviti/cadangan melalui API |
| Blast | Penghantaran e-mel/SMS/WhatsApp berskala | Marketing, Reminder, LiveSupport dan ContentStudio menghantar mesej | Jadikan messaging delivery service dengan channel adapters, templates, consent, rate limit dan delivery receipts |
| Link | Pautan pendek dan atribusi | SEO dan Marketing mungkin menjejaki klik sama | Link pemilik redirect/klik; attribution rules milik Marketing/Analytics |

### 6.2 AI dan agentic automation

| Modul | Sempadan yang dicadangkan |
|---|---|
| AIGateway | Model/provider routing, policy, metering, wallet, PII/security guard |
| AIAgents | Runtime/registry bagi agent yang boleh melaksanakan tugas mengikut polisi |
| PersonalAssistant | Antaramuka pembantu peribadi, konteks dan preference pengguna |
| ExecutiveAI | Briefing/command centre dan orkestrasi tugas eksekutif; bukan runtime AI provider berasingan |
| AgentStudio | Pembinaan, konfigurasi, ujian dan penerbitan definisi agent |
| Boardroom | Proses perbincangan/keputusan berstruktur antara peranan agent; keputusan perlu sumber dan audit |
| FlowAutomation | Runtime workflow produksi: trigger, condition, action, retry, schedule, idempotency |
| WorkflowStudio | Visual builder, validator, template dan dry-run; workflow yang diterbitkan dihantar kepada FlowAutomation |
| McpStudio | Pendaftaran dan kawalan MCP tools/resources/prompts; allowlist dan human-in-the-loop |
| BusinessIntel | Analisis/insight/learning loop; cadangan tidak boleh terus mengubah sistem tanpa policy/approval |
| ADE | Persekitaran pembangunan agentic: skills, sandbox/worktree, sub-agent, review dan kelulusan kod |

**Keputusan wajib:** satu runtime untuk workflow produksi; satu pintu model AI; satu policy engine bagi tindakan berisiko; satu registry agent/skills/tools; satu audit trail. Jika dua modul perlu mengekalkan fungsi yang serupa, nyatakan sama ada salah satunya ialah UI, adapter atau facade — bukan sistem kedua.

### 6.3 Pesanan, POS, marketplace dan omnichannel

| Modul | Pemilikan |
|---|---|
| POS | Aliran transaksi kaunter, cashier shift, receipt dan interaksi terminal |
| Orders | Rekod order kanonik, status, orchestration saluran dan order lifecycle |
| Omnichannel | Penyatuan saluran, mapping katalog/stok dan penyelarasan pesanan |
| Marketplace | Vendor, listing, cart, komisen dan peraturan multi-vendor |
| Site | Storefront/site builder, katalog dipaparkan dan checkout surface |
| COD | Polisi kelayakan COD, status kutipan, rekonsiliasi COD |
| Logistics | Label, carrier, penghantaran, tracking dan kos penghantaran |
| Inventory | Stock ledger, reservation, transfer, adjustment dan availability |

**Peraturan stok:** stok tidak boleh dikira berasingan dalam POS, Orders, Site dan Marketplace. Inventory ialah sumber pergerakan stok; Orders meminta reservation; POS menghantar transaksi yang memicu posting stok; Marketplace/Omnichannel menyegerakkan availability. Gunakan idempotency dan kawalan concurrency bagi jualan serentak.

### 6.4 Kewangan dan people

| Modul | Sempadan |
|---|---|
| Invoicing | Quote, invoice, credit/debit note, receipt, recurring billing dan e-Invoice workflow |
| Finance | Chart of accounts, journal, ledger, bank reconciliation dan laporan kewangan |
| Expense | Permintaan/perbelanjaan, bukti resit, approval dan eksport ke Finance |
| COGS | Pengiraan margin/kos jualan berdasarkan snapshot kos; bukan ledger kewangan alternatif |
| Payroll | Pengiraan gaji, potongan dan dokumen payroll berdasarkan konfigurasi statutori |
| HRMS | Data pekerja, organisasi, cuti, attendance dan dokumen HR |
| Assets | Daftar aset dan jadual susut nilai; posting kewangan melalui kontrak Finance |
| Projects | Projek, milestone, task, timesheet dan kos projek; billing melalui Invoicing |

**Risiko:** jika Finance dan COGS masing-masing mengira margin daripada sumber berlainan, laporan akan bercanggah. Tentukan sumber kos, tarikh snapshot, kaedah valuation dan aturan adjustment. Untuk payroll statutori Malaysia, kadar dan peraturan mesti versioned mengikut tarikh efektif dan disemak oleh pakar payroll/compliance.

### 6.5 Kandungan, media dan SEO

- **MediaStudio:** penciptaan/analisis media AI, aset, consent dan moderasi.
- **MediaEditor:** pengeditan projek/lapisan dan render jobs.
- **ContentStudio:** kalendar kandungan, brand voice, adaptasi platform, penerbitan, komen/DM dan analitik.
- **Seo:** audit dan metadata untuk objek yang disokong.
- **Site/Marketplace:** pemilik halaman dan produk; Seo menyimpan metadata melalui kontrak.

Jangan anggap semua platform sosial menyokong API publish, DM, komen atau analytics yang sama. Setiap connector perlu menyatakan endpoint yang disokong, scope OAuth, had rate, review/approval platform dan fungsi yang tidak tersedia.

---

## 7. Jurang produk dan keupayaan rentas platform

### 7.1 Keupayaan yang perlu dibuktikan secara hujung-ke-hujung

Katalog menunjukkan keupayaan luas, tetapi audit acceptance mesti memeriksa perjalanan pengguna sebenar:

1. **Lead-to-cash:** Prospecting/website form → CRM → quotation → order → payment → invoice/e-Invoice → Finance ledger → receipt/WhatsApp → Analytics.
2. **Omnichannel stock:** SKU dicipta → stok masuk → order web/marketplace/POS serentak → reservation → fulfilment → refund/return → rekonsiliasi stok.
3. **Campaign-to-revenue:** segment berconsent → campaign → Blast/ContentStudio → delivery receipt → response → CRM opportunity → paid order → attribution.
4. **Service booking:** pelanggan → availability → booking → deposit/payment → reminder → staff assignment → completion → invoice → review/loyalty.
5. **AI-to-action:** pengguna meminta analisis → gateway meter kos → agent baca data yang dibenarkan → cadangan dengan sumber → approval manusia jika berisiko → tindakan → audit event → rollback/compensation jika gagal.
6. **Subscription lifecycle:** daftar tenant → trial/plan → payment → entitlement → usage metering → failed payment/grace period → downgrade/cancel → eksport/pemadaman data mengikut polisi.
7. **Support-to-resolution:** tiket → SLA → knowledge search → AI draft → approval jika perlu → reply → audit → analytics.
8. **Payment lifecycle:** create intent → redirect/checkout → signed webhook → verify amount/currency/status → update order sekali sahaja → receipt → settlement/reconciliation → refund/chargeback.

### 7.2 Ciri yang mudah terlebih dakwa

Tandakan sebagai **belum dibuktikan** sehingga ada bukti acceptance:

- “auto-publish ke 9 platform” — perlu matriks setiap platform dan endpoint sebenar;
- “AI self-improve” — perlu had, sumber, evaluasi, approval dan cara mengelak perubahan automatik tidak selamat;
- “CCTV/biometrik” — perlu threat model, retention, akses, consent/justification dan pengesahan undang-undang;
- “e-Invoice LHDN” — perlu bukti API/sandbox, submission, status, rejection, cancellation dan audit;
- “eSIM/live support” — perlu bukti penyedia, kontrak, provision/deprovision dan kos;
- “semua dokumen ke WhatsApp” — perlu WhatsApp Business Platform, template yang diluluskan, consent, opt-out, kawalan penerima dan bukti penghantaran;
- “semua modul multi-tenant” — perlu ujian negatif merentas API, export, queue, cache, storage dan AI retrieval;
- “AI agent autonomi” — perlu tahap autonomi, allowlist alat, budget, timeout, approval dan kill switch.

---

## 8. Audit AI, Agentic Development Environment (ADE) dan automasi

### 8.1 ADE — status yang didokumenkan

Blueprint v9.5 menyatakan ADE dikodkan sebagai modul baharu dengan 6 jadual, 24 laluan API, 4 resource Filament dan 48 ujian/191 assertion. Fungsi yang diterangkan:

- **A1 Skills Engine:** katalog skills, trigger, marketplace dan kawalan lesen;
- **A2 Worktree Isolation:** git worktree, baseline test dan perlindungan path traversal;
- **A3 Sub-Agent Coordinator:** implementer, tester, reviewer dan researcher; semakan silang;
- **A4 Review Surface:** diff walkthrough, test runner dan komen baris; approval menyekat tindakan automatik.

Angka dan ciri tersebut dilaporkan oleh blueprint; audit ini tidak menjalankan ujian ADE. Sebelum ADE digunakan pada repositori pelanggan, wajib ada:

- sandbox per tenant/project, isolated filesystem dan credential scope;
- network egress allowlist, secret redaction dan larangan membaca `.env` tanpa kebenaran;
- had CPU/memori/masa/token dan kos maksimum setiap task;
- baseline test sebelum perubahan dan ujian selepas perubahan;
- pengesahan signature/provenance untuk skill/plugin;
- human approval untuk merge, deployment, migrasi berisiko, pembayaran dan penghantaran mesej;
- log prompt, tool call, diff, output ujian dan keputusan reviewer tanpa menyimpan rahsia;
- kill switch global dan per tenant;
- perlindungan prompt injection dalam issue, README, source file dan output tool yang dianggap data tidak dipercayai.

### 8.2 Tahap autonomi

Gunakan tahap yang seragam merentas ExecutiveAI, AgentStudio, ADE dan agent runtime:

| Tahap | Tingkah laku | Kawalan minimum |
|---|---|---|
| A0 — Observe | Baca/analisis sahaja | Hak baca minimum, audit akses |
| A1 — Recommend | Cadangan tanpa perubahan | Sumber/rasional dan confidence |
| A2 — Draft | Sediakan perubahan/draf | Tiada publish/commit luaran tanpa approval |
| A3 — Execute with approval | Laksanakan selepas kelulusan | Approval berkaitan tindakan, idempotency dan rollback |
| A4 — Bounded autonomy | Laksana dalam polisi dan had yang ditetapkan | Allowlist alat, budget, rate limit, kill switch, anomaly detection dan audit menyeluruh |

Jangan gunakan label “autonomi 4” sebagai bukti agent selamat. Setiap tindakan mesti dinilai berdasarkan kesan: baca data, ubah data, komunikasi pelanggan, transaksi kewangan, akses sistem, atau deployment.

### 8.3 Guardrails minimum AI

- Semua panggilan model melalui AIGateway.
- Setiap panggilan mempunyai `tenant_id`, actor, feature/module, model, cost estimate, token usage, correlation ID dan policy decision.
- Tool execution menjalankan authorization semula di backend; jangan percaya arahan model atau frontend.
- Data tenant tidak digunakan untuk retrieval tenant lain.
- Memori mempunyai scope, retention, delete/export, sumber dan hak akses.
- Prompt injection dianggap ancaman daripada kandungan luar, dokumen, laman web, e-mel dan connector.
- Tindakan side-effecting menggunakan approval dan idempotency.
- AI tidak boleh mengubah polisi, harga, permission atau had keselamatan sendiri.
- Eval set bagi ketepatan, hallucination, refusal, leakage dan regresi mesti versioned.

---

## 9. Audit pesanan, stok, omnichannel dan industri

### 9.1 Unified Orders

Dokumentasi menyebut Orders sebagai sistem pesanan bersatu dengan 14 status kanonikal dan stock reservation berpusat. Tetapkan lifecycle kanonik secara eksplisit; jangan benarkan setiap saluran mencipta status berlainan tanpa mapping.

Minimum order model: `order_id`, `tenant_id`, `channel`, `external_order_id`, `customer_id`, currency, item snapshots, price/tax/discount snapshots, fulfillment status, payment status, cancellation/refund status, idempotency key dan event timestamps.

Kriteria penerimaan:
- webhook pesanan berulang tidak mencipta duplicate;
- harga/produk disnapshot pada masa order;
- reservation mempunyai TTL dan pelepasan ketika gagal/batal;
- stok tidak menjadi negatif di bawah concurrent checkout kecuali polisi backorder membenarkan;
- partial fulfillment, return, refund dan exchange direkodkan;
- rekonsiliasi saluran mengenal pasti mismatch dan menyediakan proses pembetulan;
- external IDs diindeks mengikut connector/tenant.

### 9.2 Industry Packs

IndustryPacks sepatutnya menjadi konfigurasi dan extension pack — bukan fork aplikasi untuk setiap industri. Setiap pack menyatakan:
- role/permission preset;
- entiti dan workflow tambahan;
- dashboard KPI;
- templat dokumen/notifikasi;
- integration adapters;
- feature flags dan migration;
- acceptance tests khusus industri.

Sektor sasaran yang perlu diuji sebagai konfigurasi berbeza termasuk retail, F&B, servis, pembuatan dan kesihatan. Untuk sektor sensitif atau terkawal, jangan mengisytiharkan pematuhan hanya kerana pack wujud.

### 9.3 Struktur organisasi pelanggan

Sokong business owner → management → admin → supervisor → staff/field worker → customer dengan skop akses mengikut tenant, branch, department, lokasi, rekod dan tindakan. Pengguna boleh mempunyai beberapa peranan, tetapi konflik tugas (contohnya mencipta dan meluluskan pembayaran yang sama) perlu dipertimbangkan.

---

## 10. Audit pembayaran, billing, token dan fintech

### 10.1 Bezakan empat domain

1. **Payment orchestration:** mencipta checkout/payment intent dan menghubungkan gateway.
2. **Subscription billing:** mengenakan bayaran untuk pelan AI BIJAN dan mengawal entitlement.
3. **AI credit wallet:** merekod pembelian, penggunaan, adjustment, refund dan baki token.
4. **Financial ledger:** merekod transaksi perniagaan dan rekonsiliasi kewangan.

Keempat-empatnya berintegrasi tetapi bukan satu jadual atau satu status. “Paid” daripada browser redirect tidak boleh dianggap bukti pembayaran; webhook perlu disahkan secara kriptografi dan direkonsiliasi.

### 10.2 B-Pay / gateway hub

Model integrator ialah pilihan permulaan yang munasabah: AI BIJAN menyediakan UI dan adapter; rakan gateway memproses pembayaran dan settlement. Pelanggan boleh memilih gateway yang disokong seperti CHIP, Billplz, ToyyibPay, senangPay, Stripe, Xendit dan PayPal — tertakluk kepada ketersediaan, kelayakan, negara, jenis transaksi dan kontrak sebenar.

Reka bentuk adapter wajib:
- interface provider-neutral (`createPayment`, `verifyWebhook`, `refund`, `getStatus`, `reconcile`);
- capability matrix per provider;
- credential encryption, key rotation dan tenant scoping;
- signed webhook verification, replay protection dan idempotency;
- pemetaan status kanonik serta rekod payload yang disunting daripada data sensitif;
- reconciliation report untuk transaction vs settlement;
- retry dan dead-letter queue;
- sandbox test dan ujian contract;
- log audit tanpa menyimpan PAN/CVV atau rahsia;
- dokumentasi refund, partial refund, chargeback dan settlement delay.

Jangan menyatakan sesuatu gateway “berintegrasi penuh” sehingga semua flow yang disokong telah lulus sandbox/acceptance test dan credentials sebenar telah disahkan.

### 10.3 CHIP Collect, recurring dan B-BillPay

Dokumen sumber menyebut CHIP Collect dan pembelian langganan/token; recurring payments dan beberapa provider dinyatakan belum disahkan dalam konteks terdahulu. Maka:
- recurring subscription mesti diuji bagi initial payment, renewal, failed renewal, grace period, cancel, refund dan duplicate webhook;
- B-BillPay perlu ditakrifkan dengan jelas sama ada ia payment initiation, bill presentment, payout, bill collection atau sekadar integrasi penyedia;
- pelanggan yang memasukkan API key sendiri (BYOK) mesti mendapat credential vault, validation, test connection, rotate/revoke dan audit;
- API key tidak boleh dihantar semula ke browser atau dimasukkan dalam log.

### 10.4 AI token wallet

Ledger append-only dicadangkan dengan jenis transaksi: purchase, grant, consumption, refund, adjustment, expiry dan chargeback. Setiap rekod mesti mempunyai idempotency key, source event, tenant, user/feature, currency/unit, model/provider, token input/output, kos sebenar dan margin.

Keperluan:
- jangan gunakan floating point untuk wang;
- tetapkan unit token/kredit yang jelas;
- kira kos mengikut model/provider dan perubahan harga;
- kawal overspend dan concurrent requests;
- tetapkan polisi jika provider gagal selepas kredit direserve;
- bezakan kredit promosi daripada kredit berbayar;
- jangan benarkan Super Admin mengubah baki tanpa sebab dan audit trail.

### 10.5 Compliance boundary

Sebelum meluaskan AI BIJAN menjadi fintech:
- dapatkan nasihat undang-undang Malaysia tentang peranan platform, e-money, payment services, remittance, lending/financing dan perlindungan pengguna;
- utamakan rakan kongsi berlesen untuk aktiviti terkawal;
- tentukan siapa merchant of record dan siapa menerima/menyimpan/menyelesaikan dana;
- dokumentasikan KYC/AML/sanctions obligations yang benar-benar terpakai mengikut model operasi;
- buat incident response, fraud monitoring, dispute handling dan complaint process;
- jangan memasarkan “fintech compliant” tanpa penilaian undang-undang dan bukti kawalan yang berkaitan.

---

## 11. Keselamatan, multi-tenancy, PDPA dan pematuhan Malaysia

### 11.1 Senarai kawalan wajib

- Authentication: MFA untuk pentadbir, pengurusan sesi, reset token selamat dan revocation.
- Authorization: deny-by-default, object-level checks, permission sepadan dengan route dan tindakan.
- Tenant isolation: ujian silang tenant untuk API, eksport, carian, cache, storage, job dan agent tools.
- Secret management: encryption at rest, key rotation, akses minimum dan redaction.
- Webhook: signature verification, timestamp/replay checks, idempotency dan rate limiting.
- Upload: MIME validation, antivirus/malware scanning jika sesuai, path isolation dan signed URL.
- Audit: log append-only untuk perubahan role, harga, subscription, refund, API key, agent tools dan export.
- Backup: backup encryption, retention, restore drill dan RPO/RTO yang dinyatakan.
- Observability: metrics, traces, structured logs, alerting dan correlation IDs.
- Dependencies: `composer audit`, lockfile, patch policy dan software bill of materials jika diperlukan.
- API: pagination, validation, rate limit, versioning, deprecation policy dan error schema.
- AI: prompt injection, data leakage, cost abuse, tool abuse dan model/provider outage.
- Deployment: secret separation dev/staging/prod, migration review, rollback dan smoke test.

### 11.2 PDPA dan data pelanggan

Jangan menganggap “consent field” sahaja memadai. Untuk setiap pemprosesan data, rekod:
- tujuan dan asas pemprosesan yang berkaitan;
- kategori data dan subjek data;
- sumber dan masa pemerolehan;
- saluran komunikasi dan status consent/opt-out;
- perkongsian dengan processor/subprocessor;
- retention dan proses pemadaman;
- permintaan akses/pembetulan/pemadaman yang berkenaan;
- transfer rentas sempadan jika ada;
- insiden dan proses pemberitahuan mengikut kewajipan yang terpakai.

Prospecting/scraping mesti menghormati robots.txt dan syarat sumber, tetapi itu sahaja tidak membuktikan pemprosesan data sah. Kumpulkan data secara minimum, simpan provenance dan mekanisme suppression/opt-out, serta lakukan semakan undang-undang bagi penggunaan sebenar.

### 11.3 WhatsApp dan komunikasi

Untuk resit, invois, penyata, peringatan dan dokumen pelanggan:
- gunakan saluran rasmi WhatsApp Business Platform jika itulah integrasi yang disokong;
- template yang memerlukan kelulusan mesti diluluskan;
- sahkan nombor penerima, tenant dan dokumen sebelum menghantar;
- semak consent, opt-out dan polisi komunikasi;
- lindungi URL dokumen dengan token berjangka pendek;
- log message ID, template, status delivery dan kegagalan;
- jangan hantar data sensitif melalui mesej tanpa kawalan akses dokumen;
- sediakan fallback dan hadkan retry supaya tidak menghantar berulang.

### 11.4 e-Invoice dan payroll

Bagi e-Invoice, payroll dan potongan statutori:
- simpan versi skema, kadar dan tarikh efektif;
- uji submission, validation, rejection, cancellation/credit note dan reconciliation;
- asingkan “invoice dijana” daripada “e-Invoice diterima/diluluskan”;
- log request/response dengan data sensitif disunting;
- dapatkan semakan pakar Malaysia dan kemas kini berdasarkan sumber rasmi terkini sebelum pelancaran.

---

## 12. Dashboard dan pengalaman pengguna

### 12.1 Prinsip dashboard

Jangan bina satu dashboard yang memaparkan semua metrik untuk semua pengguna. Dashboard perlu ditentukan oleh peranan, tenant, industri, branch dan entitlement.

### 12.2 Dashboard Business Owner / Executive

Paparkan:
- jualan hari ini, minggu, bulan dan perbandingan tempoh;
- gross margin dan kos utama;
- cash position, receivables, payables dan invoice overdue;
- order funnel, payment success, refunds dan fulfillment delays;
- stok kritikal, stockout risk dan inventory aging;
- lead pipeline, campaign ROI dan channel attribution;
- prestasi branch/department;
- tiket/SLA dan isu operasi;
- penggunaan AI, kos model dan baki kredit;
- cadangan AI dengan bukti, impak anggaran, tahap keyakinan dan butang approve/reject;
- amaran dan tindakan tertangguh.

Setiap angka perlu mempunyai definisi, sumber, timestamp, currency/timezone, drill-down dan pemilik metrik. Jangan campurkan nilai order, revenue recognized, cash collected dan settlement seolah-olah perkara yang sama.

### 12.3 Dashboard operasi

- **Sales/CRM:** lead aging, pipeline conversion, follow-up queue.
- **Marketing:** audience consent, campaign delivery, engagement dan revenue attribution.
- **Inventory/Orders:** availability, reservations, stock exceptions, order status.
- **Finance:** bank reconciliation, overdue invoices, cash flow, unposted journals.
- **HRMS/Payroll:** attendance exceptions, leave approvals, payroll run status.
- **Customer Support:** open tickets, SLA breaches, sentiment/AI suggestions.
- **AI Control Center:** model usage, latency, failure, cost, policy blocks, agent runs dan approvals.
- **Admin HQ:** tenant health, subscription delinquency, module entitlements, usage, incidents dan audit events.

### 12.4 Kriteria dashboard

- Role-based access, bukan sekadar hide UI.
- Semua widget menggunakan API berpermission dan tenant scope.
- Definisi KPI didokumentasikan dan konsisten antara dashboard dengan eksport.
- Data stale ditandakan; tiada angka rekaan apabila integration unavailable.
- Drill-down mengesahkan akses pada rekod sasaran.
- Export mempunyai audit log dan skop tenant.
- Widget berat menggunakan aggregation/caching yang selamat dan mempunyai cache key tenant-scoped.

---

## 13. Monetisasi, reseller dan risiko margin

### 13.1 Model hasil yang berasingan

- SaaS subscription untuk akses modul.
- AI token/usage packages.
- Add-on, seat, branch, storage atau integration plans jika benar-benar ditakrifkan.
- Payment-related revenue/partner commissions hanya selepas model kontrak dan compliance ditentukan.
- Reseller/white-label revenue sharing.

### 13.2 Subscription engine

Pusatkan plan, price, currency, billing cycle, trial, grace period, entitlement, usage cap, add-on, promotion, tax, invoice, failed payment, downgrade dan cancellation. Perubahan harga oleh Super Admin perlu:
- permission khusus;
- audit old/new value, actor, masa dan sebab;
- effective date;
- versi harga supaya invoice sejarah tidak berubah;
- kawalan sama ada harga baharu terpakai kepada pelanggan sedia ada;
- preview kesan dan approval jika perubahan berskala besar.

### 13.3 Reseller — amaran risiko

Blueprint (v9.5, sebelum pembetulan 27 Okt 2026) menyatakan model contoh: pelanggan langgan RM388/bulan, reseller menerima RM100/bulan selagi pelanggan kekal aktif, komisen menjadi hak selepas 30 hari; ia turut mendakwa "kos sebenar RM75–RM230/pelanggan". **Dakwaan kos itu SALAH dan telah dibuang** — kos platform belum dikira, dan token AI bukan kos tetapi hasil tambahan. Model sebenar: RM388 langganan (WAJIB) + pakej token AI RM55–1,100 (WAJIB). Komisen RM100 dikira dari **langganan sahaja**, bukan daripada jumlah termasuk token. **Kos token sudah disahkan** daripada kontrak sebenar (RM5/RM8/RM10/RM50/RM100 — margin 90.9% seragam); kos platform yang belum disahkan hanyalah yuran gateway CHIP dan dashboard kos AI per pelanggan. Dokumen juga memberi amaran liabiliti komisen masa depan (100 pelanggan × RM100 × 12 = RM120,000) yang masih terpakai sebagai risiko.

Angka ini ialah andaian perancangan dalam dokumen, bukan jaminan margin. Sebelum launch, bina model senario sekurang-kurangnya:
- pelanggan aktif, churn dan cohort;
- kos AI, hosting, support, payment fees, tax dan refund;
- komisen atas revenue collected atau invoiced;
- clawback untuk refund/chargeback;
- tempoh penahanan, minimum payout dan payout failure;
- fraud/self-referral dan pelanggan berkaitan;
- had komisen jika margin menjadi negatif;
- liabiliti komisen selepas pembatalan dan perubahan harga;
- cash-flow reserve dan stress test.

Jangan mengaktifkan program `Reseller` untuk pelanggan hanya kerana kod dan jadual telah ditulis.

**Status kod (disahkan 27 Okt 2026):** modul `Billing` **DIKODKAN** di `app/Modules/Billing/`
— 9 jadual (`billing_customers`, `billing_subscriptions`, `billing_charges`,
`billing_webhook_events`, `resellers`, `reseller_referrals`, `reseller_commissions`,
`reseller_payouts`, `reseller_clicks`), 6 laluan API, 32 ujian dalam
`tests/Feature/BillingResellerTest.php`, 12 peraturan komisen dalam
`ResellerCommissionService`. Modul **bukan** `Affiliate` sedia ada (yang tenant-scoped).

**Status komersial:** `BLOCKED` — **belum** production-ready. Tertahan oleh ADR-010
sehingga yang berikut selesai: terma hukum (legal terms), audit margin +
stress test, rekonsiliasi payout automatik, cukai, dan KYC reseller.

Ringkasnya: *implementasi kod = `IMPLEMENTED / TESTED`; kesesuaian komersial = `BLOCKED`.*

---

## 14. Audit persaingan dan tuntutan pemasaran

Dokumen analisis pesaing berguna untuk positioning dan idea produk, tetapi tuntutan pasaran berubah dan perlu disahkan pada tarikh penerbitan. Bezakan:
- **fakta produk pesaing** yang disahkan daripada laman rasmi;
- **interpretasi strategik**;
- **cadangan AI BIJAN**;
- **ciri AI BIJAN yang benar-benar diuji**.

### Positioning yang konsisten

**AI BIJAN — The Business Growth Operating System**  
**Every Business Deserves a Boardroom.**  
Alternatif tagline yang muncul dalam dokumen: *Your AI Business Team, in the Room.*

Naratif nilai yang boleh dipertahankan: menyatukan data operasi, jualan, kewangan dan AI dalam satu platform dengan akses mengikut peranan. Elakkan dakwaan “lebih baik daripada semua pesaing”, “semua platform terintegrasi”, “fully autonomous”, “compliant” atau “production-ready” tanpa bukti yang khusus.

### Keperluan competitive intelligence

- setiap tuntutan pesaing mempunyai URL, tarikh semakan dan tangkap layar/nota;
- harga menyatakan currency, per seat/per company, annual/monthly, SST dan add-on;
- senaraikan capability gap secara adil;
- jangan tandakan ciri pesaing atau AI BIJAN sebagai wujud hanya berdasarkan roadmap;
- semak semula harga dan ciri sebelum digunakan dalam landing page, proposal atau pitch deck.

---

## 15. Matriks keutamaan P0–P3

### P0 — wajib sebelum pelancaran komersial atau peluasan akses

| ID | Tindakan | Risiko yang ditutup | Bukti siap |
|---|---|---|---|
| P0-01 | Sahkan baseline repositori pada satu commit | Angka bercanggah | Report automatik modul, migration, route, tests, permissions dan versi |
| P0-02 | Audit tenant isolation | Kebocoran data | Ujian negatif merentas semua modul dan background path |
| P0-03 | Audit authorization | Akses tanpa izin | Route-to-permission matrix dan ujian role/tenant |
| P0-04 | Payment webhook security | Bayaran palsu/duplicate | Signature, replay, idempotency, reconciliation tests |
| P0-05 | AI Gateway enforcement | Kos/leak/tool abuse | Tiada direct provider call di luar gateway; static scan + tests |
| P0-06 | Secret and PII controls | Pendedahan credentials/data | Secret scan, log redaction, key rotation test |
| P0-07 | Backup/restore + rollback | Kehilangan data | Restore drill bertarikh dan bukti RPO/RTO |
| P0-08 | Label status ciri dengan jujur | Overclaim jualan | Feature-status registry dan bukti acceptance |
| P0-09 | Bersihkan status dokumen lama | Keputusan salah berdasarkan sejarah | README/version/changelog konsisten |

### P1 — sebelum scale atau menjual integrasi sebagai siap

| ID | Tindakan | Bukti siap |
|---|---|---|
| P1-01 | Unified order/stock concurrency tests | Stress/concurrency tests dan rekonsiliasi |
| P1-02 | Full payment lifecycle per gateway | Capability matrix + sandbox acceptance |
| P1-03 | End-to-end lead-to-cash | Trace ID dari lead hingga ledger |
| P1-04 | Social/WhatsApp connector acceptance | Matriks capability, OAuth, template, opt-out |
| P1-05 | Dashboard KPI definitions | Kamus metrik dan cross-check dengan ledger |
| P1-06 | Queue/outbox/retry/dead-letter | Failure injection tests |
| P1-07 | AI agent autonomy and approval | Policy tests, kill switch, budget limits |
| P1-08 | PDPA data inventory/retention | Data map, retention schedule, DSAR workflow |
| P1-09 | e-Invoice/payroll compliance review | Ujian rasmi dan sign-off pakar |
| P1-10 | Reseller margin stress test | Model cohort, payout ledger dan legal terms |

### P2 — peningkatan keandalan dan pengalaman

- SLO/SLI untuk API, queue, AI Gateway dan connector.
- Contract tests untuk semua connector.
- Observability dashboards dan alerting.
- Feature flags dan progressive rollout.
- Data export dan tenant offboarding yang diuji.
- Dashboard industri/branch yang mempunyai KPI standard.
- Evaluation harness untuk model/agent.
- Versioned API schemas dan deprecation process.

### P3 — peluasan strategik

- Perluasan negara/SEA dan localization.
- Fintech/embedded finance melalui partner-led model.
- Marketplace untuk skills, templates dan Industry Packs.
- Advanced forecasting dan benchmarking yang menjaga privacy.
- Native mobile bila PWA/workflows dan demand telah disahkan.
- Proprietary model/edge AI hanya jika ada business case dan bukti kelebihan.

---

## 16. Pelan pelaksanaan dan kriteria penerimaan

### Fasa 0 — kunci kebenaran (baseline)

1. Pilih commit yang akan diaudit.
2. Jalankan ujian penuh dan semua skrip pemeriksaan.
3. Jana inventory modul, route, migration, model, service, resource, permission dan tests secara automatik.
4. Bandingkan dengan katalog dan blueprint.
5. Terbitkan `AUDIT-BASELINE.md` dengan tarikh/commit.

**Penerimaan:** tiada angka semasa bercanggah antara README, katalog, blueprint dan laporan automatik; semua perbezaan sejarah dilabel.

### Fasa 1 — keselamatan platform

1. Uji tenant isolation pada API, UI, export, search, storage, queue dan agent tools.
2. Audit role hierarchy dan permission.
3. Audit secret management, webhook, upload, audit log dan PII redaction.
4. Uji backup restore dan rollback.

**Penerimaan:** semua ujian P0 lulus; tiada isu kritikal/tinggi terbuka tanpa mitigasi dan sign-off.

### Fasa 2 — kontrak domain dan data kanonik

1. Tetapkan pemilik customer, product, order, stock, payment, invoice dan ledger.
2. Takrifkan event schemas dan versioning.
3. Tambah outbox/idempotency untuk side effect.
4. Tutup penulisan silang ke jadual modul lain.
5. Tambah data consistency/reconciliation jobs.

**Penerimaan:** satu aliran data kanonik bagi setiap domain; duplicate/replay tidak merosakkan state.

### Fasa 3 — aliran perniagaan hujung-ke-hujung

Laksanakan acceptance suites untuk lead-to-cash, stock/order, campaign-to-revenue, booking-to-invoice, support-to-resolution dan subscription lifecycle.

**Penerimaan:** setiap perjalanan mempunyai test trace, expected state, failure scenarios, audit events dan KPI yang sepadan.

### Fasa 4 — connector dan AI runtime

1. Audit setiap connector mengikut provider.
2. Jalankan sandbox tests dan failure injection.
3. Pastikan AI Gateway wajib untuk semua model.
4. Jalankan AI evals dan tool permission tests.
5. Uji approval, kill switch dan budget.

**Penerimaan:** connector hanya ditandakan `INTEGRATED` selepas ujian berjaya; agent hanya diberi autonomi yang sesuai dengan bukti.

### Fasa 5 — komersial dan operasi

1. Subscription/pricing/entitlement audit.
2. Support, SLA, incident response dan customer offboarding.
3. Load test, capacity planning dan cost model.
4. Reseller legal terms dan margin stress test.
5. Release checklist dan rollback plan.

**Penerimaan:** ada owner operasi, alerting, runbook, support process, backup restore, rollback dan metrik kos bagi setiap release.

---

## 17. Definition of Done dan senarai semak pelepasan

Sesuatu modul hanya boleh ditandakan **PRODUCTION-READY** apabila semua yang berkaitan berikut dipenuhi:

- [ ] tujuan, skop dan bukan-tujuan jelas;
- [ ] migrations dan rollback diuji;
- [ ] model/service/controller/API/UI lengkap bagi skop yang dijanjikan;
- [ ] validation, authorization dan tenant scope diuji;
- [ ] unit + integration + negative tests lulus;
- [ ] API schema dan error contract didokumentasikan;
- [ ] queue/retry/idempotency/dead-letter diuji jika berkenaan;
- [ ] audit log dan observability tersedia;
- [ ] data retention/export/delete difahami;
- [ ] secret/PII tidak bocor ke log atau client;
- [ ] accessibility dan UX asas diperiksa;
- [ ] performance/load profile munasabah;
- [ ] backup/restore/rollback dipertimbangkan;
- [ ] connector telah lulus ujian provider yang berkaitan;
- [ ] user acceptance test diluluskan;
- [ ] runbook, owner dan support escalation ditetapkan;
- [ ] dokumentasi, katalog, README dan status ciri dikemas kini.

Status standard yang disyorkan:

`IDEA` → `SPECIFIED` → `IMPLEMENTED` → `TESTED` → `INTEGRATED` → `ACCEPTANCE-PASSED` → `PRODUCTION-READY`.

Status tambahan: `BLOCKED`, `DEPRECATED`, `ARCHIVED`. Jangan lompat terus dari `SPECIFIED` ke `PRODUCTION-READY`.

---

## 18. Daftar keputusan seni bina

| ID | Keputusan | Rasional | Semakan semula jika |
|---|---|---|---|
| ADR-001 | Laravel standalone; tiada dependency wajib ERPNext | Kawalan produk dan multi-tenancy | Keperluan teknikal/komersial berubah dengan bukti |
| ADR-002 | Modular monolith pada fasa awal | Kurangkan kerumitan deployment | Beban atau keperluan organisasi membenarkan pemisahan servis |
| ADR-003 | Satu platform identity/tenant/RBAC | Konsistensi dan keselamatan | Tiada perubahan tanpa ADR baharu |
| ADR-004 | Satu AI Gateway untuk semua model | Metering, policy dan cost control | Tiada pengecualian tanpa threat/cost review |
| ADR-005 | Inventory pemilik stock ledger | Mengelak drift stok | Domain baharu perlu melalui kontrak stok |
| ADR-006 | Orders pemilik order lifecycle kanonik | Penyatuan saluran | Mapping saluran diuji sebelum tambah status |
| ADR-007 | FlowAutomation runtime; WorkflowStudio authoring | Elak dua enjin workflow | Hanya berubah jika satu runtime jelas ditamatkan |
| ADR-008 | Payment adapter provider-neutral | Pelanggan boleh memilih provider | Setiap provider mesti lulus capability/contract tests |
| ADR-009 | Integrator/partner-led untuk pembayaran | Mengurangkan risiko pengendalian dana | Model terkawal baharu memerlukan legal review |
| ADR-010 | Reseller tidak aktif sebelum audit margin/legal | Mengelak liabiliti komisen | Selepas model ekonomi dan kawalan payout lulus |
| ADR-011 | Status ciri berasaskan bukti | Elak overclaim | Semua perubahan status mesti mempunyai artifact bukti |
| ADR-012 | Industry Packs sebagai konfigurasi/extension | Mengelak fork kod setiap industri | Pengecualian perlu dijustifikasikan oleh domain sebenar |

---

## 19. Sumber dokumentasi yang digabungkan

Dokumen ini menyatukan dapatan dan maklumat daripada fail berikut dalam `docs.zip`. Ia tidak bermaksud setiap ayat sumber disalin verbatim; kandungan telah disusun semula untuk menjadikan audit ini satu dokumen rujukan tunggal.

| Fail sumber | Peranan dalam audit |
|---|---|
| `AI-BIJAN-MASTER-BLUEPRINT.md` | Blueprint utama, sejarah versi, modul, ADE dan Reseller |
| `KATALOG-MODUL-AI-BIJAN.md` | Inventori modul dan ringkasan fungsi |
| `SISTEM-AI-BIJAN.md` | Gambaran sistem dan seni bina |
| `SISTEM-PEMBANGUNAN-DARI-MULA.md` | Prinsip pembangunan, security, ujian dan roadmap |
| `BLUEPRINT-SISTEM-LENGKAP.md` | Reverse engineering dan komponen teras |
| `MODUL-AI-INTEGRASI.md` | Hubungan modul AI dan AI Gateway |
| `AUDIT-DOKUMEN.md` | Percanggahan, baseline dan kebersihan dokumentasi |
| `TODO.md` | Status kerja dan sejarah isu |
| `ANALISIS-KOMPETITIF.md` | Positioning dan analisis pesaing |
| `AUDIT-PESAING-LANJUTAN.md` | Analisis pesaing lanjutan |
| `competitive-analysis-report.md` | Analisis pasaran tambahan |
| `ai-bijan-vs-world-comparison.md` | Perbandingan yang perlu dikawal daripada overclaim |
| `PETA-PESAING-34-MODUL.md` | Peta fungsi pesaing dan modul |
| `RESELLER-KOMISEN-BERULANG.md` | Ekonomi reseller, payout dan risiko |
| `README-OMNICHANNEL.md` | Integrasi saluran |
| `README-INDUSTRY-PACKS.md` | Konfigurasi industri |
| `README-LOGISTICS.md` | Logistik dan fulfillment |
| `README-SIGN-DOCS.md` | Dokumen dan tandatangan |
| `BUSINESS-GROWTH-ENGINE.md` | Pertumbuhan perniagaan |
| `CADANGAN-IMPAK-TINGGI.md` | Cadangan impak tinggi |
| `KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md` | Prospecting, Blast dan Close |
| `PROSPEK-BLAST-CLOSE.md` | Rangkaian prospek dan pemasaran |
| `REVERSE-ENGINEERING-200-APPS-50-OSS.md` | Kajian produk dan OSS |

---

## 20. Lampiran A — katalog modul sumber

Bahagian ini mengekalkan katalog modul seperti yang dinyatakan dalam dokumen sumber, supaya nama asal tidak hilang semasa audit. Penerangan fungsi di bawah ialah **penerangan katalog**, bukan pengesahan bebas bahawa setiap fungsi sudah lulus acceptance.

# AI BIJAN — Katalog Penuh Modul (v9.4) — *petikan sumber*

> ⚠️ **Ini petikanverbatim fail katalog asal**, disimpan untuk memastikan tiada
> nama/penjelasan modul hilang semasa audit. **Angka di bawah adalah yang
>uppeting pada 26 Okt 2026 dan kini TELAH LAPUK.**
>
> Untuk angka semasa → [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md).
> Untuk katalog semasa → [`KATALOG-MODUL-AI-BIJAN.md`](KATALOG-MODUL-AI-BIJAN.md).

> **59 modul** · **346 jadual** · **1,518 laluan API** · **962 ujian lulus** (0 gagal, 4 skip, 5,033 assertion)
> Blueprint: `AI-BIJAN-MASTER-BLUEPRINT.md` → **v9.6**, **103 bab / 27 PART**
> Baseline metrik: `AUDIT-BASELINE.md`
> Tarikh audit: 26 Oktober 2026 · disemak 27 Oktober 2026 (modul `Billing` ditambah)
>
> **UI:** Filament 3.3 di `/admin` — **66 resource** (liputan 59 modul),
> **6 widget** dashboard, halaman Tetapan Syarikat. **212 kebenaran** merentas
> 52 namespace. Pengesahan visual: halaman ADE + POS + Tetapan = HTTP 200 + jadual render.
>
> ⚠️ **Angka petikan ini disahkan daripada kod pada 26 Okt 2026** melalui
> `php artisan test`, `php artisan route:list`, dan semakan `app/Modules/`.
> **Sudah dibetulkan 27 Okt 2026** — lihat `AUDIT-BASELINE.md` §2.

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

> 📊 **Sumber rasmi:** [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md).
> Nilai "perlu PHP" memerlukan pangkalan data atau suite ujian sebenar.

| Metrik | Nilai |
|---|---|
| Modul | **59** |
| Model | **310** |
| Service | **174** |
| Controller (modul) | **134** |
| Jadual pangkalan data | **368** *(kiraan migration)* / perlu PHP *(sebenar)* |
| Laluan API (`api/v1`) | **~872** |
| Kaedah ujian | **~1,119** *(89 fail Feature)* / perlu PHP *(lulus sebenar)* |
| Assertion | **perlu PHP** |
| Resource Filament | **65** |
| Kebenaran | **212** *(perlu PHP untuk sahkan)* |
| Blueprint | **103 bab / 27 PART** |
| Versi | **v9.6** |
| Laravel | 12.70.1 |
| Advisori keselamatan | **0** *(perlu PHP untuk sahkan)* |

**Regenerate:** `php skrip/jana-baseline.php --tulis`

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

> **B-POS System** (`C:\ai-bijan\AI-Bijan\b-pos-system`) ialah **produk kasir berasingan** yang
> terus dijual sendiri (RM88/tahun / RM18/bulan), **bukan** modul AI BIJAN.
> Audit penuh: blueprint **BAB 88**. **Keputusan: kekal berasingan**, ciri dipindahkan
> satu per satu (T-76 → T-84) — **8 daripada 9 tugasan sudah SELESAI**.

### Status pemindahan (disahkan 27 Okt 2026 daripada kod)

| Tugasan | Ciri | Status | Pewakilan dalam modul `POS/` |
|---|---|---|---|
| T-76 | Dine-in + Kitchen Display | ✅ SELESAI | `DineOrderService`, `KitchenService` |
| T-77 | Modifier / Add-on | ✅ SELESAI | `ModifierService` |
| T-78 | Service Charge | ✅ SELESAI | `ServiceChargeService` |
| T-79 | Multi-Tender Split Payment | ✅ SELESAI | `TenderService`, `SalePaymentService` |
| T-80 | Cashier Shift + Cash Movement | ✅ SELESAI | `CashShiftService`, `ShiftCashController` |
| T-81 | Offline Idempotency (`client_uuid`) | ✅ SELESAI | `SalePaymentService::bayar` |
| T-82 | TNG Soundbox | ✅ SELESAI | `TngSoundboxService` |
| T-83 | Discount Approval Workflow | ✅ SELESAI | `DiscountApprovalService` |
| T-84 | Thermal Printer WebUSB | 📋 **BELUM** | kerja **frontend** sahaja |

Migrasi menyokong: `2026_10_19_000500_create_pos_tables.php` dan
`2026_10_27_001000_add_bpos_features_to_pos.php`.

### Perbezaan yang sengaja dibuat semasa pemindahan

| Aspek | B-POS (asalnya) | Modul `POS/` AI BIJAN |
|---|---|---|
| Unit pengasingan | `outlet_id` (cawangan) | **`tenant_id`** (syarikat) |
| Prefix jadual | (tiada) | **`pos_*`** |
| Token pesanan awam | `uuid` kekal | **ber-HASH + luput** |
| Semakan silang-tenant | tiada | **wajib untuk setiap ID dari URL** |

### Ciri B-POS yang sengaja TIDAK dipindahkan

- 4 jadual wilayah Indonesia (`provinces`/`cities`/`districts`/`villages`).
- `whatsapp-web.js` gateway.
- `access_token` uuid tanpa had masa.
- Corak `legacySingleOutletBypass` (lalai TRUE — jika tiada outlet, semua user dapat semua gudang).
- UI Inertia/React penuh — AI BIJAN guna Filament/Blade.

### Perbandingan metrik

| Metrik | B-POS | AI BIJAN |
|---|---|---|
| Migration | 109 | **58** |
| Model | 61 | **310** |
| Service | 30 | **174** |
| Controller | 73 | **134** |
| Multi-tenant | ❌ Tiada | ✅ `BelongsToTenant` |
| AI | ❌ Tiada | ✅ **10+ modul** |

> Angka AI BIJAN disahkan 27 Okt 2026 (lihat `docs/AUDIT-BASELINE.md`).

**Penilaian:** POS **8/10** · Keselamatan **4/10** · AI **0/10** · UI **7/10**.
**Lesen:** asas `aryadwiputra/point-of-sales` = MIT (notis dikekalkan dalam
`THIRD-PARTY-NOTICES.md`); lapisan Bijan = proprietari. Kedua-dua produk milik
pemilik yang sama → pemindahan dibenarkan sebagai urusan dalaman.

**Tindakan tinggal:** selesaikan **T-84** (thermal printer WebUSB, frontend) dan
tutup semua 9 tugasan sebelum meny untapped B-POS sebagai rujukan pelaksanaan lengkap.

---

## Modul Dirancang (Belum Dikodkan)

| Modul | Sumber | Halangan | Status 27 Okt 2026 |
|---|---|---|---|
| **B-Migration Wizard** | Dolibarr, Matrixify | Risiko data — perlu dry-run simulator | 📋 belum |
| **B-ServiceOS** | RekaDesk (BAB 87) | Perlu lapisan domain baharu | ✅ **DIKODKAN** (`app/Modules/ServiceOS/`) |
| **ADE (A1–A4)** | `obra/superpowers` (BAB 86.11) | Skills engine, worktree, coordinator, review | ✅ **DIKODKAN** (`app/Modules/ADE/`) |
| **B-Sandbox** | ADE keperluan | Isolation container/worktree | ✅ **DIKODKAN** (`AdeSandboxService`) |
| **Kalodata-class affiliate analytics** | kalodata.com | Data awam sahaja | ✅ separa (`Affiliate/Analytics`) |
| **Compliance per negara** | Odoo | Nasihat guaman | 📋 belum |
| **Connector Pack 20+** | ServiceTitan | Kajian API per vendor | 📋 belum |
| **White-label OEM** | Tweak Odoo | Branding engine | 📋 belum |
| **FilamentPHP UI** | aureuserp (MIT) | `composer require filament/filament` — **UI 2 → 7** | ✅ **DIKODKAN** (65 resource) |


---

## 21. Lampiran B — metrik dan arahan pengesahan yang dirujuk dokumen

Dokumen sumber menyarankan arahan berikut untuk menyemak semula baseline dalam repositori aplikasi:

```bash
php artisan test
php artisan route:list --path=api/v1
composer audit
php skrip/semak-dokumen.php
```

Semakan tambahan yang disyorkan sebelum release:

```bash
php artisan migrate:status
php artisan route:list --json
composer validate
```

Tambahkan arahan/skrip projek untuk mengira secara automatik:
- direktori modul aktif;
- migrations dan jadual sebenar;
- route API unik mengikut method + URI + name;
- permission yang dirujuk route dan UI;
- test pass/fail/skip dan coverage yang bermakna;
- semua panggilan provider AI di luar Gateway;
- semua connector dengan credential/configuration;
- semua status feature yang tiada bukti acceptance.

### Hasil akhir audit

**AI BIJAN mempunyai blueprint dan katalog fungsi yang luas, tetapi keputusan teknikal dan komersial perlu berasaskan satu baseline kod yang boleh diulang.** Keutamaan bukan menambah modul semata-mata; ia adalah menyatukan pemilik data, menguatkuasakan sempadan tenant dan permission, membuktikan aliran hujung-ke-hujung, menutup percanggahan dokumentasi, dan mengukur readiness berdasarkan bukti. Kekalkan inventori asal; modul `Reseller` kini **telah dikodkan dan diuji** dalam `app/Modules/Billing/` — namun status *commercial readiness*-nya (terma, cukai, KYC, payout automatik) masih belum selesai.
