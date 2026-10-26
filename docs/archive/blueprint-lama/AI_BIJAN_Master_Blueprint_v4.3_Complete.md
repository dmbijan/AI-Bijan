# AI BIJAN v4.2 — Personal AI, Business Understanding & Autonomous Business Operations

> **Dokumen tambahan kepada:** AI BIJAN SaaS — Malaysia Industry Coverage Master Blueprint  
> **Versi cadangan:** 4.2  
> **Skop:** Personal AI Assistant, Business Understanding Engine, Autonomous Business Growth Engine, AI Operations Orchestration  
> **Status:** Spesifikasi produk dan seni bina; bukan bukti implementasi production.

---

# PART XVI — B-PERSONAL AI ASSISTANT

## 63. Visi Personal AI Assistant

B-Personal AI Assistant ialah pembantu AI peribadi untuk setiap pemilik bisnes, pengurus, pekerja dan pengguna yang dibenarkan.

Pembantu ini bukan sekadar chatbot yang menjawab soalan. Ia bertindak sebagai ruang kerja pintar yang memahami konteks perniagaan, membantu membuat keputusan, merancang tindakan, menjalankan workflow yang dibenarkan dan memantau hasil.

Matlamatnya adalah supaya pelanggan boleh berkata:

- "Fahami bisnes saya dan sediakan sistem yang sesuai."
- "Cari pelanggan baharu untuk servis saya."
- "Semak semua lead yang belum dihubungi."
- "Sediakan kempen pemasaran minggu ini."
- "Follow up quotation yang belum dijawab."
- "Semak invois yang masih belum dibayar."
- "Tunjukkan kenapa jualan bulan ini menurun."
- "Rancang operasi untuk mencapai sasaran jualan."
- "Uruskan kerja rutin yang boleh diautomasikan."
- "Beritahu saya perkara yang memerlukan keputusan saya sahaja."

AI perlu menyelaraskan permintaan dengan data sebenar, permission, modul aktif, bajet penggunaan AI, polisi organisasi dan hasil yang boleh diukur.

## 64. Jenis Personal AI Assistant

### 64.1 Owner AI Assistant

Untuk pemilik atau pengarah syarikat.

Fungsi:

- Ringkasan keadaan bisnes.
- Sasaran jualan dan prestasi.
- Cash-flow overview.
- Lead dan peluang jualan.
- Outstanding invoices.
- Operasi yang lewat.
- Risiko perniagaan.
- Keperluan tenaga kerja dan aset.
- Cadangan tindakan.
- Kelulusan tindakan yang memerlukan pemilik.

### 64.2 Executive AI Assistant

Untuk pengurusan kanan.

Fungsi:

- Executive briefing.
- KPI monitoring.
- Meeting preparation.
- Decision summaries.
- Department performance.
- Risk and issue escalation.
- Strategic planning support.

### 64.3 Sales AI Assistant

Fungsi:

- Lead qualification.
- Customer research menggunakan sumber yang dibenarkan.
- CRM updates.
- Follow-up planning.
- Sales message drafting.
- Quotation preparation.
- Objection handling suggestions.
- Pipeline monitoring.
- Closing checklist.
- Collection reminders.

### 64.4 Marketing AI Assistant

Fungsi:

- Campaign planning.
- Audience segmentation.
- Content generation.
- SEO planning.
- Landing page drafts.
- Marketing calendar.
- Campaign measurement.
- Lead attribution.
- Budget monitoring.
- Recommendations based on performance.

### 64.5 Operations AI Assistant

Fungsi:

- Task coordination.
- Workflow monitoring.
- Scheduling assistance.
- Inventory alerts.
- Vendor coordination.
- Service delivery tracking.
- Exception management.
- Standard operating procedure assistance.

### 64.6 Finance AI Assistant

Fungsi:

- Invoice summaries.
- Receivables monitoring.
- Payment matching suggestions.
- Expense categorization suggestions.
- Cash-flow forecasts.
- Budget variance analysis.
- Reconciliation exceptions.
- Finance report preparation.

AI tidak boleh memuktamadkan jurnal kewangan, meluluskan refund atau memindahkan wang tanpa permission dan approval yang sesuai.

### 64.7 HR AI Assistant

Fungsi:

- Onboarding checklists.
- Training plans.
- Shift planning assistance.
- Leave workflow support.
- Policy Q&A.
- Skills and staffing summaries.

Keputusan pekerjaan berimpak tinggi tidak boleh dibuat berdasarkan cadangan AI semata-mata.

### 64.8 Customer Service AI Assistant

Fungsi:

- Answer approved FAQs.
- Retrieve authorized customer/order status.
- Draft replies.
- Classify tickets.
- Route cases.
- Escalate complaints.
- Summarize conversation history.

### 64.9 Industry-Specific Assistant

Setiap industry blueprint boleh mendaftarkan assistant khusus, contohnya:

- Event Planner AI.
- Retail Operations AI.
- F&B Operations AI.
- Fleet Operations AI.
- Salon Booking AI.
- Construction Project AI.
- Property Management AI.
- Professional Services AI.

Assistant khusus menggunakan domain knowledge, tool, workflow dan permission modul masing-masing.

## 65. Personal AI workspace

Setiap pengguna boleh mempunyai ruang AI dengan:

- Conversation history.
- Saved tasks.
- Reminders.
- Personal preferences.
- Work context.
- Approved knowledge sources.
- Connected modules.
- Pending approvals.
- Action history.
- Usage and token balance.
- Agent activity.
- Automation subscriptions.

Kandungan peribadi tidak secara automatik menjadi pengetahuan seluruh organisasi. Pengguna perlu memahami perbezaan antara personal context, shared company knowledge dan rekod rasmi.

## 66. Memory architecture

AI BIJAN perlu membezakan jenis memori.

| Jenis | Contoh | Akses |
|---|---|---|
| Personal preferences | Bahasa pilihan, format laporan | Pengguna berkenaan |
| Work context | Tugasan semasa, projek yang ditugaskan | Mengikut permission |
| Business profile | Produk, harga, sasaran syarikat | Pengguna tenant yang dibenarkan |
| Operational records | Orders, invoices, tasks | Modul sumber dan permission |
| Conversation history | Perbualan terdahulu | Mengikut access policy |
| Approved knowledge | SOP, dokumen rasmi, polisi | Mengikut classification |
| Derived insights | Trend jualan, risiko, cadangan | Mengikut data source access |

### 66.1 Memory rules

- Simpan hanya maklumat yang mempunyai tujuan jelas.
- Sediakan fungsi review, correction dan deletion mengikut polisi.
- Jangan jadikan andaian AI sebagai fakta yang disahkan.
- Rekod sumber dan masa untuk business facts yang penting.
- Hormati permission asal apabila mencari semula maklumat.
- Jangan gunakan data tenant lain untuk menjawab soalan pelanggan.
- Jangan dedahkan data peribadi kepada pengguna yang tidak dibenarkan.
- Bezakan fakta, inferens, ramalan dan cadangan.
- Data rasmi mesti dibaca daripada source of truth, bukan semata-mata memori AI.

## 67. Personal AI task execution

Setiap tindakan AI perlu mempunyai:

- `task_id`
- `tenant_id`
- `requested_by`
- `agent_id`
- `objective`
- `input_reference`
- `allowed_tools`
- `risk_level`
- `approval_requirement`
- `budget_limit`
- `deadline`
- `status`
- `result_reference`
- `error_details`
- `created_at`
- `completed_at`

Status yang disyorkan:

`Draft → Planned → Awaiting Approval → Queued → Running → Completed / Partially Completed / Failed / Cancelled`

AI tidak boleh mendakwa tugas telah selesai sebelum sistem sumber mengesahkan hasilnya.

## 68. Proactive Personal AI

AI boleh memberikan amaran dan cadangan secara proaktif apabila mempunyai data yang sah.

Contoh:

- Lead penting tidak dihubungi selepas tempoh yang ditetapkan.
- Quotation belum mendapat jawapan.
- Invoice hampir atau telah melepasi tarikh matang.
- Stok dijangka tidak mencukupi.
- Event mempunyai task kritikal yang belum selesai.
- Campaign melebihi bajet.
- Penggunaan AI hampir mencapai had.
- Integrasi payment mengalami masalah.
- Sasaran jualan ketinggalan daripada trajectory.

Setiap alert perlu menunjukkan:

1. Apa yang berlaku.
2. Data atau rekod yang menjadi asas.
3. Tahap kepastian.
4. Kesan yang mungkin.
5. Tindakan yang dicadangkan.
6. Sama ada tindakan boleh dibuat automatik.
7. Cara pengguna meluluskan, menolak atau membetulkan cadangan.

---

# PART XVII — B-BUSINESS UNDERSTANDING ENGINE

## 69. Objektif

B-Business Understanding Engine ialah proses onboarding mendalam untuk memahami cara bisnes pelanggan benar-benar beroperasi sebelum AI BIJAN membuat konfigurasi atau mencadangkan automasi.

Matlamatnya bukan sekadar meminta pelanggan memilih industri. Sistem perlu membina **Business Operating Profile** yang boleh digunakan untuk menyediakan modul, data, workflow, KPI, AI assistants dan pelan automasi yang sesuai.

Sistem mesti mengesan maklumat yang belum diketahui dan bertanya soalan susulan apabila maklumat itu mempengaruhi konfigurasi atau risiko.

## 70. Business discovery lifecycle

```text
Business Registration
        ↓
Industry & Activity Discovery
        ↓
Business Model Discovery
        ↓
Products & Services Discovery
        ↓
Customer & Sales Discovery
        ↓
Operations Discovery
        ↓
People, Roles & Responsibilities
        ↓
Finance, Costs & Payments
        ↓
Tools, Data & Integrations
        ↓
Goals, Constraints & Risks
        ↓
Business Understanding Report
        ↓
Proposed System Blueprint
        ↓
Human Review & Confirmation
        ↓
Initial Setup & Data Import
        ↓
Workflow Simulation
        ↓
Go-Live Readiness
        ↓
Continuous Learning & Updates
```

## 71. Tahap 1 — Company identity

Kumpulkan maklumat yang diperlukan sahaja:

- Nama syarikat.
- Nama jenama.
- Jenis entiti perniagaan.
- Negara dan kawasan operasi.
- Aktiviti utama dan sekunder.
- Bilangan cawangan.
- Saiz pasukan.
- Bahasa operasi.
- Mata wang.
- Waktu operasi.
- Saluran pelanggan.
- Peringkat perniagaan.
- Model operasi.
- Keperluan industri khusus.

Maklumat pendaftaran atau pengenalan yang sensitif hanya boleh diminta apabila diperlukan untuk fungsi tertentu dan mempunyai kawalan akses yang sesuai.

## 72. Tahap 2 — Business model discovery

AI perlu mengenal pasti cara pelanggan menjana hasil.

Soalan yang boleh ditanya secara berperingkat:

- Apakah yang dijual?
- Siapakah pelanggan utama?
- Adakah hasil datang daripada jualan sekali, langganan, komisen, projek atau gabungan?
- Bagaimanakah harga ditetapkan?
- Adakah deposit diperlukan?
- Bagaimanakah pesanan diterima?
- Apakah syarat pembatalan?
- Apakah kos terbesar?
- Apakah faktor yang mengehadkan kapasiti?
- Bagaimanakah pelanggan mengetahui kewujudan syarikat?
- Apakah faktor utama yang membuatkan pelanggan membeli?

AI perlu menyesuaikan soalan mengikut jawapan terdahulu dan tidak meminta maklumat yang sudah disahkan tanpa sebab.

## 73. Tahap 3 — Products and services understanding

Bina katalog awal yang mengandungi:

- Product/service name.
- Category.
- Description.
- Target customer.
- Selling price.
- Cost components.
- Delivery method.
- Fulfilment time.
- Capacity.
- Availability.
- Optional add-ons.
- Warranty/guarantee jika berkaitan.
- Return/cancellation terms.
- Required equipment or staff.
- Tax configuration, apabila relevan dan telah disahkan.

AI boleh mencadangkan kategori dan struktur, tetapi pelanggan perlu mengesahkan harga, kos, janji perkhidmatan dan syarat jualan yang penting.

## 74. Tahap 4 — Customer understanding

Bina profil segmen pelanggan:

- B2B, B2C atau kedua-duanya.
- Customer segments.
- Service areas.
- Typical buyer.
- Buyer versus end user.
- Buying triggers.
- Common objections.
- Typical sales cycle.
- Repeat purchase pattern.
- Retention risks.
- Preferred communication channels.
- Customer consent and communication preferences.

AI tidak boleh membuat andaian sensitif atau membuat profil diskriminasi. Segmentasi perlu berasaskan tujuan perniagaan yang sah dan data yang dibenarkan.

## 75. Tahap 5 — Sales process discovery

Petakan perjalanan jualan sebenar:

1. Lead source.
2. Lead capture.
3. Qualification.
4. First contact.
5. Discovery.
6. Proposal/quotation.
7. Negotiation.
8. Approval.
9. Contract/order.
10. Deposit or payment.
11. Delivery/fulfilment.
12. Final invoice.
13. Payment collection.
14. Customer success.
15. Repeat sales/referral.

Bagi setiap peringkat, rekodkan:

- owner;
- required information;
- entry condition;
- exit condition;
- SLA;
- templates;
- automation eligibility;
- approval rules;
- common failure reasons;
- conversion metric.

## 76. Tahap 6 — Operations understanding

AI perlu memahami bagaimana produk atau perkhidmatan dihantar kepada pelanggan.

Contoh aspek:

- Work intake.
- Scheduling.
- Staff assignment.
- Resource allocation.
- Procurement.
- Inventory.
- Quality control.
- Delivery.
- Customer confirmation.
- Incident handling.
- Returns/refunds.
- Completion and handover.

Sistem perlu mengesan dependency yang penting. Contohnya, jualan tidak boleh dijanjikan jika kapasiti, stok, tenaga kerja atau vendor tidak tersedia.

## 77. Tahap 7 — Team and organization

Kumpulkan struktur tanggungjawab:

- Owner.
- Management.
- Sales.
- Marketing.
- Operations.
- Finance.
- Customer service.
- Procurement.
- Fulfilment.
- External vendors.
- Approval authority.

Jangan memberikan semua permission kepada pemilik akaun secara automatik untuk setiap pekerja. Sediakan role template dan minta pengesahan.

## 78. Tahap 8 — Finance and unit economics

Sistem perlu memahami:

- Revenue streams.
- Direct costs.
- Fixed costs.
- Variable costs.
- Staff costs.
- Supplier costs.
- Payment terms.
- Deposits.
- Receivables.
- Payables.
- Refunds.
- Discounts.
- Gross margin.
- Break-even assumptions.
- Cash-flow timing.

AI perlu membezakan data sebenar daripada anggaran pelanggan.

Jika kos tidak diketahui, sistem perlu menyatakan bahawa margin tidak dapat dikira dengan yakin dan meminta data tambahan.

## 79. Tahap 9 — Current tools and data

Kenal pasti:

- Existing CRM.
- Accounting software.
- Spreadsheets.
- POS.
- Website.
- E-commerce platform.
- Communication tools.
- Payment providers.
- Calendar systems.
- Inventory systems.
- File repositories.
- Existing APIs.

Bagi setiap integrasi, rekodkan access status, data scope, owner, sync direction, sync frequency dan recovery policy.

Jangan meminta pelanggan menyerahkan kata laluan akaun luaran melalui chat. Gunakan proses OAuth, credential vault atau kaedah sambungan selamat yang sesuai.

## 80. Tahap 10 — Goals, constraints and risks

Sistem perlu mendapatkan sasaran yang boleh diukur:

- Revenue target.
- Lead target.
- Conversion target.
- Average order value.
- Repeat purchase rate.
- Delivery time.
- Customer satisfaction.
- Operating cost.
- Staff capacity.
- Cash collection.
- Error/rework rate.

Rekodkan juga:

- Budget.
- Team capacity.
- Technical limitations.
- Legal/industry constraints.
- Required approvals.
- Data sensitivity.
- Risk tolerance.
- Launch deadline.

## 81. Business Understanding Scorecard

Sediakan scorecard yang menerangkan kelengkapan maklumat, bukan memberi gambaran palsu bahawa AI memahami semuanya.

Dimensi:

| Dimensi | Soalan penilaian |
|---|---|
| Business identity | Adakah entiti dan aktiviti disahkan? |
| Revenue model | Adakah sumber hasil difahami? |
| Product/service | Adakah katalog dan harga disahkan? |
| Customers | Adakah segmen dan saluran dikenal pasti? |
| Sales | Adakah pipeline dan conversion stages ditetapkan? |
| Operations | Adakah fulfilment workflow diketahui? |
| Team | Adakah role dan approval jelas? |
| Finance | Adakah kos, payment terms dan collection diketahui? |
| Data | Adakah source of truth dan integrasi dikenal pasti? |
| Automation | Adakah syarat dan batas automasi ditentukan? |
| Risk | Adakah risiko dan kawalan didokumenkan? |
| KPI | Adakah baseline dan sasaran tersedia? |

Gunakan status seperti `Unknown`, `Partially Understood`, `Customer Confirmed`, `System Verified` dan `Needs Review`.

Jangan gabungkan semua dimensi menjadi satu skor tanpa menjelaskan kaedah pengiraan.

## 82. Business Operating Profile

Selepas discovery, sistem menghasilkan profil berstruktur:

- Business identity.
- Industry blueprint.
- Revenue model.
- Product/service catalogue.
- Customer segments.
- Sales pipeline.
- Operations workflows.
- Team and permission proposal.
- Financial assumptions.
- KPI definitions.
- Integration map.
- Automation opportunities.
- AI assistant recommendations.
- Data migration plan.
- Risks and unknowns.
- Required customer decisions.
- Estimated implementation effort.
- Proposed rollout sequence.

Setiap item perlu mempunyai sumber, status pengesahan dan tarikh kemas kini apabila sesuai.

## 83. Initial setup plan

Daripada Business Operating Profile, AI menyediakan pelan setup:

1. Modules to activate.
2. Roles and permissions.
3. Organization and branch setup.
4. Products and services import.
5. Customer import.
6. Sales pipeline setup.
7. Workflow configuration.
8. Document templates.
9. Payment/invoicing configuration.
10. Notifications.
11. Integrations.
12. AI assistant setup.
13. Automation policies.
14. KPI dashboards.
15. Acceptance tests.
16. Training and go-live checklist.

### 83.1 Approval gate

AI boleh menyediakan cadangan dan draft konfigurasi. Sebelum perubahan yang berimpak tinggi diaktifkan, pelanggan yang berkuasa perlu mengesahkan:

- harga;
- margin dan kos;
- kontrak;
- payment configuration;
- customer messaging;
- data migration;
- permission;
- financial rules;
- workflow kritikal.

## 84. Continuous business understanding

Business Operating Profile bukan dokumen statik.

Sistem boleh mengemas kini pemahaman berdasarkan:

- perubahan katalog;
- jualan sebenar;
- customer conversion;
- operasi;
- inventory;
- kos;
- pembayaran;
- customer feedback;
- perubahan organisasi;
- arahan pengguna.

Namun, sistem mesti membezakan:

- customer-confirmed fact;
- imported record;
- system-verified fact;
- AI inference;
- forecast;
- proposed change.

Perubahan penting mesti menunjukkan perbezaan sebelum/selepas dan membenarkan review.

---

# PART XVIII — B-AUTONOMOUS BUSINESS GROWTH ENGINE

## 85. Visi

B-Autonomous Business Growth Engine membantu pelanggan membina saluran pertumbuhan yang menyambungkan marketing, lead generation, sales, delivery, invoicing dan payment collection.

Objektifnya adalah mengurangkan kerja manual dari mengenal pasti prospek hingga merekod pembayaran.

Ia tidak menjamin penjualan atau penerimaan wang. Ia mengurus proses, meningkatkan konsistensi dan mengukur hasil.

## 86. End-to-end business growth lifecycle

```text
Business Understanding
        ↓
Define Ideal Customer Profile
        ↓
Define Offer & Value Proposition
        ↓
Build Compliant Prospect Strategy
        ↓
Find / Capture Leads
        ↓
Validate & Deduplicate
        ↓
Score & Qualify
        ↓
Create CRM Record
        ↓
Personalized Outreach
        ↓
Conversation & Discovery
        ↓
Proposal / Quotation
        ↓
Negotiation & Approval
        ↓
Contract / Order Confirmation
        ↓
Invoice / Deposit Request
        ↓
Payment Verification
        ↓
Delivery / Fulfilment
        ↓
Final Invoice / Collection
        ↓
Reconciliation
        ↓
Customer Success / Repeat Sales
        ↓
Performance Learning
```

Setiap peringkat mempunyai syarat masuk, syarat keluar, pemilik, status, retry policy dan audit.

## 87. Ideal Customer Profile Engine

Berdasarkan Business Operating Profile, AI membina Ideal Customer Profile (ICP).

ICP boleh merangkumi:

- Industry/activity.
- Company size.
- Service geography.
- Business needs.
- Buying triggers.
- Budget range jika diketahui secara sah.
- Procurement process.
- Expected sales cycle.
- Fit criteria.
- Disqualifying criteria.
- Data freshness requirements.

ICP mesti diluluskan atau disemak oleh pelanggan. Ia tidak boleh bergantung kepada data sensitif yang tidak sesuai.

## 88. Lead discovery and sourcing

Sistem boleh mendapatkan prospek daripada sumber yang sah dan dibenarkan:

- Website inquiry forms.
- Customer referrals.
- Existing CRM.
- Opt-in marketing lists.
- Authorized business directories.
- Public business websites, tertakluk kepada syarat penggunaan dan peraturan berkaitan.
- Marketplace inquiries.
- Partner integrations.
- Event registrations dengan consent yang sesuai.
- Inbound calls/messages yang diintegrasikan.

### 88.1 Lead sourcing controls

- Record source and retrieval date.
- Store evidence/source URL where permitted.
- Deduplicate by suitable business identifiers.
- Respect opt-out and suppression lists.
- Respect data-use restrictions.
- Validate contact data before outreach.
- Apply rate limits.
- Avoid unauthorized scraping or account access.
- Do not invent contact details.
- Do not treat publicly visible data as automatic permission for every marketing use.

Jika sistem tidak mempunyai akses kepada sumber, ia mesti menyatakan perkara itu dan meminta sambungan yang diperlukan.

## 89. Lead enrichment and scoring

Setiap lead boleh mempunyai:

- Company name.
- Industry.
- Location/coverage.
- Website.
- Business contact channel.
- Source.
- Need/fit evidence.
- Qualification status.
- Potential value estimate.
- Confidence level.
- Last verified time.
- Next action.
- Assigned owner.

### 89.1 Lead score

Skor boleh mengambil kira:

- ICP fit.
- Need evidence.
- Service area fit.
- Budget/size fit apabila diketahui.
- Engagement.
- Timing.
- Buying authority evidence.
- Data quality.

Paparkan faktor yang menyumbang kepada skor. Jangan menunjukkan skor sebagai fakta objektif jika ia hanya model anggaran.

## 90. Outreach orchestration

AI boleh menyediakan:

- E-mel pengenalan.
- Follow-up e-mel.
- WhatsApp message drafts melalui integrasi yang dibenarkan.
- Sales call briefs.
- LinkedIn/social drafts mengikut channel policy.
- Proposal cover messages.
- Appointment booking messages.
- Customer reactivation campaigns.

### 90.1 Autonomy levels

**Level 0 — Suggest only**

AI membuat cadangan dan draft. Manusia menghantar.

**Level 1 — Approve before send**

AI menyediakan mesej dan sasaran. Manusia meluluskan.

**Level 2 — Policy-based automation**

AI boleh menghantar mesej rutin kepada penerima yang dibenarkan berdasarkan consent, channel rules, rate limits dan template yang diluluskan.

**Level 3 — Controlled sales automation**

AI boleh mengurus susulan dan penjadualan dalam skop yang telah diluluskan. Diskaun, kontrak dan komitmen kewangan kekal di bawah approval rules.

Autonomy level perlu ditetapkan bagi setiap tenant, channel dan workflow.

## 91. Follow-up engine

Follow-up engine mengurus:

- Due date.
- Channel.
- Template.
- Personalization.
- Maximum attempt count.
- Minimum interval.
- Business hours.
- Opt-out.
- Suppression.
- Reply detection.
- Stop conditions.
- Escalation.
- Next best action.

Hentikan follow-up automatik apabila:

- pelanggan meminta untuk berhenti;
- pelanggan membalas dan memerlukan perbualan manusia;
- lead disqualify;
- kontrak telah ditandatangani;
- pelanggan telah membayar;
- had percubaan dicapai;
- consent tidak tersedia;
- terdapat aduan atau risiko.

Jangan menghantar mesej tanpa henti kerana tiada jawapan.

## 92. Sales conversation intelligence

AI boleh:

- Summarize conversation.
- Identify customer questions.
- Detect objections.
- Suggest replies.
- Extract next steps.
- Update CRM fields with review where required.
- Prepare meeting notes.
- Create follow-up tasks.
- Highlight missing information.
- Prepare proposal inputs.

AI mesti membezakan apa yang pelanggan benar-benar berkata daripada tafsiran model.

## 93. Proposal and quotation automation

AI boleh menyediakan draft berdasarkan:

- Product/service catalogue.
- Approved price list.
- Approved discounts.
- Customer requirements.
- Capacity and availability.
- Scope of work.
- Delivery terms.
- Taxes/fees yang telah disahkan.
- Contract templates.

Sebelum dihantar, semak:

- harga;
- kuantiti;
- skop;
- availability;
- tax treatment;
- payment terms;
- expiry;
- exclusions;
- approval authority.

Jangan biarkan AI mencipta harga atau syarat kontrak yang tidak diluluskan.

## 94. Deal closing assistant

Deal Closing Assistant membantu menjejak proses sehingga keputusan pelanggan direkodkan.

Fungsi:

- Deal readiness checklist.
- Missing information detection.
- Stakeholder mapping.
- Objection summary.
- Proposal expiry alerts.
- Negotiation draft suggestions.
- Approval workflow.
- Contract preparation.
- Signature status tracking.
- Order creation.
- Deposit/invoice generation.
- Handover to fulfilment.

AI boleh mencadangkan tindakan closing tetapi tidak boleh memalsukan persetujuan pelanggan atau menandatangani kontrak bagi pihak pelanggan tanpa mekanisme sah dan kuasa yang jelas.

## 95. Invoice and payment collection automation

Selepas deal disahkan, sistem boleh:

1. Verify order/contract state.
2. Generate invoice draft.
3. Validate customer and amount.
4. Apply numbering and terms.
5. Request approval where required.
6. Issue invoice through approved channel.
7. Monitor due date.
8. Send permitted reminders.
9. Receive payment-provider events.
10. Verify payment status server-side.
11. Match payment to invoice.
12. Update receivables.
13. Reconcile transaction.
14. Notify relevant parties.
15. Stop reminders after confirmed settlement.

### 95.1 Duit masuk — verification rules

AI BIJAN tidak boleh menandakan wang diterima berdasarkan:

- screenshot resit semata-mata;
- mesej pelanggan;
- browser redirect;
- e-mel tidak disahkan;
- kenyataan AI sendiri.

Gunakan payment provider confirmation, bank/accounting integration yang sah atau proses pengesahan kewangan yang diluluskan.

Jika transaksi belum dapat dipastikan, gunakan status seperti `Pending Verification` atau `Reconciliation Required`.

### 95.2 Payment states

Contoh status:

`Draft → Issued → Awaiting Payment → Pending Verification → Partially Paid → Paid → Reconciliation Required → Reconciled`

Status `Failed`, `Cancelled`, `Refund Pending` dan `Refunded` perlu disokong mengikut payment workflow.

Status sebenar hendaklah diselaraskan dengan model kewangan dan payment provider.

## 96. Customer success and repeat revenue

Selepas jualan:

- Onboarding customer.
- Delivery status.
- Satisfaction follow-up.
- Support ticket creation.
- Renewal reminders.
- Reorder reminders.
- Cross-sell suggestions.
- Referral requests.
- Churn-risk signals.
- Customer lifetime value reporting.

AI hanya boleh menghantar mesej mengikut consent, channel policy dan had komunikasi pelanggan.

## 97. Growth experiments

AI boleh mencadangkan eksperimen:

- Landing page variations.
- Offer variations.
- Email subject lines.
- Audience segments.
- Pricing hypotheses.
- Follow-up intervals.
- Channel mix.
- Content topics.

Setiap eksperimen perlu mempunyai:

- hypothesis;
- target segment;
- success metric;
- budget;
- duration;
- sample/measurement plan;
- approval;
- result;
- decision.

Jangan membenarkan AI mengubah semua harga atau campaign serentak tanpa kawalan, kerana hasilnya sukar dinilai dan risiko perbelanjaan meningkat.

---

# PART XIX — B-AUTONOMOUS OPERATIONS ORCHESTRATOR

## 98. Tujuan

Orchestrator menyelaraskan tindakan antara modul supaya satu peristiwa boleh mencetuskan workflow merentas sistem.

Contoh:

- Lead baharu → CRM record → qualification task.
- Lead qualified → appointment → sales brief.
- Deal approved → order → invoice.
- Payment verified → receipt → fulfilment workflow.
- Inventory low → procurement suggestion.
- Event confirmed → project tasks → vendor requests → rental reservation.
- Invoice overdue → collection workflow.
- Customer complaint → ticket → owner assignment.
- AI wallet low → usage warning → optional top-up flow.

## 99. Event-driven architecture

Setiap domain boleh menerbitkan event yang telah didokumenkan, contohnya:

- `lead.created`
- `lead.qualified`
- `proposal.approved`
- `deal.won`
- `order.confirmed`
- `invoice.issued`
- `payment.verified`
- `payment.reconciled`
- `inventory.threshold_reached`
- `booking.confirmed`
- `event.readiness_failed`
- `support.ticket_created`

Event sebenar mesti mempunyai schema version, event ID, tenant context, timestamp dan resource reference.

### 99.1 Event processing rules

- Idempotent consumers.
- Retry with backoff.
- Dead-letter handling.
- Duplicate detection.
- Event ordering strategy where necessary.
- Monitoring and alerting.
- Replay policy.
- Data privacy controls.
- Audit trail.

Jangan bergantung pada satu queue message sahaja untuk menjadi rekod rasmi transaksi kewangan.

## 100. Workflow automation builder

Pelanggan boleh membina workflow menggunakan:

- Trigger.
- Conditions.
- Actions.
- Delays.
- Approvals.
- Branching.
- Retry.
- Stop conditions.
- Notifications.
- Error paths.

Contoh:

```text
Trigger: Lead created
    ↓
Validate required fields
    ↓
Check duplicate
    ↓
If duplicate → merge/review
    ↓
If new → score lead
    ↓
If qualified → create sales task
    ↓
If outreach permission exists → prepare/send approved message
    ↓
Wait for response
    ↓
If reply → assign conversation
    ↓
If no reply → evaluate follow-up policy
    ↓
Update CRM and report outcome
```

## 101. Automation governance

Setiap automation perlu mempunyai:

- Owner.
- Tenant scope.
- Version.
- Trigger.
- Allowed actions.
- Budget/usage limit.
- Rate limit.
- Retry policy.
- Approval policy.
- Data access scope.
- Kill switch.
- Monitoring.
- Test mode.
- Rollback or compensation plan where possible.

Perubahan workflow tidak boleh terus menjejaskan semua pelanggan tanpa versioning dan migration policy.

## 102. Human-in-the-loop

Tindakan yang lazimnya memerlukan approval bergantung pada polisi tenant:

- Menghantar kontrak rasmi.
- Memberikan diskaun melebihi had.
- Mengubah harga yang diluluskan.
- Membuat refund.
- Mengeluarkan payout.
- Memindahkan wang.
- Memadam rekod penting.
- Mengubah permission admin.
- Menghantar kempen berskala besar.
- Mengaktifkan integrasi yang mengakses data sensitif.

Approval perlu merekod approver, timestamp, decision, reason dan versi data yang diluluskan.

---

# PART XX — AUTONOMY, SAFETY & CONTROL PLANE

## 103. Autonomy policy

AI BIJAN perlu mempunyai satu lapisan polisi yang menentukan tindakan yang boleh dibuat secara automatik.

Policy boleh berdasarkan:

- Tenant.
- User role.
- Module.
- Agent.
- Tool.
- Action type.
- Financial amount.
- Data sensitivity.
- Channel.
- Risk classification.
- Business hours.
- Usage quota.
- Approval requirements.

## 104. Risk classification

| Risiko | Contoh | Kawalan minimum |
|---|---|---|
| Low | Summarize report yang dibenarkan | Permission dan audit |
| Moderate | Create task atau draft | Validation dan log |
| High | Send external communication atau ubah booking kritikal | Policy, rate limits, approval mengikut kes |
| Critical | Refund, payout, financial commitment | Strong authorization, approval, idempotency, audit |

Klasifikasi sebenar perlu dinilai mengikut impak tindakan, bukan nama tool sahaja.

## 105. Global kill switch

Platform mesti boleh menghentikan:

- Semua AI actions.
- Satu provider.
- Satu agent.
- Satu tool.
- Satu tenant automation.
- Satu communication channel.
- Satu payment integration.

Kill switch perlu berfungsi walaupun komponen AI sedang mengalami masalah. Ia mesti diuji dan dipantau.

## 106. Failure handling

Jika workflow gagal:

- Jangan tandakan berjaya tanpa pengesahan.
- Simpan status dan error yang boleh disiasat.
- Elakkan duplicate external actions.
- Retry hanya untuk tindakan yang selamat diulang.
- Sediakan manual recovery.
- Alert pemilik proses untuk kegagalan kritikal.
- Reconcile dengan source of truth.
- Rekod sama ada transaksi perlu tindakan manusia.

---

# PART XXI — AI BUSINESS PERFORMANCE & REVENUE ATTRIBUTION

## 107. KPI funnel

Ukur keseluruhan funnel:

1. Prospects discovered.
2. Prospects validated.
3. Leads created.
4. Qualified leads.
5. Contacted leads.
6. Replies.
7. Meetings booked.
8. Proposals issued.
9. Deals won.
10. Orders confirmed.
11. Invoices issued.
12. Payments verified.
13. Payments reconciled.
14. Fulfilment completed.
15. Repeat purchases.

## 108. Revenue attribution

Bezakan:

- Revenue influenced by AI.
- Revenue attributed to a campaign.
- Revenue recorded in the source system.
- Invoices issued.
- Payments verified.
- Payments reconciled.
- Gross profit.
- Net contribution after relevant costs.

Jangan menganggap semua jualan yang berlaku selepas AI menghantar mesej sebagai hasil yang disebabkan oleh AI.

Attribution method perlu didokumenkan dan dipaparkan bersama report.

## 109. Business outcome dashboard

Dashboard owner boleh menunjukkan:

- New prospects.
- Lead conversion.
- Pipeline value.
- Proposal win rate.
- Average deal size.
- Sales cycle duration.
- Invoice outstanding.
- Collection rate.
- Confirmed receipts.
- Revenue by channel.
- Campaign cost.
- Cost per qualified lead.
- AI usage cost.
- Contribution margin estimate.
- Customer retention.
- Automation failure rate.

Setiap KPI perlu mempunyai definisi, formula, source of truth dan freshness indicator.

---

# PART XXII — ADDITIONAL DATA MODEL

## 110. Business Understanding entities

Cadangan entiti:

- `business_profiles`
- `business_activities`
- `business_discovery_sessions`
- `business_discovery_answers`
- `business_facts`
- `business_fact_sources`
- `business_assumptions`
- `business_unknowns`
- `business_goals`
- `business_kpi_definitions`
- `business_operating_profiles`
- `business_blueprint_recommendations`
- `business_setup_plans`
- `business_setup_approvals`
- `business_profile_versions`

Setiap fact atau assumption yang penting perlu mempunyai status pengesahan.

## 111. Personal AI entities

- `ai_assistants`
- `ai_assistant_profiles`
- `ai_conversations`
- `ai_messages`
- `ai_memory_items`
- `ai_memory_sources`
- `ai_tasks`
- `ai_task_steps`
- `ai_task_results`
- `ai_agent_runs`
- `ai_tool_calls`
- `ai_action_approvals`
- `ai_action_audit`
- `ai_user_preferences`

Simpan maklumat minimum yang diperlukan dan gunakan retention policy yang jelas.

## 112. Growth engine entities

- `growth_campaigns`
- `growth_audiences`
- `growth_experiments`
- `prospect_sources`
- `prospects`
- `prospect_enrichment_records`
- `lead_scores`
- `crm_leads`
- `crm_activities`
- `sales_opportunities`
- `sales_stage_history`
- `sales_followup_plans`
- `sales_message_drafts`
- `sales_outreach_events`
- `sales_proposals`
- `sales_approvals`
- `deal_records`
- `deal_handoffs`
- `collection_workflows`
- `revenue_attribution_records`

Jangan menyimpan data yang tidak mempunyai tujuan operasi yang sah.

## 113. Automation entities

- `automation_definitions`
- `automation_versions`
- `automation_triggers`
- `automation_runs`
- `automation_steps`
- `automation_approvals`
- `automation_failures`
- `automation_retries`
- `automation_dead_letters`
- `automation_policy_bindings`

Struktur akhir mesti diselaraskan dengan event bus, queue dan database yang dipilih.

---

# PART XXIII — API AND TOOL CONTRACTS

## 114. Personal AI API capabilities

Contoh endpoint konseptual:

- `POST /v1/ai/conversations`
- `POST /v1/ai/conversations/{id}/messages`
- `GET /v1/ai/conversations/{id}`
- `POST /v1/ai/tasks`
- `GET /v1/ai/tasks/{id}`
- `POST /v1/ai/tasks/{id}/cancel`
- `GET /v1/ai/approvals`
- `POST /v1/ai/approvals/{id}/decision`
- `GET /v1/ai/usage`

Nama endpoint ini ialah cadangan. Ia perlu disesuaikan dengan routing dan versioning sebenar.

## 115. Business onboarding API capabilities

Contoh endpoint konseptual:

- `POST /v1/business-discovery/sessions`
- `POST /v1/business-discovery/sessions/{id}/answers`
- `GET /v1/business-discovery/sessions/{id}`
- `POST /v1/business-discovery/sessions/{id}/generate-profile`
- `GET /v1/business-operating-profile`
- `POST /v1/business-operating-profile/review`
- `POST /v1/business-setup-plans`
- `POST /v1/business-setup-plans/{id}/approve`
- `POST /v1/business-setup-plans/{id}/execute`

## 116. Growth automation API capabilities

Contoh endpoint konseptual:

- `POST /v1/growth/campaigns`
- `POST /v1/growth/prospects/import`
- `POST /v1/growth/leads/qualify`
- `GET /v1/growth/pipeline`
- `POST /v1/growth/outreach/drafts`
- `POST /v1/growth/outreach/{id}/approve`
- `POST /v1/growth/proposals`
- `POST /v1/growth/deals/{id}/close`
- `GET /v1/growth/collections`
- `GET /v1/growth/performance`

Semua endpoint perlu authentication, tenant scope, permission checks, validation, audit dan rate limits.

---

# PART XXIV — ACCEPTANCE TESTS FOR PERSONAL AI & AUTONOMOUS BUSINESS

## 117. Business understanding tests

- [ ] AI meminta maklumat yang diperlukan sebelum mencadangkan konfigurasi kritikal.
- [ ] AI tidak menandakan andaian sebagai fakta disahkan.
- [ ] Business profile mempunyai source dan status verification.
- [ ] Pelanggan boleh membetulkan maklumat.
- [ ] Perubahan penting mempunyai version history.
- [ ] Cadangan blueprint menunjukkan sebab pemilihan modul.
- [ ] Sistem boleh mengenal pasti maklumat yang masih belum diketahui.
- [ ] Modul yang tidak disokong ditandakan dengan jelas.
- [ ] Import data mempunyai validation report.
- [ ] Setup tidak diaktifkan secara senyap untuk tindakan berisiko.

## 118. Personal AI tests

- [ ] AI hanya membaca data yang dibenarkan.
- [ ] Personal memory tidak bocor kepada pengguna lain.
- [ ] AI menunjukkan sumber bagi fakta operasi penting.
- [ ] AI membezakan fakta, inferens dan ramalan.
- [ ] Task status sepadan dengan status sistem sebenar.
- [ ] Pengguna boleh membatalkan task yang belum menyelesaikan tindakan luaran.
- [ ] Tool call memeriksa permission semula.
- [ ] AI token usage direkod.
- [ ] Approval mempunyai actor dan timestamp.
- [ ] Failure tidak dipaparkan sebagai success.

## 119. Lead generation tests

- [ ] Lead source direkod.
- [ ] Duplicate leads dikesan.
- [ ] Contact data yang tidak sah ditandakan.
- [ ] Lead scoring menunjukkan faktor utama.
- [ ] Opt-out dihormati.
- [ ] Suppression list dikuatkuasakan.
- [ ] Sistem tidak menghantar mesej tanpa permission atau asas yang sesuai.
- [ ] Rate limits berfungsi.
- [ ] Reply detection menghentikan sequence yang tidak lagi sesuai.
- [ ] Lead yang tidak qualified tidak terus menerima follow-up tanpa had.

## 120. Sales and closing tests

- [ ] Harga datang daripada price list yang diluluskan.
- [ ] Diskaun melebihi had memerlukan approval.
- [ ] Proposal versioning berfungsi.
- [ ] Contract acceptance mempunyai bukti yang sesuai.
- [ ] AI tidak mencipta persetujuan pelanggan.
- [ ] Deal status hanya berubah berdasarkan syarat yang sah.
- [ ] Order tidak diwujudkan dua kali akibat retry.
- [ ] Handover kepada operations membawa data yang diperlukan.

## 121. Payment and collection tests

- [ ] Invoice amount disahkan di server.
- [ ] Payment callback diverifikasi.
- [ ] Duplicate callback tidak menggandakan penerimaan.
- [ ] Browser redirect tidak dianggap bukti pembayaran.
- [ ] Screenshot tidak secara automatik mengesahkan payment.
- [ ] Partial payment dikendalikan.
- [ ] Overpayment dan unmatched payment mempunyai workflow.
- [ ] Collection reminder berhenti selepas settlement disahkan.
- [ ] Refund memerlukan authorization.
- [ ] Reconciliation mismatch menghasilkan alert.
- [ ] Payment provider outage tidak menyebabkan data transaksi hilang.

## 122. Autonomous operations tests

- [ ] Automation mempunyai owner dan version.
- [ ] Kill switch menghentikan tindakan baharu.
- [ ] Retry tidak menggandakan tindakan luaran.
- [ ] Dead-letter failure boleh disiasat.
- [ ] Approval policy diuji.
- [ ] Tenant isolation diuji pada setiap tool.
- [ ] Budget limits dikuatkuasakan.
- [ ] Kegagalan integrasi menghasilkan status yang boleh dipulihkan.
- [ ] Event-driven workflow tidak bergantung pada satu notification sebagai rekod rasmi.
- [ ] Semua tindakan sensitif mempunyai audit trail.

---

# PART XXV — IMPLEMENTATION ROADMAP ADDENDUM

## 123. Recommended build sequence

### Phase A — Business Understanding MVP

Bangunkan:

- Discovery wizard.
- Business Operating Profile.
- Unknowns and assumptions register.
- Industry/module recommendation.
- Setup plan.
- Human review.
- Profile versioning.

**Exit gate:** AI boleh menghasilkan profil yang boleh disemak dan pelanggan boleh membetulkannya sebelum setup.

### Phase B — Personal AI Foundation

Bangunkan:

- Personal workspace.
- Conversation history.
- Authorized knowledge retrieval.
- Task management.
- Agent/tool registry.
- Permission checks.
- Usage metering.
- Approval queue.

**Exit gate:** AI boleh membantu dengan tugasan berisiko rendah dan tidak boleh melangkaui permission.

### Phase C — CRM and Lead Pipeline

Bangunkan:

- Prospect intake.
- CRM.
- Deduplication.
- Lead qualification.
- Sales activities.
- Pipeline dashboard.
- Follow-up planning.

**Exit gate:** lead boleh dijejak dari sumber sehingga keputusan jualan.

### Phase D — Controlled Outreach

Bangunkan:

- Approved templates.
- Channel integrations.
- Consent/suppression management.
- Scheduling.
- Rate limits.
- Reply handling.
- Campaign metrics.

**Exit gate:** mesej hanya dihantar melalui saluran yang disambungkan dan polisi yang diluluskan.

### Phase E — Deal to Payment

Bangunkan:

- Proposal.
- Approval.
- Contract/order workflow.
- Invoice.
- Payment verification.
- Collection reminders.
- Reconciliation.
- Fulfilment handoff.

**Exit gate:** deal, invoice dan pembayaran boleh dijejak tanpa status palsu atau duplicate transactions.

### Phase F — Autonomous Operations

Bangunkan:

- Workflow builder.
- Event-driven orchestration.
- Cross-module automations.
- Failure recovery.
- Monitoring.
- Global and tenant kill switches.
- Automation analytics.

**Exit gate:** workflow boleh dihentikan, dipulihkan dan diaudit.

### Phase G — Industry Expansion

Tambah industry assistants dan blueprint berdasarkan keperluan pelanggan sebenar.

**Exit gate:** setiap industry pack mempunyai workflow lengkap, permission matrix, data model, acceptance suite dan known limitations.

---

# PART XXVI — FINAL PRODUCT PRINCIPLES

## 124. Product rules

1. AI BIJAN perlu memahami bisnes sebelum mencadangkan setup yang kompleks.
2. Pemahaman bisnes mesti mempunyai sumber, status dan version history.
3. Personal AI perlu mempunyai permission yang sama atau lebih ketat daripada pengguna yang diwakilinya.
4. Autonomi ditetapkan mengikut risiko, bukan dihidupkan tanpa batas.
5. Lead generation mesti menggunakan sumber dan saluran yang dibenarkan.
6. Outreach mesti menghormati consent, opt-out, rate limits dan channel policies.
7. AI boleh membantu closing tetapi tidak boleh memalsukan persetujuan.
8. Invoice yang dikeluarkan bukan bukti wang diterima.
9. Payment hanya dianggap diterima selepas verification yang sah.
10. Payment diterima dan payment reconciled ialah status yang berbeza.
11. Kegagalan automation mesti kelihatan dan boleh dipulihkan.
12. AI usage, provider cost dan customer charges mesti boleh diaudit.
13. Setiap tindakan berisiko mempunyai approval rules yang jelas.
14. Semua tenant mesti terasing.
15. Semua modul mempunyai source of truth.
16. Setiap KPI mempunyai formula dan sumber.
17. AI tidak boleh menganggap output yang meyakinkan sebagai bukti kebenaran.
18. Pelanggan perlu boleh melihat dan membetulkan pemahaman AI.
19. Tiada modul boleh dilabel production-ready tanpa bukti ujian dan operasi.
20. Kejayaan AI BIJAN diukur melalui hasil yang disahkan, bukan bilangan automasi yang dijalankan.

## 125. Definisi visi akhir

AI BIJAN disasarkan menjadi platform operasi perniagaan yang boleh:

- memahami model bisnes pelanggan;
- menyediakan konfigurasi awal yang sesuai;
- menghubungkan operasi dan data;
- membantu mencari prospek;
- mengurus CRM dan follow-up;
- menyediakan proposal dan membantu proses closing;
- menyokong invoicing dan collection;
- mengesahkan serta merekonsiliasi pembayaran melalui integrasi yang sah;
- menyelaras operasi selepas jualan;
- memantau prestasi;
- mengenal pasti peluang penambahbaikan;
- menjalankan tugasan rutin secara terkawal;
- meminta manusia membuat keputusan apabila risiko atau ketidakpastian memerlukannya.

**Prinsip produk:** Understand the business → Configure the system → Find opportunities → Convert leads → Deliver value → Verify payment → Learn from outcomes → Improve operations.

**Matlamat bukan automasi tanpa kawalan. Matlamatnya ialah perniagaan yang lebih autonomi, telus, boleh diaudit dan boleh dipercayai.**


---

# PART XXVII — UNIVERSAL ORGANIZATION, ACCESS & GOVERNANCE ENGINE

## 126. Objective and mandatory status

This section is a mandatory platform foundation, not an optional industry module. Every tenant must use the same authorization engine while applying different organization templates, data scopes, workflows and industry rules.

The design below is a target specification. It is not proof that the functions have already been implemented or production-tested.

## 127. Organization hierarchy

Support flexible, configurable hierarchy levels:

`Platform → Tenant/Business Group → Legal Entity → Brand → Region → Branch/Outlet/Site → Department/Team → Position → User`

Rules:
- A tenant may have one or multiple legal entities, brands, branches, sites and departments.
- Do not force every business to use every hierarchy level.
- A person may hold multiple assignments, such as cashier at Branch A and supervisor at Branch B.
- Assignments must have effective dates, status, reporting manager and explicit scopes.
- External accountants, auditors, contractors, agencies, vendors and clients must be represented as external identities or memberships with restricted scope.
- Organization restructuring must preserve historical records and effective dates.

## 128. Authorization model

Use **RBAC + ABAC + resource relationships** rather than role names alone.

- RBAC: role grants a set of actions.
- ABAC: attributes such as tenant, branch, department, employment status, record classification, time, transaction amount and approval status restrict those actions.
- Relationship checks: user may access records assigned to them, their team, their branch, or records they are explicitly authorized to support.
- Tenant isolation: every tenant-owned request and background job must enforce tenant context at the service/data layer.
- Deny by default: missing or ambiguous permission means deny.
- Server-side enforcement: hiding a button in the UI is not authorization.
- Re-check permissions at execution time, especially for AI tool calls and queued jobs.
- Emergency access must be time-limited, reason-coded, logged and reviewed.

## 129. Standard action vocabulary

Every module must define permissions using a consistent action vocabulary:

`view_list`, `view_detail`, `create`, `edit`, `delete`, `archive`, `restore`, `assign`, `comment`, `upload`, `download`, `export`, `import`, `approve`, `reject`, `publish`, `send`, `refund`, `pay`, `reconcile`, `manage_settings`, `manage_permissions`, `manage_integrations`.

Not every resource supports every action. Destructive and financial actions should be separate from ordinary edit permissions.

## 130. Access scopes

Each grant must include one or more scopes:

- `own`: records created by or assigned to the user, where appropriate.
- `assigned`: records explicitly assigned to the user.
- `team`: records belonging to the user's team.
- `department`: records belonging to the department.
- `branch`: records belonging to an authorized branch/site.
- `region`: records within an authorized region.
- `entity`: records for an authorized legal entity.
- `tenant`: all permitted records in one tenant.
- `platform`: platform-level resources only, never automatic access to tenant business content.

Scope must be applied to reads, writes, exports, reports, search, AI retrieval, notifications, files and integrations—not only the main page.

## 131. Baseline role catalogue

| Role template | Default scope | Typical access | Explicit exclusions by default |
|---|---|---|---|
| Business Owner | Assigned business/entity | Business overview, organization, subscription, sensitive approvals, access governance | Platform secrets and other tenants' data |
| Co-owner/Director | Explicit entity/scope | Strategic reports and assigned approvals | Ownership transfer or unrestricted bank actions unless separately granted |
| Executive/General Manager | Assigned entity/region | Cross-department KPIs, approved operating decisions | Owner-only subscription, ownership and sensitive credentials |
| Operations Manager | Assigned region/branches | Scheduling, fulfilment, capacity, operational approvals | Unrelated financial data and access administration |
| Branch Manager | Assigned branch | Branch staff, schedules, sales and branch operations | Other branches and corporate-only settings |
| Department Manager | Assigned department | Department records, staffing and KPI | Other departments outside scope |
| Business Administrator | Configured resources/scope | Master data, user support, records and operations allowed by policy | Automatic access to bank credentials, payroll, ownership or all finance |
| Supervisor/Team Lead | Assigned team | Assign work, verify progress, first-level approvals | Company-wide settings and unrelated staff records |
| Staff/Operator | Own/assigned/team as required | Assigned tasks, schedules, required operational records | Payroll of others, sensitive finance and unrelated customer records |
| Finance/Accountant | Assigned entities/accounts | Invoices, expenses, receivables, payables, reconciliation and reports as granted | Payment initiation or bank changes unless separately authorized |
| Cashier/Sales Counter | Assigned outlet/register | Checkout, receipts, permitted discounts and shifts | Payroll, supplier bank details and unrestricted refunds |
| HR/People Admin | Assigned entities | Employee records, leave, onboarding and policy workflows | Unrestricted finance or clinical/customer sensitive records |
| Marketing | Assigned brand/campaign | Content, audiences with lawful access, campaigns and analytics | Raw sensitive records and payment execution |
| Customer Service | Assigned queues/customers | Approved FAQs, cases, order status and conversation history | Full card/bank data, unrestricted exports and unrelated HR data |
| Procurement | Assigned entity/category | Suppliers, purchase requests, purchase orders and delivery status | Self-approval of own purchases above policy |
| Auditor/Compliance | Read-only assigned scope | Evidence, reports, logs and controlled exports | Editing source records or approving own findings |
| External Vendor/Contractor | Specific work orders/projects | Assigned deliverables, limited contacts and uploads | Internal HR, finance, customer database and unrelated projects |
| Customer/Client | Own account/transactions | Own bookings, orders, invoices, tickets and consent settings | Other customers' records or internal notes |
| Integration Service Account | Explicit API resources | Narrow machine-to-machine operations | Interactive access and broad wildcard permissions |
| Platform Support | Minimum operational scope | Technical diagnostics with approved access | Routine browsing of tenant business data; exceptional access must be approved and audited |

These are starting templates. The tenant must confirm and adjust them before activation.

## 132. Permission decision formula

A request is allowed only when all required checks pass:

1. User/session is authenticated and active.
2. Tenant and organization membership are valid.
3. Role grant contains the action.
4. Resource scope matches.
5. Record classification allows the access.
6. Relevant business state permits the action.
7. Required approval is present and still valid.
8. Integration/AI policy allows the tool and data flow.
9. No suspension, revocation, legal hold or emergency block applies.

A permission grant must not override a platform-level security prohibition.

## 133. Ownership and privileged access

- Owner status is an explicit business relationship, not merely a role label.
- Ownership transfer requires strong re-authentication, confirmation by authorized parties and a durable audit record.
- Support multiple owners only if the tenant's legal and governance setup permits it.
- Separate subscription billing contact, legal owner, finance approver and day-to-day administrator.
- Recovery and lost-owner procedures must be documented.
- Platform super-admin access is separate from tenant owner access.
- Never expose API keys, passwords, access tokens, secret keys or full payment credentials to AI conversation context or ordinary users.

## 134. Custom roles and permission lifecycle

Owner-authorized administrators can:
- Create a role from a template.
- Name and describe the role.
- Select resources, actions and scopes.
- Set restrictions and approval limits.
- Preview effective access.
- Compare permissions with another role.
- Assign users and effective dates.
- Test with a permission simulator.
- Publish a version and roll back to a previous version.

Guardrails:
- Warn on broad permissions, wildcard scopes and incompatible duties.
- Prevent self-escalation unless a separate authorized approval process permits it.
- Require additional approval for granting permission administration, export of sensitive data, payment execution or audit-log administration.
- Notify affected owners/admins when high-risk access changes.
- Record who changed the role, what changed, when, why and approval reference.

## 135. Segregation of duties and approval matrix

Separate requester, reviewer, approver and executor where the risk warrants it.

| Action | Requester | Reviewer | Final approval | Execution control |
|---|---|---|---|---|
| Purchase request | Staff/manager | Procurement/budget owner | According to amount and policy | PO issued only after approval |
| Supplier onboarding | Procurement | Finance/compliance | Authorized manager | Bank-detail change independently verified |
| Discount | Sales/cashier | Supervisor | Within delegated threshold | System enforces approved limit |
| Refund | Customer service/cashier | Supervisor/finance | According to amount and reason | Payment provider confirms result |
| Payroll run | HR/payroll | Finance | Authorized approver | Payroll export/payment controlled separately |
| Bank detail change | Authorized requester | Independent verifier | Owner/finance policy | Strong authentication and change alert |
| Contract | Sales/project owner | Legal/management if required | Authorized signatory | E-signature evidence retained |
| Mass campaign | Marketing | Compliance/owner as configured | Campaign budget owner | Consent, suppression, audience and rate checks |
| Role escalation | Admin | Owner/security | Authorized owner | Access re-evaluated immediately |
| Data deletion/export | Authorized requester | Privacy/data owner if needed | Policy-based approval | Audit and retention rules enforced |

Thresholds are tenant-configurable and must not be invented by AI.

## 136. Staff lifecycle

Required workflows:
1. Invitation and identity verification.
2. Employment/contract type and effective dates.
3. Role, manager, branch and department assignment.
4. Least-privilege access provisioning.
5. Required training, policies and acknowledgements.
6. Probation or temporary access review where applicable.
7. Transfer, promotion, leave, suspension or contract extension.
8. Immediate access revocation at exit or defined risk event.
9. Return of assets and transfer of assigned work.
10. Retention/deletion of personal access and business records according to policy.

When a staff member transfers branches, revoke the old scope and grant the new scope atomically where possible. Preserve audit history.

## 137. Access reviews and security monitoring

- Periodic review of privileged and sensitive access.
- Detect dormant users, stale external accounts and expired assignments.
- Alert on unusual export volume, repeated denials, permission escalation and suspicious automation.
- Support MFA, SSO where appropriate, session revocation and device/session management.
- Record access decisions and high-risk data access.
- Protect audit logs against ordinary administrators changing or deleting evidence.
- Provide tenant-level data retention and legal-hold policies.
- Test cross-tenant access denial in automated security tests.

## 138. AI-specific authorization

AI is a delegated actor, not an independent super-user.

For every AI tool call:
- Resolve the requesting human identity and tenant.
- Apply the human's current permissions and the agent's additional restrictions.
- Enforce resource and branch scope at the tool/API layer.
- Classify action risk and require approval where configured.
- Validate arguments against schemas and allowed destinations.
- Use allowlisted tools; never let model output directly execute arbitrary SQL, shell commands or unrestricted HTTP requests.
- Require explicit confirmation for external messages, mass sends, destructive actions and financial commitments according to policy.
- Record requested action, actual tool, inputs/outputs references, approval and source-system result.
- Re-check authorization when queued tasks execute.
- Redact secrets and unnecessary personal data from prompts and logs.

## 139. Mandatory access acceptance tests

- [ ] Branch A user cannot read Branch B records without permission.
- [ ] A tenant cannot access another tenant by changing an ID, URL, export filter or API argument.
- [ ] Hiding a button does not substitute for server-side permission checks.
- [ ] An AI assistant cannot access data that its user cannot access.
- [ ] Revoked access is respected by active sessions and queued AI jobs.
- [ ] Export, search, reporting, files and notifications enforce the same scope.
- [ ] User cannot approve their own restricted transaction where segregation is required.
- [ ] External vendor sees only assigned project/work-order information.
- [ ] Owner transfer and privilege escalation produce immutable audit evidence.
- [ ] Integration credentials are encrypted, scoped, rotatable and never exposed to the model.
- [ ] Custom role changes can be simulated, reviewed, versioned and rolled back.
- [ ] Emergency access expires and is reviewed.

---

# PART XXVIII — INDUSTRY BLUEPRINT AND ROLE PACKS

## 140. Industry configuration principle

An industry pack contains:
- Business discovery questions.
- Organization and role templates.
- Permission/resource matrix.
- Data model and classification.
- Standard operating workflows.
- Approval thresholds as configurable placeholders.
- Forms, documents and notification templates.
- KPIs and calculation definitions.
- AI assistant skills and allowed tools.
- Integration recommendations.
- Industry-specific legal/compliance checklist for qualified review.
- Seed data and acceptance tests.
- Known limitations and version history.

Do not create separate authorization logic for each industry. Industry packs configure the shared engine.

## 141. Starter sector packs

| Sector | Example positions | Core workflows | Core records/KPIs |
|---|---|---|---|
| Restaurant, cafe and F&B | Owner, outlet manager, chef, kitchen lead, service crew, cashier, inventory/procurement, delivery coordinator | Menu, recipe/costing, order, kitchen ticket, table/queue, stock, waste, supplier purchase, delivery, complaint | Sales by outlet, food cost, waste, order prep time, stock variance, repeat visits |
| Retail and minimart | Owner, area manager, store manager, cashier, stock controller, merchandiser, buyer, e-commerce operator | POS, stock transfer, receiving, price changes, promotion, returns, purchase order, online order fulfilment | Gross margin, stock turns, shrinkage, basket size, sell-through |
| E-commerce and social commerce | Owner, store admin, catalog manager, customer service, packer, warehouse lead, ads manager | Catalog, channel sync, orders, payment, packing, shipping, returns, review and campaign | Conversion, CAC, ROAS, fulfilment time, return rate, contribution margin |
| Event management | Owner, event director, producer, project manager, coordinator, creative lead, vendor manager, finance, crew | Brief, budget, timeline, venue/vendor quotation, contract, deposit, run sheet, guest list, assets, event-day incident, final settlement | Budget variance, task readiness, vendor SLA, event margin, payment collection |
| Construction and contractor | Director, project manager, site supervisor, QS, procurement, safety officer, storekeeper, subcontractor, technician | Tender, estimate, BOQ, variation order, procurement, site diary, work progress, inspection, safety, claim and retention | Cost-to-complete, progress, variation value, defects, incidents, overdue claims |
| Clinic and healthcare | Clinic owner/administrator, clinician, nurse, receptionist, billing staff, pharmacist where applicable, compliance officer | Appointment, registration, consent, clinical record, prescription/dispensing where applicable, billing, referral, follow-up, stock and incident | Waiting time, appointment utilisation, no-show rate, stock expiry, claims status |
| Salon, barber and wellness | Owner, outlet manager, receptionist, stylist/barber, therapist, beautician, stock lead | Appointment, staff availability, service catalogue, packages, customer preferences, consent, commission, stock and aftercare | Utilisation, rebooking, average ticket, service margin, cancellations |
| Professional services and agency | Partner/owner, account director, project manager, consultant, creative, analyst, finance | Lead, discovery, proposal, retainer, timesheet, deliverables, review, client approval, invoice and renewal | Billable utilisation, project margin, scope creep, collection days, client retention |
| Logistics, courier and fleet | Fleet manager, dispatcher, driver/rider, warehouse lead, customer service, safety officer | Booking, dispatch, pickup, proof of delivery, route, vehicle maintenance, failed delivery, claims | On-time delivery, cost per stop, utilisation, failed delivery, claims |
| Property and facilities | Property manager, leasing, building manager, technician, security, cleaning supervisor, finance | Listing, viewing, tenancy, rent, maintenance request, inspection, contractor work order, deposit | Occupancy, arrears, maintenance SLA, renewal rate, asset downtime |
| Education and training | Centre owner, academic manager, trainer, admissions, student support, finance | Lead, enrolment, timetable, attendance, learning materials, assessment, certificate, fee collection | Enrolment conversion, attendance, completion, fee arrears, satisfaction |
| Hotel and accommodation | General manager, front office, housekeeping, maintenance, reservations, revenue manager | Booking, check-in/out, room assignment, housekeeping, maintenance, guest request, refund and review | Occupancy, ADR, RevPAR, room turnaround, guest satisfaction |
| Manufacturing and wholesale | Plant manager, production planner, quality, warehouse, procurement, sales, maintenance | Sales order, production order, BOM, material planning, QC, batch/lot, dispatch, returns | Yield, defects, downtime, on-time fulfilment, inventory accuracy |
| Agriculture and plantation | Estate manager, field supervisor, agronomist, storekeeper, machinery operator, worker | Field plan, inputs, work allocation, harvest, weighing, equipment, safety, transport and traceability | Yield, cost per hectare, input usage, downtime, quality |
| Automotive workshop | Workshop owner, service advisor, foreman, mechanic, parts controller, cashier | Appointment, vehicle intake, diagnosis, estimate approval, repair job card, parts, QC, invoice, warranty | Bay utilisation, turnaround, comeback rate, parts margin |
| NGO and association | Board, executive director, programme manager, field officer, finance, volunteer coordinator | Membership, donation, grant, programme, volunteer, expense approval, beneficiary case, reporting | Fund utilisation, programme reach, restricted-fund compliance, outcome metrics |

Sector packs must be reviewed with actual domain users. Healthcare, finance, construction safety and other regulated or high-risk sectors need specialist review before production use.

## 142. Role pack data design

Store industry templates as versioned configuration rather than hard-coded conditions:
- `industry_blueprints`
- `industry_blueprint_versions`
- `industry_role_templates`
- `industry_permission_templates`
- `industry_workflow_templates`
- `industry_form_templates`
- `industry_kpi_definitions`
- `industry_integration_recommendations`
- `industry_acceptance_tests`
- `tenant_blueprint_installs`
- `tenant_blueprint_overrides`

Every tenant override should record the source template version and local changes. Updating a template must not silently overwrite customer-approved settings.

---

# PART XXIX — OWNER BUSINESS TOOLKIT

## 143. Owner Command Centre

The owner's home screen should answer, without navigating through many modules:
- How much money was received, invoiced and remains outstanding?
- What is actual revenue versus gross profit estimate and cash position?
- Which leads and proposals need attention today?
- Which orders, projects, events or jobs are late or at risk?
- What stock, staffing or supplier issue may interrupt delivery?
- Which marketing campaigns are spending and converting?
- Which customer complaints or churn risks need intervention?
- Which approvals require the owner?
- Which integrations or automations have failed?
- What should the owner do next, and why?

Every metric needs a definition, source, last-updated time and drill-down to authorized source records.

## 144. Required owner-facing tools

### 144.1 Sales and CRM
- Unified lead inbox and pipeline.
- Lead capture forms and landing pages.
- Lead deduplication and qualification.
- Sales task and follow-up scheduler.
- Customer/company directory.
- Quote, proposal and contract preparation.
- Appointment/meeting booking.
- Deal handover to operations.
- Customer history and repeat-sales opportunities.
- Sales forecast with assumptions and confidence.

### 144.2 Customer service
- Shared inbox across connected channels.
- AI FAQ and knowledge-base answers.
- Ticketing, priority, SLA and escalation.
- Order/booking status lookup under permission.
- Human handover and conversation summaries.
- Complaint and refund workflows.
- Customer satisfaction surveys.
- Service quality analytics.
- Approved response templates and multilingual support.

### 144.3 Marketing and content studio
- Content calendar and approval.
- AI text, image, short-video script and creative brief generation.
- Brand voice, product facts and prohibited claims library.
- Campaign/ad copy variants.
- SEO briefs, product descriptions and blog drafts.
- Social post scheduling where supported.
- Email and consent-based messaging campaigns.
- Audience segmentation and suppression lists.
- Landing page and lead form builder.
- UTM and campaign attribution.
- Creative asset library and versioning.
- A/B test planning and reporting.
- Budget caps, spend alerts and campaign pause controls.

Generated content must be reviewed where claims, prices, offers, regulated products or brand risk are material. Platform APIs and publishing permissions vary by channel.

### 144.4 Advertising command centre
- Google Ads and Meta Ads account connection.
- TikTok Ads connection where eligible.
- Campaign, ad group/ad set, creative and conversion reporting.
- Spend pacing and budget alerts.
- UTM and conversion tracking checks.
- Lead quality and offline conversion feedback where supported.
- ROAS, CAC and cost per qualified lead.
- Draft campaign generation and human approval.
- Change log and rollback where the provider supports it.
- Automated spend limits and kill switch.

Do not promise that one API can control every ad feature. Each provider has scopes, review requirements, account eligibility and API limitations.

### 144.5 E-commerce and order management
- Product/catalogue and price list.
- Stock availability and reservation.
- Unified orders across connected stores/channels.
- Packing, picking, fulfilment and returns.
- Shipping label and tracking.
- Refund request workflow.
- Marketplace order sync where API/partner access permits.
- Promotions, bundles and coupons.
- Customer reviews and post-purchase support.

### 144.6 Finance and cash management
- Quotations, invoices, receipts and credit notes.
- Accounts receivable and payable.
- Expense capture and approval.
- Budget vs actual.
- Cash-flow forecast with explicit assumptions.
- Bank feed/reconciliation where available and authorized.
- Payment matching and discrepancy queue.
- E-invoice readiness and MyInvois integration path.
- Tax/accounting export and accountant access.
- Profitability by product, branch, campaign, client and project.
- Multi-entity reporting when supported.

AI may prepare suggestions and drafts; final accounting/tax treatment must follow approved configuration and qualified advice. Never treat a payment screenshot or browser redirect as confirmed payment.

### 144.7 HR and workforce
- Employee directory and organization chart.
- Recruitment pipeline and onboarding.
- Roster, attendance and leave.
- Shift swap and overtime approval.
- Timesheets and project/job allocation.
- Training, SOP acknowledgement and skills.
- Payroll preparation/export to a supported payroll system.
- Performance check-ins and workforce planning.
- Offboarding and immediate access revocation.

Do not make high-impact employment decisions solely from AI scoring.

### 144.8 Operations and projects
- Task/project/work-order management.
- SOP and checklist builder.
- Resource, vehicle, equipment and room scheduling.
- Procurement requests and purchase orders.
- Inventory, reorder levels and stock transfer.
- Supplier performance and delivery tracking.
- Quality inspections and incident reporting.
- SLA, deadline and exception alerts.
- Customer/vendor portals.
- Document, photo and proof-of-work capture.

### 144.9 Documents and meetings
- Central file repository with permissions and retention.
- OCR and structured data extraction.
- Document templates, merge fields and version history.
- Meeting agenda, notes, action items and follow-up.
- E-signature provider connection.
- Contract renewal and expiry reminders.
- Search across approved business knowledge.
- Evidence bundles for audits and projects.

### 144.10 Website and digital presence
- Website/landing page builder or CMS connection.
- Domain and SSL status monitoring.
- Forms, booking widgets and chat.
- Google Business Profile workflow where authorized.
- Review monitoring and reply drafts.
- Local SEO checklist and analytics.
- Website uptime, form and conversion monitoring.
- Web analytics and consent configuration.

### 144.11 Customer retention and loyalty
- Customer segments based on allowed business data.
- Repeat purchase and renewal reminders.
- Membership, loyalty and referral rules.
- Coupons and win-back campaigns.
- Customer lifetime value estimates.
- Churn-risk signals with explanation.
- Feedback and complaint root-cause analysis.
- Consent, communication preference and opt-out management.

### 144.12 Owner governance
- Approval inbox.
- Delegation and spending limits.
- Business goals and quarterly priorities.
- KPI/OKR tracking.
- Risk and incident register.
- Subscription and integration cost dashboard.
- AI usage/cost budgets.
- Access review and audit logs.
- Data export/retention controls.
- Backup, restore and business continuity status.

---

# PART XXX — MALAYSIA BUSINESS INTEGRATION CATALOGUE

## 145. Integration strategy

AI BIJAN should be an integration hub, not a brittle collection of one-off scripts. Prioritize tools by customer demand, API quality, achievable partnership, operational value and cost.

Integration status must be visible:
- `Native`: built and maintained by AI BIJAN.
- `Partner`: maintained through an approved partner.
- `Connector`: connected through a supported automation provider.
- `Import/Export`: CSV, Excel or scheduled file workflow.
- `Planned`: not yet available.
- `Unavailable`: no supported method currently verified.

Never display a connector as “connected” until authentication and a test sync succeed.

## 146. Shipping, courier and fulfilment

Priority candidates:
- EasyParcel — courier rate comparison, shipment creation, airway bill/label, tracking and status updates subject to API/account eligibility.
- Direct courier integrations where commercially justified: Pos Laju/Pos Malaysia, J&T Express, Ninja Van, DHL eCommerce, City-Link Express and other providers requested by customers.
- E-commerce order sources such as Shopify, WooCommerce and marketplaces through approved APIs/partners.
- Warehouse/fulfilment partners as customer demand warrants.

Core workflow:
`Paid/approved order → validate address and parcel → compare eligible services → create shipment → print label → dispatch → track → notify customer → handle exception/return`.

Do not assume every courier offers identical APIs, rates or account access. Verify current developer documentation and partner terms before committing to a build.

## 147. Payments and collections

Priority candidates:
- Billplz.
- senangPay.
- ToyyibPay.
- Stripe where the use case and account eligibility fit.
- FPX and supported card/e-wallet methods through the selected gateway.
- DuitNow QR or bank/payment integrations where supported.
- Payment links and recurring billing where provider terms allow.

Required capabilities:
- Payment intent/link creation.
- Verified server-side webhook/callback handling.
- Idempotency and duplicate prevention.
- Refund request and status tracking.
- Payment-to-invoice matching.
- Failed/pending transaction recovery.
- Settlement and reconciliation reports.
- Provider health and incident alerts.

Keep payment providers pluggable. Do not store raw card credentials. Use provider-hosted/tokenized payment flows and follow applicable security requirements.

## 148. Accounting, tax and e-Invoice

Priority candidates:
- LHDN MyInvois API.
- Bukku.
- AutoCount.
- SQL Account / SQL Accounting.
- Xero.
- QuickBooks Online.
- ABSS Accounting.
- Other accounting products requested by target segments.

Core functions:
- Customer/supplier sync.
- Invoice, credit note and receipt sync.
- Approved sales/expense exports.
- Tax/e-Invoice status and rejection handling.
- Reconciliation support.
- Sync error queue and retry.
- External document ID mapping.
- Audit history and source-of-truth rules.

MyInvois integration needs version-aware implementation, credential handling, validation, submission status and rejection recovery. Confirm current LHDN requirements and effective dates for each customer; do not hard-code tax advice into the AI.

## 149. POS and retail/F&B systems

Priority candidates:
- StoreHub.
- Qashier.
- Slurp.
- Existing POS through API, webhooks, approved exports or partner connectors.

Useful sync data:
- Outlet and terminal.
- Product/SKU and price.
- Sales totals and transaction references.
- Discounts and refunds.
- Inventory movements where supported.
- Customer/loyalty data subject to permission and consent.
- Shift summaries and payment tender totals.

Do not import every receipt as a separate accounting transaction if the accounting integration recommends daily summaries; preserve traceable source references and detail needed for audit.

## 150. E-commerce and marketplaces

Priority candidates:
- Shopify.
- WooCommerce.
- EasyStore where supported.
- Shopee and Lazada through their approved seller/partner APIs.
- TikTok Shop through available approved integrations.
- Other channels based on customer demand and API eligibility.

Core functions:
- Catalog and SKU mapping.
- Price and stock synchronization.
- Order import and status updates.
- Shipment tracking.
- Returns and cancellation handling.
- Channel-specific fees where available.
- Customer consent and platform policy compliance.
- Reconciliation against marketplace settlement reports.

Marketplaces frequently change API scopes, seller eligibility and approval requirements. Build a connector registry and contract tests instead of assuming permanent unrestricted access.

## 151. Messaging, customer support and marketing channels

Priority candidates:
- WhatsApp Business Platform / Cloud API.
- Meta Messenger and Instagram messaging where permitted.
- Email via approved providers.
- SMS providers selected for Malaysia coverage and pricing.
- Website live chat.
- Telegram Bot API for opted-in use cases.
- Google Business Profile messaging/review capabilities only where currently supported.

Required shared inbox features:
- Channel identity and message IDs.
- Consent and opt-out status.
- Conversation assignment and SLA.
- Approved templates and provider policy checks.
- Human takeover.
- Delivery/read/error status where exposed.
- Rate limits and retry safety.
- Suppression and audit history.

For WhatsApp, use the official Business Platform or a compliant provider. Respect template/category rules, opt-in, quality controls and current Meta policies. Do not automate unofficial WhatsApp Web sessions or bulk spam.

## 152. Advertising, analytics and lead acquisition

Priority candidates:
- Google Ads API.
- Google Analytics 4.
- Google Tag Manager.
- Google Business Profile APIs where approved.
- Meta Marketing API and lead forms.
- TikTok for Business APIs where eligible.
- Search Console.
- Connected CRM and landing-page form sources.

Core features:
- Secure account connection and token refresh.
- Account/campaign reporting.
- Spend pacing and alerting.
- UTM convention management.
- Lead-to-sale attribution with documented methodology.
- Conversion event diagnostics.
- Offline conversion upload where permitted.
- Draft and approval workflow for campaign changes.
- Provider API version monitoring.

Do not scrape private account data or store account passwords. API access, ad account permissions, business verification and review may be required.

## 153. Productivity, storage and identity

Priority candidates:
- Google Workspace: Gmail, Calendar, Drive and Sheets where authorized.
- Microsoft 365: Outlook, Calendar, OneDrive, SharePoint and Teams where authorized.
- Google Calendar and Microsoft Calendar scheduling.
- Zoom or Google Meet/Teams meeting workflows where supported.
- Dropbox or other file storage requested by customers.
- SSO providers and identity services for larger organizations.

Capabilities:
- Calendar availability and booking.
- Meeting preparation and follow-up tasks.
- Approved file search and extraction.
- Document generation and controlled sharing.
- Email drafting and sending with explicit scopes.
- Task/project synchronization.
- Identity provisioning where supported.

AI must only retrieve files and email content within the connected user's permissions and must respect provider access changes.

## 154. HR, payroll and workforce integrations

Potential candidates to evaluate:
- Swingvy.
- BrioHR.
- Payroll products used by local accountants and SMEs.
- SQL Payroll and other locally requested payroll systems.
- Attendance, biometric/time-clock and rostering systems with documented interfaces.

Use cases:
- Employee/organization sync.
- Leave and attendance summaries.
- Approved timesheet exports.
- Payroll preparation and variance checks.
- Onboarding/offboarding triggers.
- Role and branch updates.

Payroll and employee records are sensitive. Use strict field-level permissions, explicit sync scope, encryption and access logs. Do not assume every vendor provides a public API.

## 155. Forms, workflow and automation connectors

Evaluate:
- n8n.
- Make.
- Zapier.
- Webhooks and scheduled imports.
- Google Forms / Microsoft Forms or equivalent lead capture.
- CRM-specific connectors and approved API gateways.

Use them for long-tail integrations where building a native connector is not justified. Keep high-risk finance, identity and access decisions in AI BIJAN's controlled services rather than handing unrestricted authority to an external workflow.

## 156. CRM and customer acquisition systems

Potential candidates:
- HubSpot.
- Zoho CRM.
- Salesforce for larger organizations.
- Pipedrive.
- Existing spreadsheets and local CRM products.

Functions:
- Contact/company/deal sync.
- Pipeline and activity synchronization.
- Lead source and campaign attribution.
- Duplicate detection.
- Task/follow-up sync.
- Field mapping and conflict resolution.

Support a clear source-of-truth setting per field. Two-way sync without conflict rules can overwrite correct data.

## 157. Maps, location and field service

Potential candidates:
- Google Maps Platform.
- Mapbox.
- Address/postcode validation sources.
- Field-service and fleet systems requested by customers.

Functions:
- Geocoding and address validation.
- Distance/ETA estimation.
- Service area and route planning.
- Technician/driver assignment.
- Proof of visit and job completion.
- Location sharing only with clear purpose, access and retention rules.

Location tracking must be role- and job-scoped. Avoid continuous tracking when a task only requires a check-in or delivery proof.

## 158. Integration connector contract

Every connector must implement a standard lifecycle:
1. Discover and document provider capabilities.
2. Obtain consent and connect securely.
3. Validate scopes and account identity.
4. Run a test connection.
5. Map external IDs and fields.
6. Set source-of-truth and sync direction.
7. Process webhooks safely and idempotently.
8. Retry transient failures and isolate permanent failures.
9. Expose sync status, lag and last successful sync.
10. Support disconnect, token revocation and data-retention handling.
11. Version API schemas and run contract tests.
12. Provide manual recovery and reconciliation.

Suggested entities:
- `integration_providers`
- `integration_connectors`
- `integration_capabilities`
- `tenant_integrations`
- `integration_credentials`
- `integration_scopes`
- `integration_field_mappings`
- `integration_sync_runs`
- `integration_webhook_events`
- `integration_failures`
- `integration_external_id_maps`
- `integration_health_checks`
- `integration_consent_records`

Secrets must be encrypted in a dedicated credential vault or equivalent protected store, with rotation, least privilege and redaction.

## 159. Integration prioritization score

Score each candidate from 1–5 for:
- Demand among target customers.
- Number of customer segments helped.
- Revenue/retention impact.
- API maturity and documentation.
- Partnership and approval feasibility.
- Implementation effort (reverse score).
- Ongoing maintenance burden (reverse score).
- Security/compliance risk (reverse score).
- Availability of a reliable fallback.

Do not prioritize solely because a tool is popular. Prefer integrations that remove a real workflow bottleneck and can be maintained reliably.

---

# PART XXXI — BUSINESS DATA, PRIVACY AND MALAYSIA READINESS

## 160. Data classification

Minimum classifications:
- Public.
- Internal.
- Confidential.
- Restricted/sensitive.
- Regulated/sector-specific.

For each field or resource define:
- Purpose and lawful basis/authority.
- Data owner.
- Authorized roles/scopes.
- Retention period.
- Export policy.
- Encryption and masking rules.
- AI retrieval eligibility.
- External sharing policy.
- Deletion/anonymization policy.

## 161. Malaysia compliance readiness

Build configurable compliance support, not a claim of automatic legal compliance:
- Personal Data Protection Act (PDPA) obligations and amendments applicable to the business.
- LHDN MyInvois/e-Invoice requirements and current implementation timeline.
- Employment, payroll and statutory contribution workflows where relevant.
- SST/tax configuration as verified by the business and its adviser.
- Sector-specific rules for healthcare, financial services, construction, education and other regulated activities.
- Consent, marketing opt-out and record of communication preferences.
- Data breach and incident response workflow.
- Data export, correction and deletion requests.
- Vendor/subprocessor register and data-processing terms.

Legal applicability depends on the entity, sector, transaction and current regulations. Maintain a dated compliance register reviewed by qualified Malaysian advisers. Never hard-code an assumption as universal legal advice.

## 162. Privacy controls for AI and marketing

- Collect only necessary information.
- Record where lead/customer data originated.
- Distinguish public availability from permission to use for marketing.
- Maintain consent, opt-out and suppression controls per channel.
- Apply retention and deletion policies to prompts, attachments, conversations and derived data.
- Prevent one tenant's content from entering another tenant's retrieval index.
- Do not use sensitive personal attributes for inappropriate targeting.
- Redact secrets and unnecessary personal data from logs.
- Provide human review for uncertain, regulated or high-impact outputs.

---

# PART XXXII — PLATFORM CONTROL PLANE, OBSERVABILITY AND COMMERCIALS

## 163. Platform administration

Platform-level functions:
- Tenant provisioning, suspension and recovery.
- Subscription plans, usage quotas and entitlements.
- Module and connector catalogue.
- AI model/provider routing.
- Tenant-specific policy defaults.
- Feature flags and staged releases.
- Provider health and incident management.
- Abuse and fraud monitoring.
- Support access with approval and audit.
- Billing, metering, refunds and credit adjustments.
- Data export/deletion orchestration.
- Platform-wide kill switches.

Platform administrators must not automatically receive unrestricted business-data access.

## 164. AI and automation metering

Track separately:
- Model/provider cost.
- Tokens or usage units.
- Image/audio/video generation cost.
- External API costs.
- Messaging cost.
- Workflow execution cost.
- Customer billable usage.
- Credits, refunds and adjustments.
- Failed/retried task cost.
- Gross margin by tenant/module.

Display estimate versus actual cost and explain metering rules. Prevent runaway loops with per-task, per-agent, per-tenant and platform-level budgets.

## 165. Reliability and observability

Required:
- Structured logs with tenant and correlation IDs.
- Metrics for latency, error rate, queue lag and sync lag.
- Distributed tracing for multi-step workflows.
- Alerting for payment, messaging, AI and integration failures.
- Queue retry and dead-letter dashboards.
- Backup and restore tests.
- Disaster recovery objectives.
- Rate limiting and abuse detection.
- Dependency and API-version monitoring.
- Status page and incident communication.
- Rollback and migration procedures.
- Production runbooks and on-call ownership.

## 166. Testing and release gates

A module cannot be marked production-ready solely because a screen or endpoint exists.

Release gates:
- Unit and integration tests.
- Authorization and tenant-isolation tests.
- End-to-end workflow tests.
- Negative and abuse tests.
- Provider sandbox tests.
- Idempotency/retry tests.
- Performance tests on low-end phones and slow networks.
- Accessibility and localization checks.
- Backup/restore validation where relevant.
- Security review for sensitive features.
- Business acceptance tests with real sector users.
- Monitoring and rollback plan.
- Documented known limitations.

## 167. Non-functional requirements

Set measurable service-level objectives for:
- Availability.
- API latency.
- Queue processing delay.
- Recovery time and recovery point.
- Data synchronization freshness.
- Mobile performance.
- Search/reporting latency.
- Maximum import/export size.
- Security event detection.
- Support response and incident escalation.

Targets must be set based on architecture, budget and customer commitments; do not claim an SLA until it is funded, measured and contractually defined.

---

# PART XXXIII — PRODUCT PACKAGING AND ROLLOUT

## 168. Suggested product layers

### Core platform
- Organization, access and audit.
- Business profile and onboarding.
- Basic dashboards and tasks.
- Connector hub.
- Notifications and document storage.
- AI workspace with controlled tool use.

### Growth
- CRM and sales pipeline.
- Lead capture and qualification.
- Content studio.
- Campaign planning and analytics.
- Approved outreach and follow-up.
- Customer service inbox.

### Operations
- Inventory and procurement.
- Projects, work orders and scheduling.
- Staff/branch operations.
- Approvals and SOP.
- Supplier/customer portals.

### Finance
- Quotes, invoices and collections.
- Expense and approval workflows.
- Accounting integrations.
- MyInvois path.
- Reconciliation and profitability reports.

### Industry packs
- F&B, retail, event management, construction, clinic, salon/wellness, professional services, logistics, property, education, accommodation, manufacturing and additional validated sectors.

### Enterprise
- Multi-entity governance.
- SSO/advanced identity.
- Advanced audit and policy controls.
- Dedicated integration support.
- Custom roles/workflows.
- Data warehouse/BI integrations.
- Enterprise support and contractual service objectives.

Packaging is a commercial proposal, not a statement that every feature is already available.

## 169. Recommended implementation sequence

1. **Foundation:** tenant isolation, organization hierarchy, permission engine, audit and staff lifecycle.
2. **Business understanding:** discovery, facts/assumptions, sector blueprint, human approval and versioning.
3. **Owner command centre:** unified tasks, approvals, alerts and source-linked KPIs.
4. **Core business records:** CRM, customer, catalogue, orders, documents and workflow engine.
5. **Malaysia essentials:** payment gateway abstraction, invoicing and MyInvois readiness; prioritize connectors from validated customer demand.
6. **Growth tools:** customer service, content studio, consent management, campaigns and sales follow-up.
7. **Integration hub:** shipping, accounting, POS, e-commerce, messaging, ads and productivity tools.
8. **Industry packs:** pilot a small number of sectors with real customers before broad expansion.
9. **Controlled autonomy:** event-driven orchestration, approvals, budgets, kill switches and recovery.
10. **Scale and enterprise:** SSO, multi-entity reporting, observability, disaster recovery and service objectives.

Do not attempt to build every sector pack and every integration at once. Launch a complete vertical workflow, prove reliability and expand using shared platform components.

## 170. Definition of complete

AI BIJAN is considered complete for a given industry/workflow only when:
- [ ] Business model and operating profile can be confirmed by the customer.
- [ ] Organization and role templates cover real responsibilities.
- [ ] Permission and data-scope matrix is explicit and tested.
- [ ] Approval limits and separation of duties are configurable.
- [ ] Core workflows cover success, exception, cancellation and recovery paths.
- [ ] AI tools respect the same access controls.
- [ ] Integrations have verified authentication, scope, sync and failure handling.
- [ ] KPI formulas and source-of-truth systems are documented.
- [ ] Security, privacy and compliance reviews are completed as applicable.
- [ ] Acceptance tests pass and evidence is retained.
- [ ] User training, support and rollback procedures exist.
- [ ] Product status accurately distinguishes planned, beta and production-ready.

**Final principle:** one secure platform, configurable organizations, industry-specific operations, connected business tools, measurable outcomes and controlled AI autonomy.

---

# PART XXXIV — MASTER CATALOGUE EXPANSION (v4.4)

> This catalogue expands the product scope for discovery and planning. It is not a claim that every module, connector, sector rule, or AI capability has already been implemented. Every item must have an owner, priority, delivery status, acceptance criteria, and source of truth.

## 171. Enterprise-Wide Module Catalogue

### 171.1 Identity, access, and tenant administration
- Registration, login, logout, email/phone verification, password reset, MFA/passkeys, session and device management.
- Individual profile, business profile, company registration details, tax identifiers, billing profile, locale, time zone, language, and accessibility preferences.
- Tenant creation, onboarding, suspension, reactivation, deletion request, export, retention, and tenant isolation.
- Multiple businesses per user; multiple legal entities per business group; branches, outlets, sites, projects, departments, teams, cost centres, and virtual teams.
- Invitation links, staff onboarding, role assignment, temporary access, access expiry, transfer, leave, termination, contractor access, and emergency access.
- Role-based access control (RBAC), attribute-based access control (ABAC), relationship-based checks, resource ownership, field-level restrictions, row-level restrictions, and delegated authority.
- Custom roles, cloned roles, permission preview, effective-access explanation, access review, conflict detection, and least-privilege recommendations.
- Single sign-on, identity-provider integration, SCIM provisioning, API clients, service accounts, secrets rotation, and privileged-access workflows where required.

### 171.2 Business setup and understanding
- Business discovery interview, business model, sector, sub-sector, legal entity, locations, operating hours, service area, customer segments, channels, products, services, suppliers, pricing, capacity, and operating constraints.
- Organization chart, department map, job catalogue, responsibility matrix (RACI), approval matrix, delegation rules, escalation paths, and backup owners.
- Existing systems inventory, data inventory, integration inventory, process map, SOP library, policy library, risk register, KPI dictionary, and source-of-truth register.
- AI-generated business blueprint with confirmed facts, assumptions, unknowns, recommendations, customer confirmation, version history, and change impact analysis.
- Readiness scoring for digital adoption, data quality, automation suitability, security, compliance, and integration complexity.

### 171.3 Workspace and daily operations
- Unified dashboard, task inbox, notification centre, calendar, bookings, appointments, shifts, jobs, projects, cases, requests, approvals, checklists, forms, notes, comments, mentions, and activity timeline.
- Recurring tasks, dependencies, SLAs, due dates, priorities, ownership, workload balancing, queues, escalations, handovers, and exception queues.
- File storage, document templates, e-signature integration, versioning, retention labels, search, OCR, metadata extraction, and document access policies.
- Knowledge base, SOPs, internal announcements, meeting notes, decisions log, action register, staff acknowledgements, and training records.
- Universal search across authorized records; saved views; configurable dashboards; exports; bulk actions; import validation; deduplication; and data-quality queues.

### 171.4 Customer and sales management
- CRM contacts, organizations, leads, opportunities, pipeline stages, lead source, scoring, assignment, follow-up, quotations, proposals, contracts, renewals, upsell, cross-sell, referrals, and lost-reason analysis.
- Customer profiles, consent and preferences, service history, purchases, invoices, complaints, support cases, loyalty, memberships, subscriptions, and customer lifetime value estimates.
- Sales targets, territory management, sales activities, call notes, meeting records, reminders, commission rules, discount approval, and revenue forecasts.
- Forms, landing pages, website chat, lead capture, campaign attribution, UTM tracking, conversion events, and customer journey analytics.

### 171.5 Products, services, inventory, and procurement
- Product/service catalogue, variants, SKUs, bundles, packages, add-ons, categories, attributes, price lists, promotional prices, taxes, units of measure, and availability rules.
- Stock by location, stock movement, batch/lot, serial number, expiry, reorder point, purchase requisition, purchase order, goods received, supplier returns, stocktake, wastage, and inventory valuation.
- Supplier onboarding, due diligence, contracts, price comparison, quotation requests, supplier scorecards, delivery performance, and supplier payment status.
- Service capacity, appointment slots, staff skills, equipment availability, resource allocation, job estimates, bill of materials, and service completion evidence.

### 171.6 Orders, bookings, projects, and delivery
- Cart, checkout, order, reservation, appointment, work order, job card, project, event, ticket, delivery, dispatch, cancellation, refund, exchange, reschedule, and dispute.
- Status machine with allowed transitions, responsible actor, timestamps, evidence, customer notification, timeout rules, retries, and exception handling.
- Availability and capacity, deposits, milestone billing, partial payments, fulfilment, proof of delivery, OTP verification where suitable, customer acceptance, ratings, and after-sales service.
- Project milestones, resource plans, budgets, change requests, risk/issue registers, timesheets, progress claims, and close-out.

### 171.7 Finance and commercial controls
- Quotes, sales orders, invoices, credit notes, debit notes, receipts, refunds, payment reconciliation, supplier bills, expenses, petty cash, purchase approvals, and recurring billing.
- Cash flow, budget vs actual, profitability, cost centre accounting, branch consolidation, receivables ageing, payables ageing, overdue reminders, and financial close checklist.
- Chart of accounts mapping, accounting export, bank feed where supported, payment gateway settlement reconciliation, gateway fee mapping, tax configuration, and audit trail.
- E-invoice readiness, document validation, rejection handling, correction/cancellation process, and evidence retention according to applicable official requirements.
- Financial permissions, transaction limits, dual control, segregation of duties, refund thresholds, discount thresholds, and suspicious-activity alerts.
- The system must not represent management estimates as audited accounts, tax advice, or official filings without the required professional and regulatory review.

### 171.8 People, HR, and workforce
- Recruitment requests, job descriptions, candidate pipeline, interviews, offers, onboarding, employee records, emergency contacts, contracts, and document expiry reminders.
- Attendance, shifts, roster planning, leave, overtime, claims, expenses, timesheets, payroll input, payslip-provider integration, commissions, incentives, and statutory contribution workflows through qualified providers.
- Performance objectives, reviews, training, certifications, competency matrix, probation, disciplinary workflow, grievances, offboarding, asset return, and access revocation.
- Workforce planning, labour cost, absence trends, overtime warnings, coverage gaps, and staffing forecasts.
- Sensitive HR data requires purpose limitation, restricted access, retention controls, and jurisdiction-specific compliance review.

### 171.9 Marketing, content, and advertising
- Brand kit, tone of voice, audience personas, content calendar, campaign brief, creative asset library, copy variants, translation, image/video briefing, and approval workflow.
- Social post scheduling where the platform API permits; comment/inbox triage; customer review response drafts; UGC permission tracking; and brand safety review.
- Email, SMS, WhatsApp templates, push notifications, audience segments, opt-in/opt-out, suppression lists, quiet hours, frequency caps, and campaign history.
- Google Ads, Meta Ads, TikTok Ads and other channel connectors where authorized APIs and account permissions support the required actions.
- Budget plans, campaign status, spend monitoring, pacing alerts, conversion tracking, experiment plans, attribution caveats, and performance summaries.
- AI may propose campaigns and draft content; publishing, budget changes, audience export, and customer messaging must follow permissions, consent, approval thresholds, and provider policies.

### 171.10 Customer support and service quality
- Omnichannel inbox, ticket queues, categories, priority, assignment, SLA, canned replies, macros, escalation, internal notes, and resolution codes.
- FAQ and knowledge retrieval, AI answer drafting, intent classification, sentiment cues, language detection, conversation summary, and handover to a human.
- Complaint, refund request, service recovery, incident, product defect, warranty, returns, and root-cause tracking.
- QA scorecards, response time, resolution time, reopen rate, CSAT, NPS where appropriate, deflection rate, and unresolved-case monitoring.
- High-risk complaints, legal threats, safety issues, medical questions, financial disputes, and uncertain cases should route to a qualified human rather than be autonomously decided by AI.

### 171.11 Analytics, reporting, and intelligence
- KPI dictionary, metric owner, formula, time window, dimensions, exclusions, data lineage, refresh time, and confidence/quality flags.
- Sales, revenue, gross margin, cash flow, customer acquisition, retention, campaign ROAS, inventory, operations, service quality, workforce, project, and branch performance dashboards.
- Daily/weekly/monthly reports, anomaly detection, trend summaries, variance explanations, forecast scenarios, and natural-language questions over authorized data.
- Export permissions, masking, aggregation thresholds, report schedules, recipient verification, and disclosure controls.
- Every material AI-generated insight should identify the data period and sources, distinguish observed values from inference, and disclose material gaps.

### 171.12 Platform administration and support
- Platform control plane, tenant directory, subscription plans, feature flags, usage metering, billing, quotas, entitlements, trial management, support access, and customer health.
- Incident management, status page, audit logs, metrics, traces, error budgets, alert routing, backups, restore tests, disaster recovery, and release management.
- Connector health, webhook deliveries, retry queues, dead-letter queues, rate-limit handling, sync checkpoints, data reconciliation, and secret rotation.
- Feature request intake, feedback, bug tracking, release notes, knowledge base, support SLA, and customer communication.

## 172. Sector and Sub-Sector Catalogue

Use sector packs as configurable templates, not hard-coded assumptions. Each pack should define service/product models, locations, roles, workflows, documents, KPIs, permissions, compliance review points, and integration candidates.

1. **Food and beverage:** restaurant, cafe, food truck, catering, bakery, cloud kitchen, food manufacturing, franchise, barista service, canteen, meal prep.
2. **Retail and wholesale:** minimart, grocery, convenience store, fashion, footwear, cosmetics, electronics, furniture, hardware, building materials, pharmacy retail subject to applicable rules, wholesale distributor, franchise outlet.
3. **E-commerce and social commerce:** own website, marketplace seller, live commerce, dropshipping, subscription box, digital goods, print-on-demand, cross-border commerce.
4. **Events:** wedding planner, corporate event, exhibition, conference, concert, festival, birthday, community event, venue operator, ticketing, equipment rental, event crew, event media.
5. **Construction and built environment:** main contractor, subcontractor, renovation, civil works, M&E, electrical, plumbing, roofing, painting, scaffolding, quantity surveying, architecture, engineering, site supervision, facilities management.
6. **Home and field services:** cleaning, pest control, air-conditioning, appliance repair, handyman, moving, delivery, landscaping, gardening, pool maintenance, security installation, smart-home installation.
7. **Personal care and wellness:** barber, hairstylist, salon, makeup artist, nail technician, facial/skincare, spa, massage, wellness, mobile beauty, fitness coach; regulated activities require relevant checks.
8. **Healthcare and care services:** clinic, dental clinic, physiotherapy, optometry, diagnostics, pharmacy, home care, elder care, mental wellness, veterinary practice. Each requires specific privacy, licensing, and professional review.
9. **Professional services:** accounting, bookkeeping, tax practice, audit, legal practice, consulting, marketing agency, design studio, software house, IT support, translation, recruitment, training, business secretarial services.
10. **Transport and logistics:** courier, last-mile delivery, freight forwarding, haulage, fleet operator, warehouse, fulfilment centre, moving company, passenger transport, vehicle rental.
11. **Travel and hospitality:** hotel, homestay, resort, tour operator, travel agency, activity operator, tour guide, transport transfer, destination management, campsite.
12. **Property:** estate agency, property management, rental management, co-living, building management, maintenance, cleaning, inspection, facility booking.
13. **Education:** tuition centre, school, childcare, enrichment, language centre, vocational training, online course, corporate training, tutoring, learning management.
14. **Automotive:** workshop, tyre shop, detailing, car wash, body repair, spare parts, fleet maintenance, motorcycle service, inspection, vehicle rental.
15. **Manufacturing:** food, garments, furniture, metalwork, electronics, packaging, plastics, cosmetics, contract manufacturing, quality assurance, maintenance.
16. **Agriculture and fisheries:** farm, nursery, plantation, livestock, aquaculture, fisheries, farm supplies, produce collection, cold chain, agri-services.
17. **Finance and insurance distribution:** bookkeeping and non-regulated support workflows; regulated financial advice, lending, insurance underwriting, payments, and investment activities require explicit licensing and provider controls.
18. **Media and creative:** photography, videography, studio, printing, signage, advertising production, influencer management, music/event production, content studio.
19. **NGO, association, and community:** membership, donations, grants, volunteers, fundraising, beneficiaries, programmes, events, and governance.
20. **Government and public-sector suppliers:** procurement, tender tracking, service requests, contractors, records, reporting, and formal procurement controls; public-sector use requires procurement and security review.
21. **Energy and utilities:** solar installer, electrical contractor, energy audit, equipment maintenance, EV charging operator, utility subcontractor; technical and safety standards must be sector-reviewed.
22. **Telecommunications and IT:** managed service provider, ISP reseller, device repair, network installer, cybersecurity service provider, cloud consultancy, help desk.
23. **Pet services:** grooming, boarding, daycare, training, veterinary clinic, pet transport, pet supplies.
24. **Sports and recreation:** gym, studio, sports club, coach, facility rental, classes, camps, tournament organiser, equipment rental.
25. **Beauty and lifestyle commerce:** cosmetics brand, skincare retailer, personal stylist, tailor, laundry, dry cleaning, shoe repair.
26. **Repair and maintenance:** electronics, computers, phones, industrial equipment, office equipment, machinery, furniture, bicycles, household goods.
27. **Subscription and membership businesses:** coworking, clubs, recurring delivery, SaaS, service retainers, memberships, recurring classes.
28. **Rental and asset hire:** tools, event equipment, vehicles, furniture, AV equipment, machinery, uniforms, sports equipment.
29. **Security and compliance services:** guard service, alarm/CCTV installer, safety training, inspection, certification coordination; licensed activities require verification.
30. **Other and hybrid sectors:** configurable templates and custom workflow builder, with explicit review before enabling high-risk functions.

## 173. Organization Roles and Job Catalogue

Role names are starter templates. A business can rename, combine, split, or create roles while preserving permission controls and separation-of-duties rules.

### 173.1 Governance and executive
- Business owner, co-owner, director, board member, group CEO, managing director, general manager, country manager, regional manager, branch manager.
- Business administrator, executive assistant, company secretary liaison, compliance lead, risk manager, internal auditor, data protection lead, security administrator.

### 173.2 Department heads and managers
- Operations manager, finance manager, accounting manager, HR manager, sales manager, marketing manager, customer service manager, procurement manager, inventory manager, warehouse manager, logistics manager, IT manager, product manager, project manager, quality manager, facilities manager.

### 173.3 Frontline and specialist staff
- Admin executive, receptionist, cashier, sales representative, account executive, customer service agent, support specialist, content creator, designer, media editor, performance marketer, CRM executive, bookkeeper, accountant, HR executive, recruiter, buyer, storekeeper, picker/packer, dispatcher, driver, technician, installer, cleaner, field worker, consultant, trainer, analyst, developer, QA tester.

### 173.4 Supervisory roles
- Shift supervisor, team lead, floor supervisor, site supervisor, service supervisor, dispatch supervisor, warehouse supervisor, contact-centre supervisor, project supervisor, quality-control supervisor.

### 173.5 Sector-specific role examples
- F&B: outlet manager, kitchen manager, head chef, chef, cook, prep staff, barista, service crew, cashier, steward, catering coordinator, food safety lead.
- Retail: store manager, assistant manager, merchandiser, cashier, sales associate, stock controller, inventory auditor, fulfilment staff, marketplace operator.
- Events: event director, planner, producer, venue coordinator, vendor coordinator, stage manager, crew lead, ticketing staff, registration staff, AV technician, event safety lead.
- Construction: project director, project manager, contract manager, site manager, site supervisor, safety officer, engineer, quantity surveyor, storekeeper, subcontractor manager, document controller.
- Clinic: clinic manager, licensed practitioner, nurse, medical assistant, receptionist, claims/billing staff, records custodian; clinical decisions and access must follow professional and legal rules.
- Salon/wellness: salon manager, senior stylist, stylist, barber, therapist, beautician, nail technician, receptionist, mobile-service coordinator.
- Logistics: fleet manager, dispatcher, warehouse lead, driver, rider, picker/packer, customs/documentation coordinator, proof-of-delivery reviewer.
- Education: principal, academic manager, teacher, trainer, counsellor, admissions officer, registrar, parent-support officer.
- Hospitality: hotel manager, front office, housekeeping lead, concierge, maintenance technician, reservations officer, guest relations.
- Manufacturing: plant manager, production planner, shift lead, operator, maintenance technician, QA inspector, HSE officer, warehouse and procurement staff.

### 173.6 External and limited-access identities
- Customer, guest, patient/client where applicable, parent/guardian, vendor, supplier, contractor, subcontractor, freelancer, temporary worker, auditor, accountant, consultant, franchisee, delivery partner, integration service account.
- External identities must be scoped to specific records, sites, projects, tickets, or transactions and must not inherit internal employee access by default.

## 174. AI Agent and Assistant Catalogue

Each agent must have a defined purpose, approved tools, permitted data scopes, risk level, budget, audit trail, human handoff, evaluation set, and shutdown control.

1. Business Discovery Agent — interview the owner, map processes, identify missing facts, and draft the business blueprint.
2. Organization Designer Agent — suggest departments, roles, reporting lines, RACI, and approval chains for human confirmation.
3. Access Governance Agent — explain effective access, detect excessive privileges, and propose least-privilege changes; it must not silently grant itself access.
4. Executive Briefing Agent — summarize KPIs, decisions, risks, overdue actions, and exceptions with source links.
5. Operations Coordinator Agent — create and route tasks, reminders, checklists, and escalations within configured authority.
6. Customer Service Agent — answer from approved knowledge, retrieve authorized order status, draft replies, and escalate uncertain or sensitive cases.
7. Sales Assistant Agent — qualify leads, summarize conversations, recommend next actions, and draft follow-ups subject to consent rules.
8. CRM Hygiene Agent — detect duplicates, missing fields, stale leads, and inconsistent records; changes should be reviewable and reversible.
9. Marketing Strategist Agent — suggest positioning, audience hypotheses, campaign plans, and content calendars.
10. Content Studio Agent — draft copy, creative briefs, translations, product descriptions, captions, emails, and landing-page content.
11. Advertising Analyst Agent — explain performance and anomalies; campaign edits or spend changes require explicit scope and approval thresholds.
12. E-commerce Merchandising Agent — recommend product bundles, catalogue improvements, stock-aware promotions, and conversion experiments.
13. Booking and Scheduling Agent — propose available slots and schedules based on verified availability and role permissions.
14. Procurement Agent — compare supplier quotations, identify policy exceptions, and draft purchase requests.
15. Inventory Analyst Agent — flag stock discrepancies, expiry risk, slow-moving items, and reorder suggestions.
16. Finance Assistant Agent — categorize and summarize transactions, flag anomalies, reconcile candidate matches, and prepare draft reports; no unapproved movement of funds.
17. Invoice and E-Invoice Assistant — validate required fields, prepare drafts, report errors, and route submission/rejection cases to authorized personnel or providers.
18. HR Assistant Agent — answer policy questions and help with workflow administration; restricted employee records and employment decisions need human governance.
19. Recruitment Coordinator Agent — organize interview logistics and summarize job-related evidence; avoid automated discriminatory decisions.
20. Project Manager Agent — summarize milestones, blockers, risks, change requests, and overdue actions.
21. Field Service Coordinator Agent — assign work suggestions, track job status, and flag missing completion evidence.
22. Document Intelligence Agent — classify files, extract metadata, summarize documents, and route them to authorized repositories.
23. Knowledge Curator Agent — detect outdated SOPs, duplicate knowledge, unanswered questions, and review deadlines.
24. Data Analyst Agent — answer metric questions using defined formulas and authorized datasets.
25. Forecasting Agent — produce scenario ranges and assumptions for demand, staffing, inventory, and cash flow; forecasts must be labelled estimates.
26. Compliance Checklist Agent — map processes to review checklists and flag missing evidence without claiming to certify legal compliance.
27. Security Monitoring Assistant — summarize access anomalies, suspicious events, and configuration risks for security staff.
28. Integration Support Agent — diagnose connector failures, expired credentials, sync conflicts, and webhook delays without exposing secrets.
29. Training Coach Agent — create role-based onboarding and knowledge checks from approved SOPs.
30. Meeting and Action Agent — summarize approved meeting notes, extract decisions, assign actions, and track due dates.
31. Reputation and Review Agent — classify feedback and draft responses while escalating safety, legal, or sensitive allegations.
32. Loyalty and Retention Agent — identify customer segments and propose consent-compliant retention offers.
33. Franchise Operations Agent — compare outlet performance and checklist completion under authorized group-level access.
34. Sector Specialist Agents — role-specific agents for F&B, events, contractors, retail, clinics, salons, logistics, education, hospitality, and other validated sector packs.
35. Agent Supervisor/Orchestrator — plan and coordinate multi-agent workflows, enforce policy gates, budgets, tool scopes, approval checkpoints, and stop conditions.

## 175. Automation Trigger and Workflow Catalogue

### 175.1 Common triggers
- Record created, updated, approved, rejected, assigned, completed, overdue, cancelled, or deleted/archived.
- Payment succeeded, failed, refunded, disputed, reconciled, or settlement delayed.
- Invoice due, overdue, rejected, or missing required fields.
- Stock below threshold, batch nearing expiry, stocktake variance, supplier delivery late.
- Booking created, rescheduled, no-show, staff unavailable, service completed, rating received.
- Lead received, no response within SLA, quote viewed/expired, opportunity stage changed, contract renewal approaching.
- Campaign approved, scheduled, rejected by channel, spend pacing abnormal, conversion tracking broken.
- Staff joined, role changed, leave approved, contract expiring, offboarding initiated.
- Connector token expiring, webhook failed, sync lag exceeds threshold, rate limit reached, duplicate detected.
- Security anomaly, unusual export, repeated login failure, permission denied spike, backup failed, incident declared.

### 175.2 Workflow actions
- Create/update task, assign owner, request approval, notify a role, schedule reminder, escalate, create case, attach evidence, generate draft document, update status, invoke connector, enqueue sync, reconcile records, pause automation, request human review, or initiate rollback.
- Every workflow should define trigger conditions, idempotency key, preconditions, permissions, timeout, retry policy, compensation action, notification, logging, and manual recovery path.
- Irreversible or high-impact actions require explicit approval, confirmation, or a defined transaction policy. AI-generated plans must not bypass workflow authorization.

## 176. Integration Ecosystem — Candidate Catalogue

Integration availability and API scope must be verified against current official documentation, plan entitlement, region, account type, and partner approval. Prioritize based on target customers rather than treating every candidate as a launch requirement.

### 176.1 Malaysian payments and financial services
- Payment gateways/providers to evaluate: Billplz, senangPay, ToyyibPay, iPay88, eGHL, Stripe, PayPal, and bank/payment APIs where commercially available.
- Banking and reconciliation: supported bank statement imports, bank feeds where authorized, FPX settlement files, card settlement reports, DuitNow-related providers, and accounting reconciliation partners.
- Accounting/bookkeeping: Bukku, AutoCount, SQL Account, Xero, QuickBooks, ABSS and other systems demanded by target customers.
- Malaysia tax/e-invoice: LHDN MyInvois API and eligible e-invoice service providers; confirm current timelines, exemptions, validation rules, and official guidance before implementation.
- Payroll/HR: payroll providers, attendance devices, statutory contribution workflows and HR systems selected after current API and compliance review.

### 176.2 Commerce, POS, and marketplaces
- Online stores: Shopify, WooCommerce, EasyStore, Magento/Adobe Commerce, and custom storefront APIs.
- POS: StoreHub, Qashier, Slurp, and other POS vendors with verified API/partner access.
- Marketplaces/social commerce: Shopee, Lazada, TikTok Shop, and Meta commerce capabilities subject to seller access, region, and API permissions.
- Catalogue, inventory, orders, refunds, shipping labels, fulfilment status, price updates, and settlement data should be mapped through a common connector model.

### 176.3 Shipping and logistics
- EasyParcel and supported courier providers; evaluate direct integrations only when a business case exists.
- Candidate carrier families: Pos Malaysia, J&T Express, Ninja Van, DHL, FedEx, UPS, City-Link, Flash Express, and other regionally available carriers.
- Standard capabilities to test: rate quote, shipment creation, label, pickup booking, tracking, cancellation, proof of delivery, claims, COD reconciliation, webhook support, and service-area validation.

### 176.4 Communication and productivity
- WhatsApp Business Platform, email providers, SMS gateways, push notifications, voice/contact-centre services, and approved social messaging channels.
- Google Workspace, Microsoft 365, Google Calendar, Outlook Calendar, Drive/SharePoint, Docs/Word, Sheets/Excel, Teams, and Slack where authorized APIs are available.
- Video meetings, scheduling, e-signature, file storage, forms, and document workflow providers.
- Respect template approvals, opt-in requirements, rate limits, data residency terms, and provider messaging policies.

### 176.5 Marketing and analytics
- Google Ads, Meta Ads, TikTok Ads, Google Analytics, Google Tag Manager, Search Console, Meta Pixel/Conversions API, TikTok pixel/events, and supported analytics platforms.
- Email marketing/CRM candidates: HubSpot, Zoho, Mailchimp, Brevo, Klaviyo and similar providers based on customer demand and current API scopes.
- Social publishing and listening candidates: only services with authorized APIs and a compliant workflow; do not promise capabilities that a platform does not expose.
- Track source, consent, UTM conventions, event schema, conversion definition, attribution window, deduplication, and reporting caveats.

### 176.6 Maps, field operations, and scheduling
- Google Maps Platform, Mapbox, geocoding, route planning, distance matrix, service-area polygons, address validation, geofencing, and technician dispatch tools.
- Calendar and booking systems; workforce scheduling; appointment reminders; route optimization; fleet GPS providers subject to consent and access controls.
- Map/routing APIs can incur cost and usage limits; enforce quotas, caching terms, and fallback handling.

### 176.7 Cloud, development, and automation
- Cloud hosting, object storage, managed databases, logging, monitoring, error tracking, backup, secrets management, CI/CD, source control, and vulnerability scanning providers.
- n8n, Make, Zapier, and similar workflow platforms as optional orchestration integrations; keep authorization and audit in AI BIJAN rather than relying solely on third-party flow settings.
- Webhooks, REST APIs, OAuth 2.0, API keys, signed callbacks, CSV/SFTP imports, event buses, and database connectors where safe and contractually permitted.

### 176.8 AI model and AI service providers
- Pluggable model gateway for approved language, vision, speech-to-text, text-to-speech, embeddings, reranking, OCR, and document extraction providers.
- Support model routing by task, cost, latency, quality, region, privacy, and availability; keep provider-specific adapters behind a common interface.
- Evaluate providers for retention, training use, data processing terms, regional availability, tool calling, structured outputs, rate limits, and model lifecycle changes.
- AI model vendors are not the system of record for business data, authorization, audit logs, or financial transactions.

## 177. Common Data Objects and System-of-Record Map

Minimum cross-module objects to consider:
- Tenant, legal entity, business, branch, location, department, team, cost centre, project, service area.
- User, identity, membership, role, permission, scope, delegation, invitation, session, service account.
- Customer, contact, supplier, vendor, employee, contractor, partner, consent record, communication preference.
- Product, service, variant, bundle, price list, tax rule, stock item, inventory balance, stock movement, supplier item.
- Lead, opportunity, quote, contract, order, booking, appointment, job, project, task, case, ticket, shipment, delivery proof.
- Invoice, credit note, payment intent, payment, refund, settlement, expense, purchase request, purchase order, bill, reconciliation record.
- Campaign, content asset, audience, consent event, channel account, ad account, campaign result, attribution event.
- Document, attachment, knowledge article, SOP, approval request, decision, audit event, workflow definition, workflow run.
- Integration connection, credential reference, sync job, webhook event, mapping rule, error record, dead-letter item.
- AI agent, agent run, tool invocation, model route, prompt/version reference, evaluation result, approval checkpoint, budget ledger.
- KPI definition, metric snapshot, report, forecast, alert, incident, feature flag, subscription, usage record, invoice for platform billing.

For each object define: unique ID, tenant boundary, owner, data classification, lifecycle state, timestamps, deletion/retention rule, source system, sync strategy, access policy, audit events, and export policy.

## 178. Notification and Communication Catalogue
- In-app notifications, email, SMS, WhatsApp templates, push, voice calls through approved providers, webhook callbacks, and internal escalation queues.
- Transactional: sign-in/security, invitation, booking/order, payment, invoice, delivery, case, approval, task due, password/device changes.
- Operational: SLA breach, low stock, shift gap, project delay, supplier delay, sync failure, integration credential expiry, data-quality issue.
- Commercial: lead follow-up, abandoned checkout where lawful, renewal, loyalty, promotion, review request, win-back, and subscription billing.
- Governance: access grant/revoke, privilege escalation, export request, policy change, suspicious activity, approval completion, retention/deletion workflow.
- Controls: recipient verification, consent, template approval, localization, frequency caps, quiet hours, delivery status, retry, bounce/suppression handling, and audit.

## 179. Reporting and KPI Catalogue

Use an explicit metric dictionary; names alone are insufficient. Each KPI must include formula, owner, refresh interval, source, inclusion/exclusion rules, timezone, currency, and known limitations.

- Executive: revenue, gross margin, operating costs, cash runway/cash position, budget variance, forecast accuracy, open risks, overdue approvals.
- Sales: lead response time, lead-to-opportunity conversion, win rate, average order value, sales cycle, pipeline coverage, repeat purchase, lost reasons.
- Marketing: spend, impressions, reach, clicks, CTR, CPC, CPM, conversions, CPA, ROAS, attributed revenue, frequency, unsubscribe/complaint rate.
- Customer service: first response time, time to resolution, SLA attainment, reopen rate, backlog age, CSAT, complaint themes, escalation rate.
- Commerce: conversion rate, cart abandonment, fulfilment time, cancellation rate, refund rate, return rate, stock availability, contribution margin.
- Finance: receivables ageing, payables ageing, cash collection days, reconciliation exceptions, overdue invoices, payment failure rate, settlement variance.
- Inventory/procurement: stock turnover, days of inventory, stockout rate, expiry loss, shrinkage, supplier on-time delivery, purchase price variance.
- Workforce: attendance exceptions, overtime, labour cost ratio, training completion, vacancies, schedule coverage, turnover where validly measured.
- Project/construction: milestone variance, cost variance, change requests, safety observations, defect closure, progress claim status, subcontractor delivery.
- F&B: sales by outlet/shift, average ticket, table turnover where tracked, food cost, wastage, order prep time, delivery complaints.
- Events: budget variance, registration conversion, attendance rate, vendor readiness, schedule adherence, incident count, post-event satisfaction.
- Field services: travel time, first-time fix rate, job completion time, revisit rate, technician utilization, evidence completeness.
- Platform: uptime, latency, error rate, queue lag, connector success, sync freshness, AI cost per task, approval wait time, failed workflow recovery.

## 180. Security, Privacy, and AI Governance Catalogue
- Tenant isolation tests, authentication controls, MFA for privileged users, secure session handling, CSRF/XSS/SQL injection protection, encryption in transit and at rest, key management, secrets vault, and secure backups.
- Permission tests at API, UI, export, search, reporting, file, webhook, AI retrieval, and background-job layers.
- Audit events for access grants, sensitive reads where required, exports, financial actions, permission changes, AI tool calls, approvals, connector changes, and administrative impersonation/support access.
- Data classification, purpose limitation, minimization, consent records, retention schedules, deletion workflows, data export, correction requests, breach response, and vendor/subprocessor inventory.
- Prompt injection and untrusted-document defenses, retrieval scope enforcement, tool allowlists, output validation, rate limits, per-tenant budgets, and safe handling of secrets.
- Human approval for sensitive/high-impact actions; two-person control for configured actions; kill switch by tenant, agent, connector, workflow, and global platform.
- AI evaluation for accuracy, groundedness, refusal behavior, access-control bypass, prompt injection, bias, language performance, hallucination, cost, and latency.
- Incident response, threat modelling, penetration testing, dependency scanning, patching, disaster recovery exercises, and security evidence retention.
- Malaysia PDPA and other applicable laws/sector rules must be reviewed against current official guidance and the actual processing purpose; this catalogue is not legal certification.

## 181. Import, Migration, and Data Quality Catalogue
- CSV/XLSX import templates, API imports, legacy-system extraction, field mapping, type validation, mandatory-field rules, duplicate detection, reference mapping, preview, dry run, rollback, and import report.
- Migration objects: customers, suppliers, staff, roles, catalogue, stock, price lists, opening balances, open invoices, active orders, documents, consent records, and historical reporting data as appropriate.
- Data-quality rules: completeness, validity, uniqueness, consistency, referential integrity, freshness, reconciliation, ownership, and source-of-truth conflicts.
- Data lineage, transformation version, imported-by, import timestamp, source file hash, rejected row reason, and approved correction history.
- Never silently merge customer identities, financial records, health records, or legal records solely on a probabilistic match; route ambiguous matches for review.

## 182. Reliability, Performance, and Operational Readiness
- Define availability and response-time objectives by tier; monitor p50/p95/p99 latency, error rate, queue age, database load, cache hit rate, and external API failure rates.
- Mobile/low-bandwidth design, pagination, server-side filtering, request deduplication, caching where safe, lazy loading, image optimization, and offline-tolerant drafts where appropriate.
- Queue-based processing for slow integrations, idempotent handlers, retry with backoff, rate-limit awareness, circuit breakers, timeout budgets, dead-letter queues, and manual replay.
- Backups with tested restore; recovery point objective (RPO) and recovery time objective (RTO); regional resilience appropriate to the chosen architecture and contractual requirements.
- Feature flags, staged rollout, migrations with rollback plans, automated tests, smoke tests, regression tests, load tests, accessibility checks, and production release gates.
- Status page, incident severity, on-call ownership, customer communication templates, post-incident review, corrective actions, and evidence of closure.

## 183. Product Tier and Entitlement Catalogue

Possible packaging dimensions (commercial values to be validated, not fixed recommendations):
- Number of businesses/legal entities, branches, users, custom roles, external collaborators, and monthly active users.
- AI usage credits, model classes, agent runs, context/file limits, document OCR, generated media, and workflow executions.
- Connector count, sync frequency, API rate limits, webhook volume, data retention, storage, audit retention, and export capacity.
- Advanced features: SSO/SCIM, custom approval policies, advanced analytics, multi-entity consolidation, sandbox, custom domain, branded portal, SLA, support response, and dedicated environment.
- Governance: approval requirements, AI autonomy tiers, permitted action classes, spend caps, and audit/export access.
- Entitlements must be enforced server-side and included in plan-change, downgrade, overage, cancellation, and data-export workflows.

## 184. Delivery Status and Prioritization Register

Every catalogue item should carry:
- ID and name.
- Sector(s) and user persona(s).
- Customer problem and measurable outcome.
- Priority: P0 foundation, P1 launch-critical, P2 growth, P3 later/experimental.
- Status: discovered, specified, designed, in development, internal test, pilot, beta, production, deprecated.
- Product owner and technical owner.
- Dependencies, security classification, compliance review, and integration prerequisites.
- Acceptance tests, monitoring, rollback plan, support documentation, and release date.
- Customer demand evidence, expected value, implementation effort, operating cost, and confidence level.

Suggested scoring: customer impact + strategic fit + demand evidence + risk reduction, balanced against engineering effort, operating cost, dependencies, and compliance burden. Do not prioritize by catalogue length.

## 185. Additional Completion Checklist
- [ ] Every module has clear ownership and a source-of-truth definition.
- [ ] Every role template has a tested permission matrix and data scope.
- [ ] Every sector pack has a real end-to-end workflow and exception path.
- [ ] Every integration has documented API scope, credentials handling, limits, retries, sync direction, reconciliation, and support owner.
- [ ] Every AI agent has an evaluation set, action boundary, budget, approval policy, audit record, and kill switch.
- [ ] Every automated message respects recipient, consent, channel, timing, and suppression controls.
- [ ] Every KPI has a versioned formula and documented data source.
- [ ] Every migration has preview, validation, reconciliation, and rollback or compensating procedure.
- [ ] Every production release has security checks, regression tests, monitoring, recovery plan, and support readiness.
- [ ] Planned features are clearly distinguished from implemented and tested features in product UI, sales material, and documentation.

**End of v4.4 catalogue expansion.** Treat this as a living product inventory. Validate each item against customer interviews, current official API documentation, legal advice where required, technical feasibility, and a staged delivery plan.
---

# 186. B-AI Workspace — Integrasi Custom AI Agent, Builder, Automation dan Coding Runtime

**Status:** Cadangan seni bina untuk pembangunan dan pengesahan teknikal. Kewujudan dalam blueprint tidak bermaksud integrasi telah dibina, lesen telah diluluskan atau sistem telah diuji di production.

## 186.1 Objektif dan prinsip seni bina

AI BIJAN berasaskan Laravel boleh menyatukan beberapa projek pihak ketiga dalam satu pengalaman produk. Sistem-sistem ini hendaklah diintegrasikan sebagai servis atau runtime khusus, bukannya dicantumkan ke dalam satu proses Laravel atau satu aplikasi monolitik yang tidak mempunyai sempadan jelas.

Prinsip utama:

- **Laravel ialah control plane dan sumber kebenaran utama** untuk identiti, organisasi, tenant, keahlian, peranan, langganan, entitlement, billing, AI Token Wallet, polisi dan audit.
- **Setiap integrasi mempunyai adapter/connector tersendiri** dengan kontrak API, health check, timeout, retry, pengendalian ralat dan versi yang didokumenkan.
- **Tenant isolation wajib merentas aplikasi dan infrastruktur**, bukan sekadar menambah `tenant_id` pada jadual Laravel.
- **API-first:** dashboard web, PWA, aplikasi mudah alih, Admin HQ dan AI agent mengakses fungsi melalui API AI BIJAN yang disahkan.
- **Fail-closed:** jika identiti tenant, kebenaran atau polisi tidak dapat disahkan, permintaan ditolak.
- **Feature flags dan entitlement** menentukan integrasi yang boleh digunakan oleh setiap pelan, tenant dan pengguna.
- **Lesen, hak embedding/white-label, penggunaan komersial dan kos hosting** mesti disahkan sebelum ciri dipasarkan.

## 186.2 Katalog sistem dan tanggungjawab

| Sistem | Peranan dalam AI BIJAN | Kaedah integrasi yang dicadangkan | Perkara yang mesti disahkan |
|---|---|---|---|
| **Hermes Agent** | Runtime ejen AI peribadi; profil, kemahiran, sesi dan memori ejen | Adapter Laravel ke runtime/API yang disokong; profil dan konfigurasi berskop tenant | Kaedah deployment, sesi serentak, storan memori, credential, sandbox dan lesen semasa |
| **Flowise** | Visual builder untuk chatbot, aliran AI, RAG dan agent workflow | API atau embedding rasmi yang dibenarkan; setiap flow dimiliki tenant dan dikawal melalui entitlement | Keupayaan multi-tenant edisi terpilih, autentikasi, embedding, lesen dan had deployment |
| **Activepieces** | Builder automasi aplikasi dan workflow | API/embedding yang disokong; credential, flow dan run diikat kepada tenant | Pelan/lesen embedding, hak white-label, pengasingan credential dan had concurrency |
| **Exodus AI** | Belum ditetapkan sehingga projek dikenal pasti | Jangan bina adapter production sebelum repositori dan produk yang tepat disahkan | URL rasmi/repo, pemilik projek, fungsi, lesen, API, aktiviti penyelenggaraan dan risiko keselamatan |
| **n8n** | Automasi lanjutan, webhook, integrasi API dan workflow operasi | Sambungan API atau deployment khusus dengan kawalan akses dan rekod penggunaan | Lesen penggunaan komersial/SaaS, syarat embedding/white-label, model pengguna dan deployment yang dibenarkan |
| **OpenCode** | AI coding workspace dan agent coding untuk projek yang dibenarkan | Backend session broker; workspace serta proses coding dalam sandbox/container yang diasingkan | API/server auth, pelaksanaan arahan, akses fail/Git, concurrency, sandbox, network egress dan lesen |
| **OmniRoute** | Calon gateway/routing model AI berbilang penyedia | Adapter di belakang B-AI Gateway; jangan benarkan modul memintas rekod penggunaan dan polisi | Repo tepat, format API, pengurusan key, routing/fallback, telemetry, lesen dan status penyelenggaraan |
| **9Router** | Calon alternatif atau pelengkap routing model/API | Uji sebagai pilihan berasingan; elakkan dua router bertindih tanpa keperluan yang nyata | Repo tepat, API compatibility, sumber credential, logging, fallback, lesen dan kos operasi |

**Nota pengesahan:** Nama “Exodus AI”, “OmniRoute” dan “9Router” boleh merujuk kepada projek berlainan. URL atau repositori yang tepat perlu direkod dalam daftar integrasi sebelum reka bentuk terperinci diluluskan. Jangan menganggap ciri, lesen atau keserasian API berdasarkan nama semata-mata.

## 186.3 Seni bina logik

```text
AI BIJAN Web / PWA / Mobile / Admin HQ
                  |
          Laravel Core API
 Identity | Tenant | RBAC/ABAC | Subscription | Entitlements
 Billing | AI Wallet | Audit | Policy | Integration Registry
                  |
            B-AI Control Gateway
 Auth context | Tenant scope | Quota | Rate limit | Policy
 Routing | Usage metering | Idempotency | Trace/correlation ID
                  |
     +------------+--------------------+
     |            |                    |
 Agent Runtime  AI/Automation       Model Gateway
 Hermes         Flowise             OmniRoute OR 9Router
 OpenCode       Activepieces        approved providers
                n8n                 and model APIs
     |
 Per-tenant runtime/workspace, secrets, memory, data and logs
```

Rajah ini menunjukkan sempadan tanggungjawab, bukan arahan bahawa semua sistem mesti dipasang pada satu pelayan. Setiap servis boleh ditempatkan pada host, container atau cluster berasingan mengikut tahap risiko dan beban.

## 186.4 Tenant isolation — kawalan wajib

Setiap request yang memasuki B-AI Control Gateway mesti membawa identiti yang boleh disahkan. Tenant context tidak boleh diterima secara membuta tuli daripada input frontend; ia mesti ditentukan dan disahkan melalui sesi/token serta keahlian pengguna di Laravel.

| Domain | Keperluan pengasingan |
|---|---|
| Identiti dan organisasi | Setiap request mengesahkan `user_id`, `tenant_id`, membership, peranan dan status akaun. Sokongan multi-tenant mesti menentukan tenant aktif secara jelas. |
| Database | Semua query berskop tenant; gunakan polisi authorization di server. Pertimbangkan row-level security atau database/schema berasingan apabila tahap risiko atau keperluan kontrak memerlukannya. |
| Memori AI | Memori peribadi diasingkan mengikut pengguna; memori organisasi hanya boleh diakses ahli yang diberi kebenaran. Retrieval/RAG mesti menapis tenant sebelum mengambil kandungan, bukan selepas jawapan dijana. |
| API key dan secrets | Simpan dalam secret manager atau storan terenkripsi dengan key management yang sesuai. Jangan hantar secret kepada frontend, log, prompt atau analitik. Sokong rotation, revocation dan skop minimum. |
| Workflow dan credentials | Flow, connection, trigger, webhook secret, execution history dan hasil workflow mesti terikat kepada tenant serta ACL yang diuji. Elakkan credential global yang boleh digunakan semua tenant. |
| Coding workspace | Asingkan direktori, proses, container/VM, token Git, terminal dan output setiap tenant atau sesi mengikut model ancaman. Hadkan mount, privilege, CPU/RAM, masa dan network egress. |
| Runtime agent | Profil, sesi, tools, skills, memori, fail dan kebenaran perlu diskopkan. Tool berisiko tinggi memerlukan allowlist dan approval. |
| AI usage dan billing | Rekod setiap penggunaan dengan tenant, pengguna, integrasi, model, token/input-output jika tersedia, kos, status dan correlation ID. Elakkan caj berganda melalui idempotency dan reconciliation. |
| Logs dan tracing | Tenant-scoped access; redaksi secret dan data sensitif; tetapkan retention. Jangan log prompt penuh atau kandungan peribadi tanpa keperluan dan asas yang jelas. |
| Admin HQ / support | Akses minimum, bersebab, berjangka jika sesuai, dan diaudit. Akses pentadbir kepada kandungan pelanggan bukan automatik hanya kerana peranan Super Admin. |
| Export dan deletion | Data export, penutupan tenant dan pemadaman perlu meliputi pangkalan data, fail, vector store, cache, workflow, execution logs dan backup mengikut polisi retention. |

**Peringatan keselamatan:** `tenant_id` dalam jadual Laravel sahaja tidak mengasingkan folder, proses terminal, container, vector store atau servis luar. OpenCode dan mana-mana agent yang boleh menjalankan kod/arahan memerlukan sandbox dan ujian escape, privilege, filesystem serta network access.

## 186.5 Laravel control plane dan skema data cadangan

Nama jadual di bawah ialah cadangan logik; ia perlu diselaraskan dengan skema sedia ada dan tidak semestinya diwujudkan satu demi satu jika model yang setara sudah wujud.

- `tenants`, `tenant_memberships`, `roles`, `permissions` — identiti organisasi dan kawalan akses.
- `integration_catalog` — nama, vendor/projek, versi, status, jenis deployment, capability, lesen yang telah disemak dan pemilik teknikal.
- `tenant_integrations` — integrasi yang diaktifkan untuk tenant, konfigurasi bukan rahsia, status, environment dan runtime reference.
- `integration_entitlements` — had akses berdasarkan pelan, add-on, tenant dan peranan.
- `integration_credentials` — metadata secret reference sahaja; nilai rahsia disimpan dalam secret manager/encrypted vault, bukan sebagai teks biasa.
- `agent_profiles`, `agent_sessions`, `agent_memory_refs` — metadata profil/sesi dan rujukan kepada storan yang berskop tenant; jangan campurkan memori tenant dalam satu indeks tanpa filter keselamatan.
- `automation_projects`, `workflow_refs`, `workflow_executions` — metadata workflow dan execution reference daripada servis luar.
- `coding_workspaces`, `coding_sessions`, `runtime_instances` — pemetaan workspace/sesi kepada runtime yang terasing.
- `ai_usage_events`, `ai_wallet_ledger`, `billing_reconciliations` — penggunaan, debit/kredit dan rekonsiliasi bil.
- `integration_audit_events` — perubahan konfigurasi, akses, tindakan ejen, approval, deployment dan operasi sokongan.

Semua model perlu menggunakan foreign key atau rujukan yang konsisten, polisi authorization di server, indeks untuk carian tenant-scoped dan ujian yang cuba mengakses objek tenant lain. Jangan percaya `tenant_id` yang dihantar pengguna tanpa mengesahkan membership.

## 186.6 B-AI Gateway, usage metering dan billing

Semua panggilan model yang boleh dikawal oleh AI BIJAN hendaklah melalui B-AI Gateway. Gateway perlu:

1. Mengesahkan pengguna, tenant, entitlement dan polisi tindakan.
2. Menentukan integrasi/runtime serta model yang dibenarkan.
3. Memeriksa quota, rate limit, bajet dan baki sebelum permintaan.
4. Mengeluarkan credential dengan skop minimum tanpa mendedahkannya kepada klien.
5. Merekod usage event dengan correlation ID dan maklumat kos yang tersedia daripada provider/runtime.
6. Mengurus timeout, retry terhad, idempotency dan provider fallback tanpa menghasilkan debit berganda.
7. Menghantar event penggunaan kepada ledger dan billing reconciliation.
8. Menguatkuasakan had harian/bulanan, alert, hard-stop dan polisi overage yang ditetapkan oleh pelan.

Jika sesebuah runtime menggunakan provider API key milik pelanggan secara langsung, AI BIJAN perlu membezakan dengan jelas antara **BYOK (Bring Your Own Key)** dan penggunaan melalui **AI Token Package** AI BIJAN. Jangan menagih token yang tidak dapat diukur atau mendakwa kos tepat apabila runtime tidak membekalkan data penggunaan yang boleh dipercayai.

## 186.7 Subscription, package dan entitlement

Super Admin boleh menetapkan harga, had, add-on dan integrasi yang tersedia melalui konfigurasi pelan yang berversi. Harga yang dipaparkan kepada pelanggan perlu mempunyai sejarah perubahan dan tarikh kuat kuasa. Perubahan pelan tidak boleh secara senyap memadam data pelanggan atau mematikan workflow kritikal tanpa notis serta prosedur migrasi yang ditentukan.

Contoh matriks produk awal — bukan harga muktamad:

| Pelan contoh | Akses yang mungkin ditawarkan | Had yang perlu dikonfigurasi |
|---|---|---|
| Starter | AI chat, kuota asas dan workflow siap guna | Token/quota, bilangan workflow aktif, had run dan storan |
| Business | AI builder dan automasi perniagaan | Bilangan projek/flow, execution concurrency, sambungan aplikasi dan had penggunaan |
| Professional | Pilihan profil agent, automasi lanjutan dan coding workspace jika diluluskan | Runtime sessions, workspace, CPU/RAM, storage, egress dan bajet AI |
| Enterprise | Had dan polisi tersuai, pilihan deployment terasing, kawalan pentadbir tambahan | SLA, concurrency, retention, audit, lokasi data dan keperluan kontrak |

Setiap entitlement mesti disemak di backend untuk setiap operasi, bukan hanya menyembunyikan menu di frontend. Pisahkan harga SaaS/module daripada caj penggunaan AI, kos runtime/compute dan caj provider pihak ketiga.

## 186.8 Deployment dan operasi

- Mulakan dengan persekitaran development/staging yang boleh dibina semula melalui konfigurasi dan deployment automatik.
- Jalankan Laravel Core dan servis integrasi sebagai deployment berasingan dengan network policy yang jelas.
- Gunakan container atau sandbox yang sesuai bagi coding/agent runtime; elakkan menjalankan kod pelanggan dengan privilege aplikasi utama.
- Asingkan production secrets, log dan data daripada development/testing.
- Tetapkan health check, readiness check, resource limit, autoscaling atau concurrency cap, backup, restore test, patching dan vulnerability scanning.
- Wujudkan kill switch bagi setiap integrasi dan tenant; kegagalan satu runtime tidak sepatutnya melumpuhkan seluruh AI BIJAN.
- Simpan konfigurasi integrasi sebagai versi; perubahan yang gagal boleh di-rollback.
- Sediakan runbook untuk kegagalan provider, credential tamat tempoh, queue tertunggak, overage, incident keselamatan dan pemulihan data.

## 186.9 Daftar lesen, komersial dan pematuhan

Sebelum sesuatu integrasi diaktifkan untuk pelanggan berbayar, rekodkan:

- URL rasmi dan repositori tepat; pemilik dan status penyelenggaraan.
- Lesen bagi kod sumber dan dependency, termasuk kewajipan atribusi, notis, source disclosure atau syarat pengedaran jika berkenaan.
- Kebenaran untuk hosting berpusat, SaaS multi-tenant, embedding, white-label, resale atau akses berbayar.
- Syarat trademark/branding dan penggunaan aset.
- Data yang dihantar kepada vendor/provider, lokasi pemprosesan, retention, subprocessor dan terma privasi.
- API authentication, rate limits, penggunaan data, keselamatan webhook dan polisi credential.
- Kos lesen komersial, compute, storage, bandwidth, model API dan sokongan.
- Tarikh semakan, bukti/rujukan rasmi, pemilik semakan dan keputusan: `approved`, `conditional`, `blocked` atau `needs_review`.

Jangan menganggap projek open-source automatik membenarkan white-label, embedding berbayar atau penjualan sebagai servis terurus. Dapatkan semakan lesen dan nasihat undang-undang apabila skop komersial atau syarat lesen tidak jelas.

## 186.10 Pelan pelaksanaan berperingkat

**P0 — Asas platform**
- Sahkan model tenant, membership, RBAC/ABAC, entitlement dan audit.
- Bina B-AI Control Gateway, usage event schema, AI wallet ledger dan integration registry.
- Sediakan ujian automatik tenant isolation dan ujian akses silang tenant.

**P1 — Integrasi pertama**
- Pilih satu AI builder dan satu automation engine berdasarkan lesen, keperluan pelanggan dan kemudahan deployment.
- Tambah satu runtime agent selepas polisi memori, tools dan credential disahkan.
- Uji login, provisioning, penggunaan, billing, revocation, backup dan deletion dari hujung ke hujung.

**P2 — Coding workspace**
- Tambah OpenCode hanya selepas sandbox, had filesystem/network, secret isolation dan audit tindakan berjaya diuji.
- Sediakan approval untuk operasi berisiko seperti deployment, perubahan production, akses data pelanggan atau tindakan kewangan.

**P3 — Router dan integrasi tambahan**
- Audit OmniRoute dan 9Router secara berasingan; pilih satu sebagai laluan lalai kecuali terdapat keperluan routing berlapis yang jelas.
- Tambah Hermes, Activepieces, n8n atau sistem lain berdasarkan keputusan lesen, ujian keselamatan dan permintaan produk.
- Jangan integrasikan Exodus AI sehingga identiti projek tepat disahkan.

## 186.11 Kriteria penerimaan sebelum production

- [ ] Pengguna Tenant A tidak boleh membaca atau mengubah data, memori, flow, credential, workspace atau log Tenant B.
- [ ] Perubahan `tenant_id` secara manual, IDOR, akses melalui API terus dan webhook yang dipalsukan ditolak.
- [ ] Secret tidak muncul pada frontend, log, trace, prompt, error message atau export biasa.
- [ ] Quota, overage, retry, provider fallback dan ledger diuji tanpa caj berganda.
- [ ] Revocation pengguna/key, downgrade pelan, penutupan tenant dan pemadaman data diuji.
- [ ] Runtime coding tidak boleh membaca fail host atau tenant lain, menggunakan privilege berlebihan atau membuat network egress yang tidak dibenarkan.
- [ ] Akses Admin HQ dan tindakan sokongan mempunyai sebab, skop, tempoh yang sesuai dan audit trail.
- [ ] Lesen, hak komersial/embedding dan kos deployment telah disemak serta direkod.
- [ ] Backup/restore, pemantauan, alert, rollback dan incident response telah diuji.
- [ ] Semua ciri yang belum dibina atau belum disahkan dilabel sebagai planned/experimental, bukan production-ready.

## 186.12 Keputusan seni bina

AI BIJAN boleh menawarkan satu pintu masuk produk untuk Hermes Agent, Flowise, Activepieces, n8n, OpenCode dan router model yang diluluskan, dengan Exodus AI menunggu pengesahan identiti projek. Laravel kekal sebagai pusat kawalan; runtime khusus kekal sebagai servis yang mempunyai sempadan jelas.

Keutamaan pelaksanaan ialah **tenant isolation → control gateway → usage metering/billing → satu builder dan satu automation engine → agent runtime → coding sandbox → integrasi tambahan**. Jangan membina kesemua lapan integrasi serentak sebelum keselamatan, kos, lesen dan operasi asas terbukti.
