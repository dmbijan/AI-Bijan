# B-POS — Point of Sale Module

## Overview

AI BIJAN POS system designed for retail stores, cafes, restaurants, and service businesses. Features register management, cash shift tracking, fast sales processing, multi-payment support, and returns/refunds handling.

**Priority:** P1  
**Status:** ✅ Complete  
**Blueprint Reference:** BAB 12, Kumpulan 2 (Jualan, Saluran & Pertumbuhan)

---

## Architecture

```
app/Modules/POS/
├── POSServiceProvider.php          # Service provider registration
├── config/
│   └── pos.php                     # Configuration (payment methods, statuses, settings)
├── Models/
│   ├── POSRegister.php             # POS register entity
│   ├── CashShift.php               # Cash shift tracking
│   ├── POSSale.php                 # Sales transactions
│   ├── POSSaleItem.php             # Line items
│   ├── POSSalePayment.php          # Multi-payment records
│   ├── POSSaleDiscount.php         # Discount tracking
│   └── POSSaleReturn.php           # Returns/refunds
├── Services/
│   ├── RegisterService.php         # Register CRUD operations
│   ├── CashShiftService.php        # Shift open/close/discrepancy
│   └── SaleService.php             # Sale lifecycle management
├── Http/Controllers/
│   ├── RegisterController.php      # Register API endpoints
│   ├── SaleController.php          # Sale API endpoints
│   └── CashShiftController.php     # Shift API endpoints
├── database/migrations/
│   └── 2026_10_19_000500_create_pos_tables.php
└── routes/
    └── api.php                     # Module-specific routes
```

---

## Database Schema

### Tables

#### `pos_registers`
POS registers representing physical or virtual checkout points.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| code | VARCHAR(50) | Unique code (e.g., POS-REG-001) |
| name | VARCHAR(255) | Display name |
| description | TEXT | Optional description |
| location | VARCHAR(255) | Physical location |
| is_active | BOOLEAN | Active/inactive status |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `cash_shifts`
Tracks opening/closing of cash shifts per register.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| register_id | BIGINT UNSIGNED | FK → pos_registers |
| user_id | BIGINT UNSIGNED | User who opened shift |
| status | ENUM | open, closed, locked |
| opening_cash | DECIMAL(15,2) | Starting float (default RM200) |
| closing_cash | DECIMAL(15,2) | Actual counted cash |
| expected_cash | DECIMAL(15,2) | Calculated expected amount |
| cash_discrepancy | DECIMAL(15,2) | Difference (closing - expected) |
| notes | TEXT | Operator notes |
| opened_at | TIMESTAMP | When shift opened |
| closed_at | TIMESTAMP | When shift closed |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `pos_sales`
Sales transactions linked to register and shift.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| sale_no | VARCHAR(50) | Unique sale number (SALE-YYYY-NNNNNN) |
| register_id | BIGINT UNSIGNED | FK → pos_registers |
| cash_shift_id | BIGINT UNSIGNED | FK → cash_shifts |
| customer_id | BIGINT UNSIGNED | FK → crm_clients (optional) |
| user_id | BIGINT UNSIGNED | Cashier/operator |
| status | ENUM | draft, completed, cancelled, refunded, partially_refunded |
| subtotal | DECIMAL(15,2) | Sum of line items before discount/tax |
| tax_amount | DECIMAL(15,2) | Total tax |
| discount_amount | DECIMAL(15,2) | Total discounts |
| total_amount | DECIMAL(15,2) | Final amount due |
| amount_paid | DECIMAL(15,2) | Total payments received |
| change_amount | DECIMAL(15,2) | Change given |
| notes | TEXT | Internal notes |
| sale_date | DATE | Date of sale |
| completed_at | TIMESTAMP | When completed |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `pos_sale_items`
Line items within a sale.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| sale_id | BIGINT UNSIGNED | FK → pos_sales |
| product_id | BIGINT UNSIGNED | FK → inventory_products (nullable) |
| product_name | VARCHAR(255) | Product name snapshot |
| product_sku | VARCHAR(100) | SKU snapshot |
| quantity | INT | Quantity sold |
| unit_price | DECIMAL(15,2) | Price per unit |
| discount_percentage | DECIMAL(5,2) | Item-level % discount |
| discount_amount | DECIMAL(15,2) | Item-level fixed discount |
| tax_rate | DECIMAL(5,2) | Tax rate applied |
| tax_amount | DECIMAL(15,2) | Tax amount |
| line_total | DECIMAL(15,2) | Final line total |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `pos_sale_payments`
Multiple payment methods per sale.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| sale_id | BIGINT UNSIGNED | FK → pos_sales |
| payment_method | ENUM | cash, card, ewallet, credit, voucher, split |
| amount | DECIMAL(15,2) | Amount for this method |
| reference_number | VARCHAR(100) | Transaction/reference ID |
| payment_details | JSON | Additional data (card type, e-wallet app, etc.) |
| created_at | TIMESTAMP | Creation timestamp |

#### `pos_sale_discounts`
Detailed discount breakdown.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| sale_id | BIGINT UNSIGNED | FK → pos_sales |
| type | ENUM | percentage, fixed, loyalty, voucher, manual |
| code | VARCHAR(50) | Discount/promo code |
| value | DECIMAL(15,2) | Percentage or fixed value |
| amount | DECIMAL(15,2) | Discount amount applied |
| description | VARCHAR(255) | Human-readable description |
| created_at | TIMESTAMP | Creation timestamp |

#### `pos_sale_returns`
Returns and refunds.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| return_no | VARCHAR(50) | Unique return number (RET-YYYY-NNNNNN) |
| sale_id | BIGINT UNSIGNED | FK → pos_sales |
| status | ENUM | pending, approved, rejected, completed |
| reason | TEXT | Reason for return |
| return_amount | DECIMAL(15,2) | Total refund amount |
| approved_by | BIGINT UNSIGNED | Approver user ID |
| approved_at | TIMESTAMP | Approval timestamp |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `pos_receipt_templates`
Receipt customization.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| name | VARCHAR(100) | Template name |
| header | TEXT | Header text/logo |
| footer | TEXT | Footer text/terms |
| print_logo | BOOLEAN | Print company logo |
| is_default | BOOLEAN | Default template |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

---

## API Endpoints

Base URL: `/api/v1/pos/`

### Registers

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/registers` | `pos.view` | List all registers |
| GET | `/registers/{id}` | `pos.view` | Show register details |
| POST | `/registers` | `pos.manage` | Create new register |
| PUT | `/registers/{id}` | `pos.manage` | Update register |
| DELETE | `/registers/{id}` | `pos.manage` | Delete register |
| POST | `/registers/{id}/toggle` | `pos.manage` | Toggle active status |
| GET | `/registers/{id}/summary` | `pos.view` | Get register sales summary |

### Cash Shifts

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/shifts` | `pos.view` | List all shifts |
| GET | `/shifts/open/{registerId}` | `pos.view` | Get current open shift |
| GET | `/shifts/{id}` | `pos.view` | Show shift details |
| POST | `/shifts/open` | `pos.manage` | Open new shift |
| POST | `/shifts/{id}/close` | `pos.manage` | Close shift |
| POST | `/shifts/{id}/lock` | `pos.manage` | Lock closed shift |
| GET | `/shifts/summary` | `pos.view` | Get shift summary statistics |

### Sales

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/sales` | `pos.view` | List sales (with filters) |
| GET | `/sales/{id}` | `pos.view` | Show sale details |
| GET | `/sales/summary` | `pos.view` | Get POS summary statistics |
| POST | `/sales/draft` | `pos.manage` | Create draft sale |
| POST | `/sales/{saleId}/items` | `pos.manage` | Add item to sale |
| PUT | `/sales/items/{itemId}` | `pos.manage` | Update sale item |
| DELETE | `/sales/items/{itemId}` | `pos.manage` | Remove item from sale |
| POST | `/sales/{saleId}/complete` | `pos.manage` | Complete sale transaction |
| POST | `/sales/{saleId}/cancel` | `pos.manage` | Cancel draft sale |
| POST | `/sales/{saleId}/refund` | `pos.manage` | Process refund |

---

## Numbering Formats

| Entity | Format | Example |
|--------|--------|---------|
| Register Code | POS-REG-NNN | POS-REG-001 |
| Sale Number | SALE-YYYY-NNNNNN | SALE-2026-000001 |
| Return Number | RET-YYYY-NNNNNN | RET-2026-000001 |

---

## Payment Methods

| Key | Label (Malay) | Description |
|-----|---------------|-------------|
| cash | Tunai | Cash payments |
| card | Kad (Debit/Credit) | Credit/debit cards |
| ewallet | E-Wallet | Touch 'n Go, GrabPay, etc. |
| credit | Akaun Kredit | Credit account |
| voucher | Voucher/Gift Card | Voucher/gift card redemption |
| split | Pembayaran Berpisah | Split across multiple methods |

---

## Sale Statuses

| Status | Malay Label | Description |
|--------|-------------|-------------|
| draft | Rangka | Sale in progress, not finalized |
| completed | Selesai | Sale finalized and paid |
| cancelled | Dibatalkan | Draft sale cancelled |
| refunded | Dipulangkan | Full refund processed |
| partially_refunded | Sebahagian Dipulangkan | Partial refund processed |

---

## Shift Statuses

| Status | Description |
|--------|-------------|
| open | Currently active shift |
| closed | Shift closed, awaiting reconciliation |
| locked | Shift locked, cannot be modified |

---

## Permissions

| Permission | Description | Roles |
|------------|-------------|-------|
| `pos.view` | View POS transactions, registers, shifts | tenant_owner, tenant_admin, finance_manager, cashier, staff |
| `pos.manage` | Manage POS transactions, cash shifts, registers | tenant_owner, tenant_admin |

---

## Business Logic

### Sale Lifecycle

1. **Create Draft** → `POST /sales/draft`
   - Requires register_id
   - Optional: customer_id, notes
   - Generates unique sale_no

2. **Add Items** → `POST /sales/{id}/items`
   - Can add multiple items
   - Supports product lookup or manual entry
   - Auto-recalculates totals

3. **Update/Remove Items** → `PUT/DELETE /sales/items/{id}`
   - Modify quantities/prices
   - Remove items entirely

4. **Complete Sale** → `POST /sales/{id}/complete`
   - Validates sale has items
   - Processes multiple payments
   - Auto-links to open cash shift
   - Deducts inventory for products
   - Fires `SaleCompleted` event

5. **Cancel Draft** → `POST /sales/{id}/cancel`
   - Only for draft status
   - Restores inventory if linked

6. **Refund** → `POST /sales/{id}/refund`
   - Creates return record
   - Restores inventory
   - Updates sale status

### Cash Shift Management

1. **Open Shift** → `POST /shifts/open`
   - Prevents duplicate open shifts
   - Default opening cash: RM200
   - Links to register

2. **Close Shift** → `POST /shifts/{id}/close`
   - Calculates expected cash from sales
   - Compares with actual closing cash
   - Computes discrepancy

3. **Lock Shift** → `POST /shifts/{id}/lock`
   - Prevents further modifications
   - Audit trail protection

### Inventory Integration

When a sale is completed:
- Stock levels are automatically deducted
- Products must exist in `inventory_products` table
- If product deleted/unavailable, sale still processes (manual entry)

### CRM Integration

- Optional customer linking during sale creation
- Enables loyalty points accumulation
- Purchase history tracking

---

## Services

### RegisterService

```php
// Create register
$register = $service->createRegister($data, $tenantId, $userId);

// Update register
$result = $service->updateRegister($id, $data, $tenantId);

// Toggle active status
$result = $service->toggleActive($id, $tenantId);

// Get sales summary
$summary = $service->getRegisterSalesSummary($id, $tenantId, $startDate, $endDate);
```

### CashShiftService

```php
// Open shift
$shift = $service->openShift($registerId, $userId, $openingCash, $tenantId);

// Get open shift
$shift = $service->getOpenShift($registerId, $tenantId);

// Close shift
$shift = $service->closeShift($shiftId, $closureData, $userId, $tenantId);

// Calculate expected cash
$expected = $service->calculateExpectedCash($shift);

// Lock shift
$result = $service->lockShift($shiftId, $userId, $tenantId);
```

### SaleService

```php
// Create draft sale
$sale = $service->createDraft($data, $tenantId, $registerId, $userId);

// Add item
$item = $service->addItem($saleId, $itemData, $tenantId);

// Update item
$item = $service->updateItem($itemId, $data, $tenantId);

// Remove item
$success = $service->removeItem($itemId, $tenantId);

// Complete sale
$sale = $service->completeSale($saleId, $completionData, $tenantId);

// Cancel sale
$success = $service->cancelSale($saleId, $tenantId);

// Refund sale
$return = $service->refundSale($saleId, $refundData, $tenantId, $userId);

// Get summary
$summary = $service->getSummary($tenantId, $startDate, $endDate);
```

---

## Events

| Event | Trigger | Payload |
|-------|---------|---------|
| `SaleCreated` | Draft sale created | sale_id, register_id, user_id |
| `SaleCompleted` | Sale finalized | sale_id, total_amount, payments[], shift_id |
| `SaleCancelled` | Draft cancelled | sale_id, reason |
| `SaleRefunded` | Refund processed | return_id, sale_id, amount |

---

## Configuration

File: `config/pos.php`

```php
'payment_methods' => [ ... ],  // Payment method labels
'sale_statuses' => [ ... ],    // Sale status labels
'shift_statuses' => [ ... ],   // Shift status labels
'cash_shift' => [
    'float_cash' => 200.00,    // Default opening cash
],
'tax' => [
    'default_rate' => 0.06,    // Default 6% SST
    'enabled' => true,
],
'discount' => [
    'max_percentage' => 50,    // Max discount 50%
],
'numbering' => [
    'sale_format' => 'SALE-YYYY-NNNNNN',
    'return_format' => 'RET-YYYY-NNNNNN',
    'register_format' => 'POS-REG-NNN',
],
```

---

## Testing

Run POS module tests:

```bash
php artisan test --filter=PosModuleTest
```

---

## Integration Points

- **B-Inventory**: Stock deduction on sale completion
- **B-CRM**: Customer linking for loyalty/history
- **B-Finance**: Revenue posting, payment reconciliation
- **B-Loyalty**: Points earning on completed sales
- **B-Document AI**: Receipt generation, OCR for returns

---

## Future Enhancements

- [ ] Barcode scanner integration
- [ ] Weight scale integration (for produce)
- [ ] Kitchen display system (KDS) for restaurants
- [ ] Queue management
- [ ] Customer-facing display
- [ ] Offline mode with sync
- [ ] Multiple tax rates per jurisdiction
- [ ] Tip/gratuity handling
- [ ] Void reason tracking
- [ ] Manager override approvals

---

## License

Part of AI BIJAN SaaS Platform. Proprietary license.
