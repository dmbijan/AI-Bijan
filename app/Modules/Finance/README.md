# B-Finance — Financial Management Module

## Overview
Modul kewangan lengkap untuk AI-Bijan SaaS dengan ciri-ciri:
- Chart of Accounts (COA) - Standard Malaysian format
- Double-entry bookkeeping system
- Journal Entries (manual & auto from other modules)
- Accounts Payable & Receivable
- Fixed Assets & Depreciation
- Financial Reports (P&L, Balance Sheet, Trial Balance, General Ledger)

## Architecture

```
app/Modules/Finance/
├── FinanceServiceProvider.php          # Service provider with bindings
├── Models/
│   ├── ChartOfAccount.php              # Chart of accounts hierarchy
│   ├── JournalEntry.php                # Journal entry header
│   └── JournalEntryLine.php            # Debit/credit lines
├── Services/
│   ├── JournalService.php              # Core journal operations
│   └── FinancialReportService.php      # Report generation engine
├── Http/Controllers/
│   ├── ChartOfAccountsController.php   # COA CRUD
│   ├── JournalController.php           # Journal entries CRUD + post/cancel
│   └── FinancialReportController.php   # P&L, Balance Sheet, Trial Balance
├── database/migrations/
│   ├── 2026_10_19_000010_create_chart_of_accounts.php
│   ├── 2026_10_19_000011_create_journal_entries.php
│   ├── 2026_10_19_000012_create_accounts_payable_receivable.php
│   └── 2026_10_19_000013_create_fixed_assets.php
└── routes/api.php                      # API endpoints
```

## Database Schema

### chart_of_accounts
Standard Malaysian chart of accounts with hierarchical structure:
- **1xxx** = Assets (Aset)
- **2xxx** = Liabilities (Kewajipan)
- **3xxx** = Equity (Ekuiti)
- **4xxx** = Revenue (Pendapatan)
- **5xxx** = Cost of Goods Sold (Harga Pokok Jualan)
- **6xxx** = Operating Expenses (Perbelanjaan Operasi)

### journal_entries
Double-entry bookkeeping:
- Each entry must balance (total debits = total credits)
- Supports multi-currency
- Links to source documents (Payroll, SCM, CRM, etc.)
- Approval workflow (draft → posted → cancelled)

### journal_entry_lines
Debit/credit lines per entry:
- account_id → chart_of_accounts
- debit amount / credit amount
- cost center / project tracking
- foreign currency support

### accounts_payable
Supplier invoices tracking:
- Invoice number, dates, amounts
- Payment status (pending, partial, fully paid, overdue)
- Withholding tax tracking
- Links to journal entries

### accounts_receivable
Customer invoices tracking:
- Invoice number, dates, amounts
- Aging buckets (current, 1-30, 31-60, 61-90, 91-180, 181+)
- Bad debt tracking
- Links to journal entries

### payments
Payment records (polymorphic):
- Can link to accounts_payable or accounts_receivable
- Multiple payment methods (cheque, wire transfer, e-wallet, etc.)
- Bank reconciliation support

### fixed_assets
Asset management:
- Acquisition cost tracking
- Multiple depreciation methods (straight_line, reducing_balance, units_of_production)
- Malaysian tax treatment (initial allowance per Income Tax Act 1967)
- Disposal tracking (gain/loss calculation)

### depreciation_schedule
Depreciation schedule per asset:
- Year-by-year breakdown
- Opening/closing net book value
- Posting status to journal entries

## API Endpoints

### Chart of Accounts
```
GET    /api/finance/chart-of-accounts              # List accounts
GET    /api/finance/chart-of-accounts/{id}         # Get single account
POST   /api/finance/chart-of-accounts              # Create account
PUT    /api/finance/chart-of-accounts/{id}         # Update account
```

### Journal Entries
```
GET    /api/finance/journal-entries                # List entries
GET    /api/finance/journal-entries/{id}           # Get single entry
POST   /api/finance/journal-entries                # Create draft entry
POST   /api/finance/journal-entries/{id}/post      # Post (approve) entry
POST   /api/finance/journal-entries/{id}/cancel    # Cancel entry
```

### Financial Reports
```
GET    /api/finance/reports/profit-loss?date_from=&date_to=
GET    /api/finance/reports/balance-sheet?as_of_date=
GET    /api/finance/reports/trial-balance?date_from=&date_to=
GET    /api/finance/reports/general-ledger/{accountId}?date_from=&date_to=
```

## Permissions

| Permission | Description |
|------------|-------------|
| `finance.view` | View accounts, journals & financial reports |
| `finance.manage` | Manage chart of accounts, journals & transactions |
| `finance.approve` | Approve journals and financial transactions |
| `finance.reports` | Access financial reports (P&L, Balance Sheet, Trial Balance) |

## Roles

- **finance_manager**: Full finance access (view, manage, approve, reports)
- **tenant_admin**: View only
- **auditor**: View only (reports)

## Integration Points

### From Payroll Module
When payroll run is finalized:
```php
// Auto journal entry for payroll
$journalService->autoJournalFromEvent('payroll_run_completed', [
    'payroll_run_id' => $runId,
    'employee_totals' => [...],
    'statutory_totals' => [...],
]);
```

### From SCM Module
When purchase order confirmed:
```php
$journalService->autoJournalFromEvent('purchase_order_confirmed', [
    'po_id' => $poId,
    'amount' => $amount,
    'account_code' => '60100', // Expense account
]);
```

When invoice received:
```php
$journalService->autoJournalFromEvent('invoice_received', [
    'supplier_invoice' => 'SUP-12345',
    'amount' => 1000.00,
    'ap_id' => $apId,
]);
```

### From Sales Module
When customer invoice issued:
```php
$journalService->autoJournalFromEvent('invoice_issued', [
    'customer_invoice' => 'INV-12345',
    'amount' => 500.00,
    'ar_id' => $arId,
]);
```

When payment received:
```php
$journalService->autoJournalFromEvent('payment_received', [
    'ar_id' => $arId,
    'amount' => 500.00,
    'bank_account' => '10100',
]);
```

## Sample Journal Entry

### Manual Entry Example
```json
{
    "entry_date": "2026-10-19",
    "reference_number": "JE-MANUAL-001",
    "description": "Office supplies purchase",
    "lines": [
        {
            "account_id": 60100,
            "debit": 150.00,
            "credit": 0
        },
        {
            "account_id": 10100,
            "debit": 0,
            "credit": 150.00
        }
    ]
}
```

### Payroll Run Completion Example
```json
{
    "entry_date": "2026-10-19",
    "source_module": "payroll",
    "source_id": "PR-2026-001",
    "description": "Monthly payroll - October 2026",
    "lines": [
        {"account_id": 60100, "debit": 5000.00, "credit": 0},     // Salary Expense
        {"account_id": 60200, "debit": 500.00, "credit": 0},      // Overtime Expense
        {"account_id": 20100, "debit": 0, "credit": 550.00},      // EPF Employee Payable
        {"account_id": 20200, "debit": 0, "credit": 100.00},      // PERKESO Employee Payable
        {"account_id": 20300, "debit": 0, "credit": 200.00},      // PCB Tax Payable
        {"account_id": 10100, "debit": 0, "credit": 3650.00}      // Bank (Net Pay)
    ]
}
```

## Financial Reports

### Profit & Loss Statement Format
```
Revenue / Pendapatan
  Sales Revenue              RM 50,000.00
  Other Income               RM 2,000.00
  Total Revenue              RM 52,000.00

Cost of Goods Sold / Harga Pokok Jualan
  Direct Materials           RM 15,000.00
  Direct Labor               RM 8,000.00
  Total COGS                 RM 23,000.00

Gross Profit / Untung Kasar  RM 29,000.00

Operating Expenses / Perbelanjaan Operasi
  Salaries                   RM 10,000.00
  Rent                       RM 2,000.00
  Utilities                  RM 500.00
  Total OpEx                 RM 12,500.00

Net Profit Before Tax        RM 16,500.00
Tax Expense                  RM 2,500.00

Net Profit After Tax         RM 14,000.00
```

### Balance Sheet Format
```
Assets / Aset
  Current Assets / Aset Semasa
    Cash                     RM 25,000.00
    Bank                     RM 75,000.00
    Accounts Receivable      RM 15,000.00
    Inventory                RM 10,000.00
    Total Current Assets     RM 125,000.00
  
  Non-Current Assets / Aset Bukan Semasa
    Equipment (net)          RM 30,000.00
    Vehicles (net)           RM 20,000.00
    Total Non-Current Assets RM 50,000.00

Total Assets                 RM 175,000.00

Liabilities / Kewajipan
  Current Liabilities
    Accounts Payable         RM 8,000.00
    EPF Payable              RM 1,500.00
    Tax Payable              RM 3,000.00
    Total Current Liab.      RM 12,500.00

  Non-Current Liabilities
    Long-term Loan           RM 50,000.00
    Total Non-Current Liab.  RM 50,000.00

Total Liabilities            RM 62,500.00

Equity / Ekuiti
  Share Capital              RM 100,000.00
  Retained Earnings          RM 12,500.00
  Total Equity               RM 112,500.00

Total Liabilities & Equity   RM 175,000.00 ✓
```

## Testing

Run tests:
```bash
php artisan test --filter=Finance
```

## Future Enhancements

- [ ] Multi-currency support (full implementation)
- [ ] Budget vs Actual comparison
- [ ] Cash flow statement
- [ ] VAT/SST reporting (Malaysian tax compliance)
- [ ] Bank reconciliation module
- [ ] Fixed assets disposal automation
- [ ] Inter-company accounting (multi-tenant group)
- [ ] Export to Excel/PDF
- [ ] Integration with Malaysian banking APIs (FPX, DuitNow)
