# 🎯 PELAN TINDAKAN — AI BIJAN DEVELOPMENT

**Tarikh:** 2026-10-19  
**Prioriti:** Berdasarkan kesan perniagaan & ketergantungan teknikal

---

## 🔴 FASA PRIORITI TINGGI (Sekarang)

### Task 1: Complete ai-bijan-saas Foundation
**Status:** Core ready, needs finishing touches
**Estimasi:** 2-3 minggu

- [ ] Run `composer install` & `php artisan migrate --seed` — verify all migrations pass
- [ ] Run `php artisan test` — ensure 43 tests still passing
- [ ] Complete Subscription Engine UI (Filament admin)
- [ ] Complete Brand Portfolio Management UI (Filament admin)
- [ ] Finalize AI Token Wallet gateway integration
- [ ] Add missing modules: Procurement, Logistics basic models
- [ ] e-Invois LHDN integration research & planning

### Task 2: Build Next.js Frontend Screens
**Status:** Need to start from scratch
**Estimasi:** 4-6 minggu (semasa dengan Task 1)

Priority screens needed:
- [ ] Dashboard main layout (navigation, sidebar, header)
- [ ] Virtual Office screen (OfficeAgent, OfficeTask kanban)
- [ ] HRMS complete screen (attendance, leave, payroll)
- [ ] POS complete screen (cart, checkout, receipt)
- [ ] Inventory/Warehouse screen (stock, movements, batches)
- [ ] Projects & Helpdesk screen
- [ ] Assets management screen
- [ ] Finance/Invoicing screen
- [ ] Marketing campaign screen
- [ ] Analytics dashboard

**Tech Stack:** Next.js 15 + React 19 + Tailwind CSS + Filament-inspired design

### Task 3: Integrate bloyalty into Main Platform
**Status:** Standalone, needs integration
**Estimasi:** 1 minggu

- [ ] Merge bloyalty models/routes into ai-bijan-saas
- [ ] Share User model (single auth across platforms)
- [ ] Convert standalone to Laravel module in ai-bijan-saas/app/Modules/Loyalty/
- [ ] Update routes to `/loyalty` instead of separate app
- [ ] Test multi-tenant isolation with combined system

---

## 🟡 FASA PRIORITI SEDANG (Selepas Fasa Tinggi)

### Task 4: bijan-virtual-o-office Backend ⭐ PRIORITI TINGGI
**Status:** Design complete, zero code
**Estimasi:** 6-8 minggu
**Architecture:** Module dalam ai-bijan-saas (`app/Modules/VirtualOffice/`)

Decisions made (19/10/2026):
- ✅ Module dalam ai-bijan-saas (bukan app berasingan)
- ✅ New Next.js app untuk frontend dashboard (bukan guna ruang-main)
- ✅ Higgsfield digunakan sebagai content creator (generate gambar & video)

Tasks:
- [ ] Create `app/Modules/VirtualOffice/` structure
- [ ] Set up AI Gateway (OpenAI/Anthropic/Gemini routing)
- [ ] Build connector system (WhatsApp, Telegram, Google Drive, Shopee, TikTok)
- [ ] Implement agent runtime (Redis jobs via Horizon)
- [ ] Build Peti Keputusan (approval inbox)
- [ ] Agent scheduling system
- [ ] Credit/token billing per tenant
- [ ] Integrate Higgsfield API for image/video generation

### Task 5: Ruang Dashboard Completion
**Status:** Scaffolded, needs content
**Estimasi:** 3-4 minggu

- [ ] Connect to ai-bijan-saas API
- [ ] Build admin panel views
- [ ] Implement real-time updates (Laravel Reverb)
- [ ] Add role-based access control UI

### Task 6: POS Next.js Screen
**Status:** Basic scaffold exists
**Estimasi:** 3-4 minggu

- [ ] Product catalog view
- [ ] Cart/checkout flow
- [ ] Payment processing (ToyyibPay/FPX)
- [ ] Receipt generation
- [ ] Offline mode (PWA service worker)
- [ ] Barcode scanning (html5-qrcode)

---

## 🟢 FASA PRIORITI RENDAH (Backlog)

### Task 7: Brain Hub Phase 2
- [ ] Connect remaining modules (Booking, Sign & Docs, Site, Marketplace)
- [ ] AI autonomous suggestions with human approval
- [ ] Daily automated reports
- [ ] Natural language queries with real data

### Task 8: Industry Blueprint Engine
- [ ] Define industry templates (F&B, Retail, Services, Construction, etc.)
- [ ] Auto-configure modules per industry
- [ ] MSIC code mapping
- [ ] Automated onboarding workflow

### Task 9: Advanced AI Features
- [ ] B-Boardroom (multi-agent deliberation)
- [ ] B-Scout/B-Intel (market research)
- [ ] B-Compliance (Malaysian regulation checker)
- [ ] B-Flow/B-Trigger (automation engine)

### Task 10: Open Higgsfield Integration
- [ ] Understand higgsfield purpose
- [ ] Define integration points
- [ ] Implement if beneficial

---

## ⚠️ PENGARUH & KETERGANTUNGAN

```
Task 1 (saas core) ──────┐
                          ├──→ Task 2 (frontend screens)
                          │
Task 3 (loyalty merge) ───┤
                          │
Task 4 (virtual office) ──┼──→ Task 8 (blueprint engine)
                          │
Task 5 (ruang dashboard) ─┤
                          │
Task 6 (POS screens) ─────┤
                          │
All above ────────────────┼──→ Task 7, 9, 10 (advanced features)
```

---

## 📋 KEPUTUSAN PERLU DIAMBIL

### 1. Arsitektur Virtual Office
**Pilihan A:** Module dalam ai-bijan-saas (`app/Modules/VirtualOffice/`)
- ✅ Single codebase, shared auth, shared DB
- ✅ Easier deployment
- ❌ Can become bloated

**Pilihan B:** Separate Laravel app
- ✅ Clean separation of concerns
- ✅ Independent scaling
- ❌ Complex deployment, shared auth needed

**Cadangan:** Pilihan A (module dalam saas) untuk permulaan

### 2. Frappe/ERPNext Role
**Keputusan Owner (18/10/2026):** ERPNext BUKAN dependency
- ai-bijan-saas berdiri sendiri
- aibijan-frappe hanya untuk integrasi optional
- Semua modul utama dibina dalam Laravel

### 3. Frontend Strategy
**Pendapatan:** Next.js 15 sebagai single frontend untuk semua modul
- Shared navigation & layout
- API calls ke ai-bijan-saas
- PWA capable for mobile/POS

### 4. Database Strategy
**Keputusan:** Single database, tenant_id scoping
- Semua tenant dalam satu database
- Data isolation melalui query scopes
- Mudah untuk multi-tenant SaaS pada tahap PKS

---

## 📊 METRIK KEMAJUAN

| Kategori | Siap | Belum | % |
|----------|------|-------|---|
| **Platform Core** | 80% | 20% | 🟢 |
| **Models & Migrations** | 90% | 10% | 🟢 |
| **Backend APIs** | 60% | 40% | 🟡 |
| **Frontend UI** | 10% | 90% | 🔴 |
| **Testing** | 30% | 70% | 🔴 |
| **Documentation** | 95% | 5% | 🟢 |
| **Deployment Ready** | 20% | 80% | 🔴 |

---

## 🚀 NEXT STEPS (Minggu Ini)

1. **Hari 1-2:** Setup development environment
   - `cd ai-bijan-saas && composer install`
   - Configure `.env` dengan database tempatan
   - `php artisan migrate --seed`
   - `php artisan test` — baseline tests

2. **Hari 3-4:** Start building first frontend screen
   - Create Next.js app structure (atau guna ruang-main)
   - Build main dashboard layout
   - Connect to Laravel API

3. **Hari 5-7:** Merge BLoyalty
   - Review BLoyalty code
   - Plan migration strategy
   - Begin merging into ai-bijan-saas

---

*Pelan ini berdasarkan audit penuh workspace pada 2026-10-19.*
*Status dikemas kini secara berkala.*
