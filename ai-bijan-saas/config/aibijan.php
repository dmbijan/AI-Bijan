<?php

// Katalog kebenaran & peranan lalai AI Bijan. Kebenaran = "modul.tindakan".
return [
    'permissions' => [
        'tenant.settings.view' => 'Lihat tetapan syarikat',
        'tenant.settings.update' => 'Ubah tetapan syarikat',
        'members.view' => 'Lihat ahli pasukan',
        'members.invite' => 'Jemput ahli',
        'members.manage' => 'Ubah peranan / nyahaktif ahli',
        'roles.manage' => 'Urus peranan & kebenaran',
        'audit.view' => 'Lihat log audit',
        'files.upload' => 'Muat naik fail',
        'files.view' => 'Lihat / muat turun fail',
        'api_keys.manage' => 'Urus kunci API',
        'approvals.view' => 'Lihat permintaan kelulusan',
        'approvals.decide' => 'Lulus / tolak permintaan',
        'documents.view' => 'Lihat & muat turun dokumen (bukan sulit)',
        'documents.issue' => 'Keluarkan dokumen',
        'documents.sensitive' => 'Akses dokumen sulit (slip gaji, surat pekerja, payroll)',
        'documents.void' => 'Batalkan dokumen',
        'inventory.view' => 'Lihat stok, barang & laporan stok',
        'inventory.manage' => 'Urus barang, gudang & dokumen stok',
        'inventory.approve' => 'Lulus pelarasan & kiraan stok',
        'inventory.sell' => 'Tolak stok jualan (POS / kedai online)',
        'manufacturing.view' => 'Lihat BOM & work order',
        'manufacturing.manage' => 'Urus BOM & work order',
        'projects.view' => 'Lihat projek (yang disertai)',
        'projects.manage' => 'Urus semua projek & lulus jam kerja',
        'projects.timesheet' => 'Rekod jam kerja',
        'projects.approve' => 'Lulus / tolak rekod jam kerja',
        'assets.view' => 'Lihat aset',
        'assets.manage' => 'Urus aset, susut nilai & pelupusan',

        // B-POS — Point of Sale
        'pos.view' => 'Lihat transaksi POS, register & shift',
        'pos.manage' => 'Urus transaksi POS, cash shift & register',

        // B-Booking — Service Booking & Appointments
        'booking.view' => 'Lihat perkhidmatan, slot & temujanji',
        'booking.manage' => 'Urus perkhidmatan, slot & temujanji',

        // B-Marketplace — E-commerce Marketplace
        'marketplace.view' => 'Lihat produk, vendor, pesanan, kart & ulasan',
        'marketplace.manage' => 'Urus produk, vendor, pesanan, kart & ulasan',

        // B-Site — Website Builder (Shopify Replacement)
        'site.view' => 'Lihat laman web, halaman, tema, blog & domain',
        'site.manage' => 'Urus laman web, halaman, tema, blog & domain',
        'site.reports' => 'Akses analitik & laporan laman web',

        // B-Marketing — Marketing Automation (HubSpot Replacement)
        'marketing.view' => 'Lihat campaign, segment, contact, voucher, deal, sequence & workflow',
        'marketing.manage' => 'Urus campaign, segment, contact, voucher, deal, sequence & workflow',
        'marketing.approve' => 'Lulus start/pause/complete campaign & close deals',
        'marketing.reports' => 'Akses analytics, lead scoring & ad performance',

        // B-Loyalty — Loyalty Program & Rewards System
        'loyalty.view' => 'Lihat ahli, mata ganjaran, tahap & laporan',
        'loyalty.manage' => 'Urus ahli, mata, ganjaran & tebusan',
        'loyalty.approve' => 'Lulus tebusan & pelarasan mata',
        'loyalty.reports' => 'Akses analitik loyaliti, LTV & metrik churn',

        // B-Affiliate — Affiliate Marketing & Commission System
        'affiliate.view' => 'Lihat affiliate, referral code & komisen',
        'affiliate.manage' => 'Urus affiliate, referral code & payout',
        'affiliate.approve' => 'Lulus komisen & proses payout',
        'affiliate.reports' => 'Akses analitik affiliate, ROI & metrik konversi',

        // B-Link — Dynamic Links, Digital Cards & Click Tracking
        'link.view' => 'Lihat link pages, digital cards & click data',
        'link.manage' => 'Urus link pages, digital cards & tracking',
        'link.reports' => 'Akses analytics & laporan clicks',

        // B-Sign & Docs — Document Management, Templates & Electronic Signatures
        'sign_docs.view' => 'Lihat dokumen, template & tandatangan',
        'sign_docs.manage' => 'Urus dokumen, template & workflow',
        'sign_docs.sign' => 'Tandatangan dokumen secara elektronik',
        'sign_docs.reports' => 'Akses laporan dokumen & audit trail',

        // B-Omnichannel — Unified Customer Communication Platform
        'omnichannel.view' => 'Lihat conversations, messages & channels',
        'omnichannel.manage' => 'Urus conversations, agents & automation',
        'omnichannel.channels' => 'Urus communication channels (WhatsApp, Email, Social)',
        'omnichannel.reports' => 'Akses analytics & reports komunikasi',

        // B-Logistics — Warehouse, Stock Movement & Shipping
        'logistics.view' => 'Lihat warehouses, stock moves & shipments',
        'logistics.manage' => 'Urus warehouses, stock & shipping operations',
        'logistics.shipping' => 'Urus shipments, couriers & tracking',
        'logistics.reports' => 'Akses logistics analytics & reports',

        // B-SCM — Purchase Order, Supplier & Goods Received
        'scm.view' => 'Lihat pesanan belian, pembekal & terimaan barang',
        'scm.manage' => 'Urus pesanan belian, pembekal & terimaan barang',
        'scm.approve' => 'Luluskan pesanan belian melebihi had',
        'scm.reports' => 'Akses laporan perolehan & pembekal',

        // B-Vision — CCTV, Kamera & Analitik Visual
        'vision.view' => 'Lihat kamera CCTV & peristiwa',
        'vision.manage' => 'Urus kamera, retensi & tetapan rakaman',
        'vision.audit' => 'Akses log tontonan & audit kamera',

        // B-COD — Cash on Delivery
        'cod.view' => 'Lihat pesanan COD & kutipan',
        'cod.manage' => 'Urus pesanan COD, kurier & penyelesaian',

        // B-Media Studio — Aset media, persona & pemahaman video
        'media_studio.view' => 'Lihat aset media, persona & transkrip',
        'media_studio.manage' => 'Urus aset media, persona & jana video',

        // B-Content Studio — Penjanaan & penjadualan kandungan
        'content_studio.view' => 'Lihat kandungan, idea & takwim terbitan',
        'content_studio.manage' => 'Urus kandungan, idea, kelulusan & terbitan',

        // B-Workflow Studio — Automasi visual
        'workflow_studio.view' => 'Lihat aliran kerja & sejarah larian',
        'workflow_studio.manage' => 'Ubah aliran kerja & laksanakan automasi',

        // B-Analytics — Business Intelligence & Analytics Engine
        'analytics.view' => 'Lihat dashboards, reports & metrics',
        'analytics.manage' => 'Urus metrics, reports & dashboard configurations',
        'analytics.export' => 'Ekspor data analytics ke PDF, Excel, CSV',
        'analytics.reports' => 'Akses advanced analytics & insights',

        // B-Brain Hub — AI-Powered Insight, Forecast & Recommendation Engine
        'brainhub.view' => 'Lihat insights, forecasts & recommendations',
        'brainhub.insights' => 'Urus insights (mark read, dismiss, record actions)',
        'brainhub.forecasts' => 'Urus forecasts & evaluate accuracy',
        'brainhub.recommendations' => 'Urus recommendations (accept, complete, feedback)',

        // B-COGS Engine — Cost of Goods Sold Calculation & Margin Analysis
        'cogs.view' => 'Lihat product costs, COGS reports & margin analysis',
        'cogs.manage' => 'Urus cost components, calculate costs & allocate overheads',
        'cogs.reports' => 'Akses margin reports, profitability & break-even analysis',

        // Industry Packs — Pre-configured industry templates
        'industry_packs.view' => 'Lihat profil industri & konfigurasi template',
        'industry_packs.manage' => 'Ubah setingan industri tenant & apply template',

        // B-AI Agents — Multi-Agent System (BAB 16)
        'ai_agents.view' => 'Lihat senarai agents, stats & governance report',
        'ai_agents.manage' => 'Urus agent configurations, enable/disable/reset',
        'ai_agents.execute' => 'Execute agent tasks & tool calls',

        // B-Personal AI Assistant (BAB 17)
        'personal_ai.view' => 'Lihat personal assistant, preferensi & memori sendiri',
        'personal_ai.tasks' => 'Cipta & urus tugas personal AI',
        'personal_ai.memory' => 'Urus item memori AI sendiri',
        'personal_ai.configure' => 'Konfigurasi preferensi & tukar model AI',

        // B-Boardroom Workflow (BAB 20)
        'boardroom.view' => 'Lihat sesi boardroom, dapatan ejen & statistik',
        'boardroom.create' => 'Buka sesi boardroom baru',
        'boardroom.participate' => 'Rekod dapatan ejen & jalankan langkah workflow',
        'boardroom.decide' => 'Rekod keputusan pemilik pada sesi boardroom',

        // B-Flow Automation (BAB 28)
        'flow.view' => 'Lihat workflow, run history & statistik automasi',
        'flow.manage' => 'Cipta, ubah suai, aktifkan & padam workflow',
        'flow.execute' => 'Cetus workflow secara manual & retry run',
        'flow.kill_switch' => 'Hidupkan/matikan kill switch workflow',

        // B-AI Gateway (BAB 21 + 22)
        'ai_gateway.use' => 'Guna AI chat & lihat wallet sendiri',
        'ai_gateway.wallet' => 'Lihat & top-up wallet kredit AI tenant',
        'ai_gateway.models' => 'Lihat katalog model AI',
        'ai_gateway.admin' => 'Urus katalog model, rate & usage events (admin)',

        'helpdesk.view' => 'Lihat & balas tiket (ditugaskan)',
        'helpdesk.manage' => 'Urus semua tiket, SLA & pangkalan pengetahuan',

        // B-Payroll — Malaysian Statutory Payroll
        'payroll.view' => 'Lihat data pekerja & laporan payroll',
        'payroll.manage' => 'Urus payroll, overtime & kadar statutori',

        // B-Finance — Financial Management
        'finance.view' => 'Lihat akaun, jurnal & laporan kewangan',
        'finance.manage' => 'Urus chart of accounts, jurnal & transaksi kewangan',
        'finance.approve' => 'Lulus jurnal dan transaksi kewangan',
        'finance.reports' => 'Akses laporan kewangan (P&L, Balance Sheet, Trial Balance)',

        // B-CRM — Customer Relationship Management & Sales Force Automation
        'crm.view' => 'Lihat pelanggan, leads, opportunities & quotations',
        'crm.manage' => 'Urus pelanggan, leads, opportunities & quotations',
        'crm.approve' => 'Lulus quotations & teruskan ke sales order',
        'crm.reports' => 'Akses laporan CRM (pipeline, conversion, statistics)',

        // B-Invoicing — Quotation, Invoice, Credit/Debit Note, Recurring Billing & e-Invois LHDN
        'invoicing.view' => 'Lihat quotation, invoice, credit/debit note & recurring billing',
        'invoicing.manage' => 'Urus quotation, invoice, payment & recurring billing',
        'invoicing.approve' => 'Lulus invoice dan hantar ke LHDN e-Invois',
        'invoicing.reports' => 'Akses laporan invoicing (aging, collection rate, overdue)',

        // B-Virtual Office — Agent, Task, Board & Schedule Management
        'virtual_office.view' => 'Lihat agents, tasks, boards & schedules',
        'virtual_office.manage' => 'Urus agents, tasks, boards, columns & schedules',

        // B-HRMS — Employee, Shift & Leave Management
        'hrms.view' => 'Lihat pekerja, shift, cuti & rekod kehadiran',
        'hrms.manage' => 'Urus pekerja, shift, cuti & kehadiran',
        'hrms.approve' => 'Lulus permintaan cuti & overtime',

        // Biometrik (BAB 56C.6A) — face recognition berpagar consent
        'biometric.view' => 'Lihat tetapan & status consent biometrik',
        'biometric.configure' => 'Konfigurasi pagar consent biometrik',
        'biometric.audit' => 'Lihat audit log padanan wajah',

        // B-Vision / CCTV (BAB 56C Fasa 1)
        'vision.view' => 'Lihat kamera & peristiwa CCTV',
        'vision.manage' => 'Urus kamera, retensi & pengakuan peristiwa',
        'vision.audit' => 'Lihat log tontonan CCTV',

        // Prospecting Engine (BAB 82 Fasa 1)
        'prospecting.view' => 'Lihat prospek, senarai & sumber',
        'prospecting.manage' => 'Import CSV, urus sumber, rekod consent & skor prospek',
        'prospecting.audit' => 'Lihat audit log prospect & laporan consent PDPA',

        // Blast Engine (BAB 82 Fasa 2)
        'blast.view' => 'Lihat kempen blast & statistik',
        'blast.manage' => 'Cipta kempen blast, muat naik audiens, atur jadual',
        'blast.approve' => 'Lulus kempen blast sebelum penghantaran',
        'blast.execute' => 'Hantar kempen blast & urus penindihan',

        // Media Studio (BAB 82 Fasa 4) — video & media
        'media.view' => 'Lihat aset media, job & pemahaman video',
        'media.execute' => 'Muat naik media, jalankan pemahaman & jana video',
        'media.approve' => 'Lulus job media sebelumIa disiarkan',
        'media.audit' => 'Lihat log pemprosesan & moderasi media',

        // Sales Copilot (BAB 82 Fasa 3)
        'sales_copilot.view' => 'Lihat brief prospek & analisis balasan',
        'sales_copilot.execute' => 'Jana draf proposal & jadual follow-up',
        'sales_copilot.approve' => 'Lulus proposal & follow-up dihantar',

        // B-Executive AI (BAB 82 Fasa 5)
        'executive_ai.view' => 'Lihat command centre, briefing & danni soalan',
        'executive_ai.execute' => 'Guna pembantu eksekutif & alat AI',
        'executive_ai.approve' => 'Lulus tindakan AI pada tahap autonomy tinggi',
        'executive_ai.configure' => 'Konfigurasi tahap autonomy & keutamaan eksekutif',
        'executive_ai.audit' => 'Lihat jejak audit tindakan AI',

        // COD Engine (BAB 84 Idea #6) — jurang Malaysia
        'cod.view' => 'Lihat pesanan COD, tetapan & analisis',
        'cod.manage' => 'Cipta pesanan COD, kemas kini tetapan & rekod kutipan',
        'cod.execute' => 'Tukar status COD, hantar & kutip wang',
        'cod.reports' => 'Lihat analisis kegagalan & reconciliation COD',

        // Fleet & GPS (BAB 84 Idea #10) — pengurusan armada
        'fleet.view' => 'Lihat kenderaan, pemandu, peta & amaran',
        'fleet.manage' => 'Tambah/kemas kini kenderaan, pemandu & tetapan',
        'fleet.execute' => 'Rekod lokasi GPS, urus perjalanan & selesaikan amaran',
        'fleet.reports' => 'Lihat statistik armada & skor keselamatan',

        // Proof of Value (BAB 84 Idea #5) — metrik hasil per modul
        'proof_of_value.view' => 'Lihat metrik hasil, baseline & eksport',
        'proof_of_value.manage' => 'Tambah metrik, rekod bacaan & baseline',
        'proof_of_value.approve' => 'Sahkan metrik sebagai bukti disahkan',

        // MCP Studio (BAB 84 Idea #1) — AI Model Context Protocol
        'mcp.view' => 'Lihat alat, sumber, prompt & sesi MCP',
        'mcp.manage' => 'Daftar alat, sumber & prompt MCP',
        'mcp.execute' => 'Panggil alat MCP',
        'mcp.approve' => 'Luluskan/tolak panggilan alat MCP yang',
        // Agent Studio (BAB 84 Idea #8) — B-Ejen bina ejen AI
        'agent_studio.view' => 'Lihat ejen, alat, pengetahuan & ujian',
        'agent_studio.manage' => 'Bina/kemas kini ejen, ikat alat & pengetahuan',
        'agent_studio.execute' => 'Jalankan ejen & ujian',
        'agent_studio.approve' => 'Publish ejen ke produksi',

        // Unified Order Management (BAB 86 Idea #19) — satu tempat semua pesanan
        'orders.view' => 'Lihat pesanan unified, ringkasan jualan & timeline',
        'orders.manage' => 'Cipta/edit pesanan & tetapkan channel binding',
        'orders.execute' => 'Tukar status, rekod bayaran & sync platform',

        // SEO & Metadata Engine (BAB 86 Idea #20) — SEO untuk semua objek
        'seo.view' => 'Lihat metadata, audit SEO, isu, cadangan & kata kunci',
        'seo.manage' => 'Simpan metadata, selesaikan isu & terima cadangan',
        'seo.execute' => 'Jana cadangan metadata AI',

        // Content Studio (BAB 86 Idea #21) — jana & terbitkan kandungan
        'content.view' => 'Lihat kandungan, komen, chat, platform & prestasi',
        'content.manage' => 'Simpan kandungan, platform, brand voice & moderation rules',
        'content.execute' => 'Jana kandungan AI, balas komen/chat & terbitkan',

        // Reminder & Notification Center (BAB 86 Idea #22) — pusat peringatan
        'reminder.view' => 'Lihat peringatan, kotak masuk AI, jadual & ringkasan',
        'reminder.manage' => 'Cipta/edit peringatan, jenis & jadual berulang',
        'reminder.execute' => 'Hantar, tunda, sahkan & lengkapkan peringatan',

        // Business Intelligence (BAB 86 Idea #23-25) — AI self-improve & pesaing
        'business_intel.view' => 'Lihat skor kesihatan, kajian, idea, pesaing & sinyal',
        'business_intel.manage' => 'Terima/tolak idea & urus pesaing',
        'business_intel.execute' => 'Jalankan kajian AI, snapshot kesihatan & jana sinyal',

        // Workflow Studio (BAB 86 Idea #26) — visual workflow + template
        'workflow.view' => 'Lihat workflow, templat, node types & sejarah run',
        'workflow.manage' => 'Cipta/edit workflow & pasang templat',
        'workflow.execute' => 'Jalankan workflow',

        // Media Editor (BAB 86 Idea #27) — edit gambar/video sendiri
        'media_editor.view' => 'Lihat projek media, templat & kerja render',
        'media_editor.manage' => 'Cipta/edit projek media & lapisan',
        'media_editor.execute' => 'Jalankan render media',

        // Live Support (BAB 86 Idea #28) — call center AI + eSIM
        'live_support.view' => 'Lihat saluran, panggilan & ringkasan live support',
        'live_support.manage' => 'Urus saluran live support & eSIM',
        'live_support.execute' => 'Terima panggilan, transkrip, eskalasi & tamat',

        // Community (BAB 86 Idea #29) — komuniti AI BIJAN
        'community.view' => 'Lihat ruang, pos & balasan komuniti',
        'community.manage' => 'Cipta ruang, pos & balasan komuniti',

        // B-ServiceOS (BAB 87) — service business operating system
        'service_os.view' => 'Lihat projek perkhidmatan, sebut harga & jadual',
        'service_os.manage' => 'Urus projek, sebut harga, kru, tugas & penugasan',
        'service_os.finance' => 'Lihat margin, kos sebenar & laporan kewangan projek',

        // B-Expense (BAB 89) — pengurusan perbelanjaan
        'expense.view' => 'Lihat perbelanjaan, resit, tuntutan & ringkasan',
        'expense.manage' => 'Cipta/edit perbelanjaan, muat naik resit & penghantaran tuntutan',
        'expense.approve' => 'Lulus/tolak perbelanjaan & tuntutan',
        'expense.post' => 'Post perbelanjaan ke jurnal & tandakan dibayar',

        // B-Fundamental (BAB 90) — health, observability, metrics, backup
        'fundamental.view' => 'Lihat health check, log observability, metrik & status backup',
        'fundamental.manage' => 'Urus peraturan alert, rekod metrik & latihan pemulihan',

        // B-Entitlement (BAB 90) — langganan & akses modul
        'entitlement.view' => 'Lihat langganan, plan, modul aktif & had penggunaan',
        'entitlement.manage' => 'Tukar plan, grant/revoke modul & urus had',
    ],

    // Templat peranan untuk setiap tenant baru. '*' = semua kebenaran.
    'roles' => [
        'tenant_owner' => ['name' => 'Pemilik', 'permissions' => ['*']],
        'tenant_admin' => ['name' => 'Pentadbir', 'permissions' => ['inventory.view', 'inventory.manage', 'inventory.approve', 'manufacturing.view', 'manufacturing.manage', 'projects.view', 'projects.manage', 'projects.timesheet', 'assets.view', 'assets.manage', 'helpdesk.view', 'helpdesk.manage', 'tenant.settings.view', 'tenant.settings.update', 'members.view', 'members.invite', 'members.manage', 'audit.view', 'files.upload', 'files.view', 'approvals.view', 'approvals.decide', 'documents.view', 'documents.issue', 'pos.view', 'pos.manage', 'booking.view', 'booking.manage', 'marketplace.view', 'marketplace.manage', 'site.view', 'site.manage', 'marketing.view', 'marketing.manage', 'loyalty.view', 'loyalty.manage', 'affiliate.view', 'affiliate.manage', 'link.view', 'link.manage', 'sign_docs.view', 'sign_docs.manage', 'sign_docs.sign', 'omnichannel.view', 'omnichannel.manage', 'omnichannel.channels', 'logistics.view', 'logistics.manage', 'logistics.shipping', 'analytics.view', 'analytics.manage', 'analytics.export', 'analytics.reports', 'brainhub.view', 'brainhub.insights', 'brainhub.forecasts', 'brainhub.recommendations', 'cogs.view', 'cogs.manage', 'cogs.reports', 'industry_packs.view', 'industry_packs.manage', 'ai_agents.view', 'ai_agents.manage', 'ai_agents.execute', 'personal_ai.view', 'personal_ai.tasks', 'personal_ai.memory', 'personal_ai.configure', 'boardroom.view', 'boardroom.create', 'boardroom.participate', 'boardroom.decide', 'flow.view', 'flow.manage', 'flow.execute', 'flow.kill_switch', 'ai_gateway.use', 'ai_gateway.wallet', 'ai_gateway.models', 'ai_gateway.admin', 'prospecting.view', 'prospecting.manage', 'prospecting.audit', 'blast.view', 'blast.manage', 'blast.approve', 'blast.execute', 'media.view', 'media.execute', 'media.approve', 'media.audit', 'sales_copilot.view', 'sales_copilot.execute', 'sales_copilot.approve', 'executive_ai.view', 'executive_ai.execute', 'executive_ai.approve', 'executive_ai.configure', 'executive_ai.audit', 'cod.view', 'cod.manage', 'cod.execute', 'cod.reports', 'fleet.view', 'fleet.manage', 'fleet.execute', 'fleet.reports', 'proof_of_value.view', 'proof_of_value.manage', 'proof_of_value.approve', 'mcp.view', 'mcp.manage', 'mcp.execute', 'mcp.approve', 'agent_studio.view', 'agent_studio.manage', 'agent_studio.execute', 'agent_studio.approve', 'orders.view', 'orders.manage', 'orders.execute', 'seo.view', 'seo.manage', 'seo.execute', 'content.view', 'content.manage', 'content.execute', 'reminder.view', 'reminder.manage', 'reminder.execute', 'business_intel.view', 'business_intel.manage', 'business_intel.execute', 'workflow.view', 'workflow.manage', 'workflow.execute', 'media_editor.view', 'media_editor.manage', 'media_editor.execute', 'live_support.view', 'live_support.manage', 'live_support.execute', 'community.view', 'community.manage', 'service_os.view', 'service_os.manage', 'service_os.finance', 'expense.view', 'expense.manage', 'expense.approve', 'expense.post', 'fundamental.view', 'fundamental.manage', 'entitlement.view', 'entitlement.manage']],
        'finance_manager' => ['name' => 'Pengurus Kewangan', 'permissions' => ['inventory.view', 'manufacturing.view', 'projects.view', 'assets.view', 'assets.manage', 'files.upload', 'files.view', 'approvals.view', 'documents.view', 'documents.issue', 'documents.void', 'payroll.view', 'payroll.manage', 'finance.view', 'finance.manage', 'finance.approve', 'finance.reports', 'crm.view', 'crm.reports', 'invoicing.view', 'invoicing.manage', 'invoicing.approve', 'invoicing.reports', 'pos.view', 'booking.view', 'marketplace.view', 'site.view', 'marketing.view', 'marketing.reports', 'loyalty.view', 'loyalty.reports', 'affiliate.view', 'affiliate.reports', 'link.view', 'link.reports', 'sign_docs.view', 'sign_docs.reports', 'omnichannel.view', 'omnichannel.reports', 'logistics.view', 'logistics.reports', 'analytics.view', 'analytics.reports', 'analytics.export', 'brainhub.view', 'brainhub.insights', 'brainhub.forecasts', 'brainhub.recommendations', 'cogs.view', 'cogs.manage', 'cogs.reports', 'industry_packs.view', 'ai_agents.view', 'ai_agents.execute', 'personal_ai.view', 'personal_ai.tasks', 'personal_ai.memory', 'personal_ai.configure', 'boardroom.view', 'boardroom.create', 'boardroom.participate', 'flow.view', 'flow.manage', 'flow.execute', 'ai_gateway.use', 'ai_gateway.wallet', 'ai_gateway.models', 'blast.view', 'blast.manage', 'blast.approve', 'media.view', 'executive_ai.view', 'cod.view', 'proof_of_value.view']],
        'sales_manager' => ['name' => 'Pengurus Jualan', 'permissions' => ['crm.view', 'crm.manage', 'crm.reports', 'marketing.view', 'marketing.manage', 'marketing.approve', 'marketing.reports', 'prospecting.view', 'prospecting.manage', 'prospecting.audit', 'blast.view', 'blast.manage', 'blast.approve', 'blast.execute', 'media.view', 'media.execute', 'media.approve', 'sales_copilot.view', 'sales_copilot.execute', 'sales_copilot.approve', 'executive_ai.view', 'executive_ai.execute', 'documents.view', 'documents.issue', 'files.view', 'files.upload', 'analytics.view', 'analytics.reports', 'ai_gateway.use', 'ai_gateway.models', 'cod.view', 'cod.manage', 'cod.execute', 'fleet.view', 'proof_of_value.view']],
        'hr_manager' => ['name' => 'Pengurus HR', 'permissions' => ['projects.view', 'projects.manage', 'projects.timesheet', 'projects.approve', 'members.view', 'files.upload', 'files.view', 'approvals.view', 'documents.view', 'documents.issue', 'documents.sensitive', 'documents.void', 'payroll.view', 'payroll.manage', 'hrms.view', 'hrms.manage', 'hrms.approve', 'booking.view', 'marketplace.view', 'site.view', 'marketing.view', 'marketing.reports', 'loyalty.view', 'affiliate.view', 'link.view', 'sign_docs.view', 'sign_docs.manage', 'sign_docs.sign', 'omnichannel.view', 'omnichannel.manage', 'logistics.view', 'analytics.view', 'brainhub.view', 'brainhub.insights', 'cogs.view', 'cogs.reports', 'industry_packs.view', 'ai_agents.view', 'personal_ai.view', 'personal_ai.tasks', 'personal_ai.memory', 'personal_ai.configure', 'boardroom.view', 'flow.view', 'ai_gateway.use', 'ai_gateway.models']],
        'inventory_manager' => ['name' => 'Pengurus Stok', 'permissions' => ['inventory.view', 'inventory.manage', 'inventory.approve', 'inventory.sell', 'manufacturing.view', 'manufacturing.manage', 'files.upload', 'files.view', 'documents.view', 'documents.issue', 'logistics.view', 'logistics.manage', 'logistics.shipping', 'analytics.view', 'brainhub.view', 'brainhub.forecasts', 'cogs.view', 'cogs.manage', 'cogs.reports', 'industry_packs.view', 'ai_agents.view', 'personal_ai.view', 'personal_ai.tasks', 'personal_ai.memory', 'personal_ai.configure', 'boardroom.view', 'flow.view', 'ai_gateway.use', 'ai_gateway.models']],
        'cashier' => ['name' => 'Juruwang', 'permissions' => ['inventory.view', 'inventory.sell', 'pos.view', 'booking.view', 'marketplace.view', 'site.view', 'marketing.view', 'loyalty.view', 'affiliate.view', 'link.view', 'sign_docs.view', 'omnichannel.view', 'logistics.view', 'analytics.view', 'brainhub.view', 'cogs.view', 'industry_packs.view', 'ai_agents.view', 'personal_ai.view', 'personal_ai.tasks', 'personal_ai.memory', 'boardroom.view', 'flow.view', 'ai_gateway.use', 'ai_gateway.models']],
        'staff' => ['name' => 'Staf', 'permissions' => ['projects.view', 'projects.timesheet', 'helpdesk.view', 'files.upload', 'pos.view', 'booking.view', 'marketplace.view', 'site.view', 'marketing.view', 'loyalty.view', 'affiliate.view', 'link.view', 'sign_docs.view', 'omnichannel.view', 'logistics.view', 'analytics.view', 'brainhub.view', 'cogs.view', 'industry_packs.view', 'ai_agents.view', 'personal_ai.view', 'personal_ai.tasks', 'personal_ai.memory', 'boardroom.view', 'flow.view', 'ai_gateway.use', 'ai_gateway.models', 'media.view', 'media.execute', 'executive_ai.view']],
        'auditor' => ['name' => 'Juruaudit', 'permissions' => ['inventory.view', 'manufacturing.view', 'projects.view', 'assets.view', 'helpdesk.view', 'audit.view', 'members.view', 'files.view', 'tenant.settings.view', 'documents.view', 'marketing.view', 'marketing.reports', 'loyalty.view', 'loyalty.reports', 'affiliate.view', 'affiliate.reports', 'link.view', 'link.reports', 'sign_docs.view', 'sign_docs.reports', 'omnichannel.view', 'omnichannel.reports', 'logistics.view', 'logistics.reports', 'analytics.view', 'analytics.reports', 'brainhub.view', 'brainhub.insights', 'brainhub.forecasts', 'brainhub.recommendations', 'cogs.view', 'cogs.reports', 'industry_packs.view', 'ai_agents.view', 'personal_ai.view', 'personal_ai.tasks', 'personal_ai.memory', 'boardroom.view', 'flow.view', 'ai_gateway.use', 'ai_gateway.models']],
    ],

    'trial_days' => 14,
    'upload' => [
        'max_kb' => 10240,
        'mimes' => ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'csv', 'xlsx', 'docx', 'txt'],
    ],
];
