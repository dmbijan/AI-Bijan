# Integrasi B-POS ke Modul POS AI BIJAN

Ciri POS lanjutan daripada [dmbijan/bpos](https://github.com/dmbijan/bpos) yang
belum wujud dalam AI BIJAN.

> Tarikh: 9 Nov 2026

---

## Pendekatan

B-POS ialah aplikasi Laravel penuh (146 migrasi). Daripada menyalin keseluruhan
aplikasi, kami **hanya mengambil ciri POS yang belum ada** dalam modul `POS`
AI BIJAN sedia ada (19 jadual, 43 fail).

Ciri yang sudah wujud (**tidak diduplikasi**):
`pos_sales`, `pos_sale_items`, `pos_sale_payments`, `pos_registers`, `cash_shifts`,
`pos_dine_*`, `pos_modifier_*`, `pos_discount_approvals`, `pos_tng_notifications`,
`pos_receipt_templates`, `pos_shift_cash_movements`.

Ciri yang diambil daripada Inventory/Invoicing/CRM sedia ada (**tidak diduplikasi**):
`warehouses`, `stock_transfers`, `units`, `price_lists`, `product_batches`,
`receivables`, `payables`, `suppliers`, `purchase_orders`, `goods_receivings`,
`loyalty_points`, `customer_vouchers`, `customer_segments`.

---

## 11 Ciri Baharu: `App\Modules\POSBpos`

| # | Ciri | Jadual | Model |
|---|------|--------|-------|
| 1 | Kad hadiah | `pos_gift_cards` | `PosGiftCard` |
| 2 | Tempahan meja | `pos_table_reservations` | `PosTableReservation` |
| 3 | Kehadiran staf | `pos_staff_attendances` | `PosStaffAttendance` |
| 4 | Jadual syif staf | `pos_staff_roster_shifts` | `PosStaffRosterShift` |
| 5 | Food court | `pos_food_courts` | `PosFoodCourt` |
| 6 | Meja food court | `pos_food_court_tables` | `PosFoodCourtTable` |
| 7 | Kaunter giliran | `pos_queue_counters` | `PosQueueCounter` |
| 8 | Langganan push | `pos_push_subscriptions` | `PosPushSubscription` |
| 9 | Sync offline | `pos_delivery_syncs` | `PosDeliverySync` |
| 10 | Kedai online | `pos_online_stores` | `PosOnlineStore` |
| 11 | Bil cawangan | `pos_branch_invoices` | `PosBranchInvoice` |
| 12 | Bayaran langganan | `pos_subscription_payments` | `PosSubscriptionPayment` |

---

## API

Prefix: `/api/v1/pos-bpos` · dilindungi `sanctum` + `tenant` + `perm:pos.*`

```
# Kad hadiah
GET  /gift-cards                    Senarai kad
POST /gift-cards                    Terbit kad baharu
POST /gift-cards/redeem             Tebus (atomik, lockForUpdate)

# Tempahan meja
GET  /reservations                  Senarai tempahan
POST /reservations                  Cipta tempahan
POST /reservations/{id}/confirm     Sahkan (semak konflik meja)
POST /reservations/{id}/cancel      Batal

# Kehadiran & syif
GET  /attendance                    Rekod kehadiran
POST /attendance/clock-in           Masuk kerja
POST /attendance/clock-out          Keluar kerja
GET  /roster                        Jadual syif
POST /roster                        Cipta syif

# Food court & giliran
GET  /food-courts                   Senarai food court
POST /food-courts                   Cipta + auto-jana meja
GET  /food-courts/{id}/tables       Senarai meja
POST /queue/next                    Nombor giliran seterusnya

# Sync offline
GET  /sync                          Baris gilir sync
POST /sync                          Masuk baris gilir
POST /sync/{id}/synced              Tanda berjaya
POST /sync/{id}/failed              Tanda gagal

# Kedai online
GET  /online-stores                 Senarai kedai
POST /online-stores                 Cipta kedai
POST /online-stores/{id}/publish    Terbit kedai

# Bil cawangan & langganan
GET  /branch-invoices               Senarai bil
POST /branch-invoices               Cipta bil
GET  /subscription-payments         Senarai bayaran
POST /subscription-payments         Rekod bayaran (idempoten)
POST /subscription-payments/{id}/confirm   Sahkan bayaran
```

---

## Keputusan Reka Bentuk Penting

### 1. Wang sebagai integer SEN
Semua nilai wang disimpan sebagai `unsignedBigInteger` dalam **sen** (bukan float).
Ini menghapuskan ralat pembundaran titik terapung — amalan B-POS.

```php
'amount_sen' => 1800,   // RM18.00
'balance_sen' => 5000,  // RM50.00
```

### 2. Operasi atomik untuk wang
Penebusan kad hadiah dan pengesahan bayaran menggunakan `lockForUpdate()`
dalam transaksi — elak race condition bila dua juruwang tebus serentak.

```php
return DB::transaction(function () use ($kod, $jumlahSen) {
    $kad = PosGiftCard::where('code', $kod)->lockForUpdate()->first();
    // ...
});
```

### 3. Nombor giliran atomik
`nomborGiliran()` menggunakan `lockForUpdate()` supaya dua kaunter tidak
mendapat nombor yang sama.

### 4. idempoten sync
`client_uuid` unik per tenant → replay sync tidak menduplikasi rekod.

### 5. Tempahan meja tanpa konflik
`sahkanTempahan()` menyemak tempahan bertindih pada meja yang sama sebelum
mengesahkan.

---

## Konfigurasi

Fail: `app/Modules/POSBpos/config/pos_bpos.php`

| Kunci | Lalai | Maksud |
|---|---|---|
| `gift_card.default_validity_days` | 365 | Tempoh sah kad hadiah |
| `reservation.default_duration_minutes` | 90 | Tempoh tempahan meja |
| `attendance.late_threshold_minutes` | 5 | Ambang lewat |
| `food_court.default_tables` | 10 | Meja auto-jana |
| `sync.batch_size` | 50 | Saiz batch sync |
| `billing.per_branch_sen` | 1800 | RM18/cawangan |

Persekitaran: `POS_BPOS_ENABLED`, `POS_BPOS_MAX_GIFTCARDS`, `POS_BPOS_GC_DAYS`,
`POS_BPOS_RSV_MINUTES`, `POS_BPOS_SYNC_BATCH`, `POS_BPOS_PER_BRANCH_SEN`.

---

## Pengesahan

| Semakan | Keputusan |
|---|---|
| Ujian pengasingan POSBpos | **5/5 lulus**, 36 assertions |
| Laluan API | **28** dilindungi `perm:pos.*` + `tenant` |
| Pint | **passed** |
| Sintaks | **0 ralat** pada 18 fail |
| Migrasi | 12 jadual dicipta |

---

## Nota

- **Tiada perubahan pada modul POS sedia ada** — integrasi sepenuhnya tambahan.
- Semua model guna `BelongsToTenant` (skop global + anti-pemalsuan `tenant_id`).
- Kebenaran guna namespace `pos.*` sedia ada — tiada kebenaran baharu diperlukan.
