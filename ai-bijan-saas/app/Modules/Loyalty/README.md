# B-Loyalty — Loyalty Program & Rewards System

Modul **B-Loyalty** menyediakan sistem pengurusan program kesetiaan pelanggan yang lengkap untuk platform AI-Bijan SaaS. Modul ini membolehkan perniagaan menggalakkan pelanggan kembali melalui program mata ganjaran, tahap loyaliti (tier), dan katalog ganjaran.

## 📋 Ringkasan Ciri-ciri

### Ciri Utama
- **Program Mata Ganjaran** — Sistem ledger mata yang lengkap dengan penjeakan pendapatan, tebusan, dan luput
- **Tahap Loyaliti (Tier)** — Struktur tier dinamik (Bronze, Silver, Gold, Platinum, Diamond) dengan multiplier mata
- **Katalog Ganjaran** — Sistem reward catalog dengan pelbagai jenis (diskaun, penghantaran percuma, produk, cashback, pengalaman)
- **Proses Tebusan** — Workflow tebusan dari pending → fulfilled/cancelled
- **Analitik Lanjutan** - Kira LTV, metrik churn, liabiliti mata, dan segmentation
- **Integrasi CRM** — Sync automatik dengan B-CRM contacts
- **Integrasi Marketplace** — Points earned secara automatik dari pembelian
- **Bonus Automatik** — Signup bonus, birthday bonus, referral bonus, review bonus, social share bonus

## 🏗️ Senarai Modul

| Komponen | Fail | Keterangan |
|----------|------|------------|
| **ServiceProvider** | `LoyaltyServiceProvider.php` | Register services dan config |
| **Config** | `config/loyalty.php` | Konfigurasi program loyalty |
| **Models** | `Models/Tier.php` | Tahap loyaliti (Bronze→Diamond) |
| | `Models/Member.php` | Ahli program dengan SoftDeletes |
| | `Models/PointsLedger.php` | Ledger mata dengan scopes |
| | `Models/Reward.php` | Katalog ganjaran |
| | `Models/Redemption.php` | Rekod tebusan |
| **Services** | `Services/MemberService.php` | CRUD ahli, enrollment, stats |
| | `Services/PointsService.php` | Award, redeem, transfer, expire points |
| | `Services/RewardService.php` | Manage rewards, redemption process |
| | `Services/TierService.php` | Tier management, evaluation, promotion |
| | `Services/LoyaltyAnalyticsService.php` | Analytics, LTV, churn, liability |
| **Controllers** | `Http/Controllers/MemberController.php` | API endpoints untuk ahli |
| | `Http/Controllers/PointsController.php` | API endpoints untuk points |
| | `Http/Controllers/RewardController.php` | API endpoints untuk rewards |
| | `Http/Controllers/TierController.php` | API endpoints untuk tiers |
| | `Http/Controllers/LoyaltyAnalyticsController.php` | API endpoints untuk analytics |
| **Migration** | `database/migrations/2026_10_20_001000_create_loyalty_tables.php` | 5 jadual database |

## 🗄️ Struktur Database

### Jadual: `loyalty_tiers`
```sql
- id                  : Primary key
- tenant_id           : Foreign key ke tenants
- name                : Nama tier (e.g., "Gold")
- slug                : Unique slug
- description         : Penerangan tier
- min_points          : Minimum points untuk qualify
- benefits            : JSON array of benefits
- color_hex           : Warna brand tier
- icon                : Icon identifier
- point_multiplier    : Multiplier untuk earning points (1.0x - 5.0x)
- is_active           : Boolean active flag
- is_default          : Boolean default tier flag
```

### Jadual: `loyalty_members`
```sql
- id                  : Primary key
- tenant_id           : Foreign key ke tenants
- tier_id             : Foreign key ke loyalty_tiers
- crm_contact_id      : Foreign key ke crm_contacts (optional)
- member_code         : Unique code (LOY-MEM-XXXXXXXX format)
- first_name          : Nama pertama
- last_name           : Nama terakhir
- email               : Email address
- phone               : No telefon
- status              : enum('active', 'inactive', 'suspended')
- current_balance     : Semasa balance points
- total_points_earned : Jumlah points earned
- total_points_redeemed : Jumlah points redeemed
- total_points_expired : Jumlah points expired
- lifetime_value      : Total nilai kewangan
- last_activity_date  : Tarikh aktiviti terakhir
- birthday            : Tarikh lahir (untuk birthday bonus)
- referral_code       : Unique referral code
- referred_by_member_id : Foreign key ke loyalty_members (referrer)
- notes               : Catatan admin
```

### Jadual: `loyalty_points_ledger`
```sql
- id                  : Primary key
- tenant_id           : Foreign key ke tenants
- member_id           : Foreign key ke loyalty_members
- reference_type      : Type reference (purchase, signup_bonus, etc.)
- reference_id        : ID reference
- points              : Jumlah points (+/-)
- balance_after       : Balance selepas transaksi
- type                : Type transaksi (earned, redemption, expiration, adjustment, etc.)
- description         : Penerangan transaksi
- expires_at          : Timestamp luput (optional)
```

### Jadual: `loyalty_rewards`
```sql
- id                  : Primary key
- tenant_id           : Foreign key ke tenants
- name                : Nama ganjaran
- slug                : Unique slug
- description         : Penerangan
- type                : enum('discount', 'free_shipping', 'product', 'cashback', 'experience')
- cost_points         : Kos dalam points
- max_redemptions     : Maximum redemptions (nullable = unlimited)
- total_redemptions   : Counter redemptions
- image_url           : URL gambar
- terms_conditions    : Terms & conditions
- is_active           : Boolean active flag
```

### Jadual: `loyalty_redemptions`
```sql
- id                  : Primary key
- tenant_id           : Foreign key ke tenants
- member_id           : Foreign key ke loyalty_members
- reward_id           : Foreign key ke loyalty_rewards (nullable)
- points_spent        : Points yang dibelanjakan
- status              : enum('pending', 'fulfilled', 'cancelled', 'expired')
- redemption_code     : Unique code untuk redemption
- fulfillment_notes   : Catatan fulfillment
- fulfilled_at        : Timestamp fulfillment
```

## 🔌 API Endpoints

### Base URL: `/api/v1/loyalty/`

#### Members (perm: loyalty.view)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/members` | Senarai semua ahli |
| GET | `/members/{id}` | Lihat detail ahli |
| GET | `/members/code/{code}` | Cari oleh member code |
| POST | `/members/enroll-from-contact` | Enrol CRM contact sebagai member |
| POST | `/members/bulk-evaluate-tier` | Bulk evaluate tier upgrades |

#### Members Management (perm: loyalty.manage)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/members` | Cipta ahli baru |
| PUT | `/members/{id}` | Update detail ahli |
| POST | `/members/{id}/deactivate` | Deactivate ahli |
| POST | `/members/{id}/reactivate` | Reactivate ahli |
| POST | `/members/{id}/update-tier` | Update tier berdasarkan points |

#### Points (perm: loyalty.view)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/points/balance/{memberId}` | Dapatkan current balance |
| GET | `/points/ledger/{memberId}` | Recent ledger entries |
| POST | `/points/award` | Award points kepada member |
| POST | `/points/redeem` | Redeem/burn points |
| POST | `/points/transfer` | Transfer points antara members |
| POST | `/points/adjust` | Manual adjust (admin) |
| POST | `/points/expire` | Expire old points |

#### Rewards (perm: loyalty.view)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/rewards` | Senarai rewards |
| GET | `/rewards/{id}` | Detail reward |
| POST | `/rewards/redeem` | Redeem reward |
| POST | `/rewards/{id}/fulfill` | Fulfill redemption |
| POST | `/rewards/{id}/cancel` | Cancel redemption |
| GET | `/rewards/stats` | Reward statistics |

#### Rewards Management (perm: loyalty.manage)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/rewards` | Cipta reward baru |
| PUT | `/rewards/{id}` | Update reward |
| DELETE | `/rewards/{id}` | Delete reward |

#### Tiers (perm: loyalty.view)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/tiers` | Senarai tiers |
| GET | `/tiers/{id}` | Detail tier |
| POST | `/tiers/promote-member` | Promote member ke tier tertentu |
| POST | `/tiers/demote-member` | Demote member dari tier |
| POST | `/tiers/bulk-evaluate` | Bulk evaluate all members |
| GET | `/tiers/stats` | Tier statistics |

#### Tiers Management (perm: loyalty.manage)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/tiers` | Cipta tier baru |
| PUT | `/tiers/{id}` | Update tier |
| DELETE | `/tiers/{id}` | Delete tier |

#### Tiers Approval (perm: loyalty.approve)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/redemptions/{id}/approve` | Approve redemption |
| POST | `/redemptions/{id}/reject` | Reject redemption |

#### Reports (perm: loyalty.reports)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/members/stats` | Member statistics |
| GET | `/rewards/stats` | Reward statistics |
| GET | `/tiers/stats` | Tier statistics |
| GET | `/analytics` | Overall analytics |
| GET | `/analytics/segmentation` | Member segmentation |
| GET | `/analytics/ltv-trends` | LTV trends (12 months) |
| GET | `/analytics/churn-metrics` | Churn prediction metrics |
| GET | `/analytics/liability` | Points liability report |

## ⚙️ Konfigurasi

File config: `app/Modules/Loyalty/config/loyalty.php`

### Points Earning Rules
```php
'earning_rules' => [
    'purchase_multiplier' => 1.0,      // 1 point per RM1
    'signup_bonus' => 100,             // Welcome bonus
    'birthday_bonus' => 50,            // Birthday bonus
    'referral_bonus' => 75,            // Referral bonus
    'review_bonus' => 25,              // Review bonus
    'social_share_bonus' => 10,        // Social share bonus
],
```

### Points Burning Rules
```php
'burning_rules' => [
    'min_points_to_redeem' => 10,      // Minimum points untuk redeem
    'max_points_per_transaction' => 5000, // Maximum per transaction
    'point_value' => 0.01,             // 1 point = RM0.01
],
```

### Points Expiration
```php
'expiration' => [
    'enabled' => true,                 // Enable expiration
    'ttl_months' => 24,                // Points expire after 24 months
    'cron_schedule' => '0 0 * * 0',    // Run every Sunday midnight
],
```

### Default Tiers
```php
'default_tiers' => [
    ['name' => 'Bronze', 'min_points' => 0, 'multiplier' => 1.0, 'color' => '#CD7F32'],
    ['name' => 'Silver', 'min_points' => 1000, 'multiplier' => 1.2, 'color' => '#C0C0C0'],
    ['name' => 'Gold', 'min_points' => 5000, 'multiplier' => 1.5, 'color' => '#FFD700'],
    ['name' => 'Platinum', 'min_points' => 20000, 'multiplier' => 2.0, 'color' => '#E5E4E2'],
    ['name' => 'Diamond', 'min_points' => 50000, 'multiplier' => 2.5, 'color' => '#B9F2FF'],
],
```

## 🔗 Integrasi Modul Lain

### B-CRM Integration
- Auto-enroll contacts dari CRM ke loyalty program
- Link membership dengan CRM contact records
- Sync member data dengan CRM profiles

### B-Marketplace Integration
- Award points secara automatik dari setiap pembelian
- Track purchase history untuk tier evaluation
- Display loyalty points di checkout page

### B-Site Integration
- Embed loyalty signup forms
- Display member dashboard di customer portal
- Show reward catalog di storefront

### B-Analytics Integration
- Export loyalty metrics ke analytics dashboard
- Cross-module ROI calculation
- Customer lifetime value analysis

## 👥 Permission Matrix

| Role | View | Manage | Approve | Reports |
|------|------|--------|---------|---------|
| **tenant_owner** | ✅ (all) | ✅ (all) | ✅ (all) | ✅ (all) |
| **tenant_admin** | ✅ | ✅ | ❌ | ❌ |
| **finance_manager** | ✅ | ❌ | ❌ | ✅ |
| **hr_manager** | ✅ | ❌ | ❌ | ❌ |
| **inventory_manager** | ❌ | ❌ | ❌ | ❌ |
| **cashier** | ✅ | ❌ | ❌ | ❌ |
| **staff** | ✅ | ❌ | ❌ | ❌ |
| **auditor** | ✅ | ❌ | ❌ | ✅ |

## 📊 Contoh Penggunaan

### 1. Create New Tier
```bash
POST /api/v1/loyalty/tiers
{
  "name": "Gold",
  "slug": "gold",
  "description": "Premium tier with 1.5x points multiplier",
  "min_points": 5000,
  "benefits": ["Priority support", "Free shipping", "Early access"],
  "color_hex": "#FFD700",
  "icon": "star",
  "point_multiplier": 1.5,
  "is_active": true
}
```

### 2. Enroll Member from Contact
```bash
POST /api/v1/loyalty/members/enroll-from-contact
{
  "contact_id": 123
}
```

### 3. Award Points (Purchase)
```bash
POST /api/v1/loyalty/points/award
{
  "member_id": 456,
  "type": "purchase",
  "points": 100,
  "reference_type": "order",
  "reference_id": "ORD-2024-001",
  "description": "Points for order ORD-2024-001"
}
```

### 4. Redeem Reward
```bash
POST /api/v1/loyalty/rewards/redeem
{
  "member_id": 456,
  "reward_id": 789
}
```

### 5. Get Member Stats
```bash
GET /api/v1/loyalty/members/stats
```

Response:
```json
{
  "total": 1250,
  "active": 1100,
  "avg_balance": 850.5,
  "avg_ltv": 1250.75,
  "total_ltv": 1563437.50,
  "tier_breakdown": [
    {"name": "Bronze", "count": 500},
    {"name": "Silver", "count": 400},
    {"name": "Gold", "count": 250},
    {"name": "Platinum", "count": 80},
    {"name": "Diamond", "count": 20}
  ]
}
```

## 🔄 Cron Jobs

### Expire Old Points
```bash
php artisan schedule:run
```

Cron schedule (dalam config):
```
0 0 * * 0  # Every Sunday at midnight
```

Task akan:
1. Cari ledger entries yang expired
2. Create expiration entries
3. Update member balances
4. Increment total_points_expired

### Evaluate Tier Upgrades
```bash
php artisan loyalty:evaluate-tiers
```

Task akan:
1. Loop semua active members
2. Check current balance vs tier requirements
3. Auto-promote jika qualify
4. Log tier changes

## 🧪 Testing

Run tests:
```bash
php artisan test --testsuite=Loyalty
```

Test coverage:
- ✅ Unit tests untuk Services
- ✅ Feature tests untuk Controllers
- ✅ Integration tests untuk database operations
- ✅ Edge cases (insufficient points, invalid tiers, etc.)

## 📈 Metrics & KPIs

### Member Metrics
- Total members
- Active members
- Average balance
- Average LTV
- Total LTV
- Tier distribution

### Points Metrics
- Total points earned
- Total points redeemed
- Net points outstanding
- Burn rate (%)
- Expiration rate (%)

### Redemption Metrics
- Total redemptions
- Points burned
- Fulfillment rate
- Pending queue

### Financial Metrics
- ROI percentage
- Points liability (RM)
- Expiring liability (RM)
- Cost per acquisition

## 🚀 Deployment Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Seed default tiers (optional)
- [ ] Configure cron jobs
- [ ] Set up webhook integrations
- [ ] Test point awarding flow
- [ ] Test redemption flow
- [ ] Verify permission matrix
- [ ] Monitor initial metrics

## 📝 Notes Penting

1. **Member Code Format**: `LOY-MEM-XXXXXXXX` (auto-generated)
2. **Point Value**: Default RM0.01 per point (boleh configure)
3. **Tier Multiplier**: Applied pada semua point earnings
4. **Expiration**: Configurable TTL (default 24 months)
5. **Soft Deletes**: Semua models support soft deletes
6. **Tenant Isolation**: Semua queries scoped ke tenant_id
7. **Transaction Safety**: Semua point operations dalam DB transactions

## 🆘 Troubleshooting

### Points tidak bertambah
- Check if member exists dan active
- Verify tenant_id match
- Check PointsService logs

### Tier upgrade tidak auto
- Run `php artisan loyalty:evaluate-tiers`
- Check tier min_points configuration
- Verify member status is 'active'

### Redemption gagal
- Check member balance sufficient
- Verify reward is_active
- Check max_redemptions limit

### Points tidak expire
- Verify cron job running
- Check expiration config enabled
- Run `php artisan loyalty:expire-points --force`

---

**Modul Version**: 1.0.0  
**Last Updated**: 2026-10-20  
**Author**: AI-Bijan Development Team  
**License**: Proprietary - AI-Bijan SaaS
