# B-SCM — Supply Chain Management Module

## Overview
Modul pengurusan rantaian bekalan lengkap untuk AI-Bijan SaaS dengan ciri-ciri:
- Supplier management (daftar, kelulusan, prestasi)
- Purchase Order lifecycle (draft → approval → sent → received → closed)
- Goods Received Notes (GRN)
- Approval workflow untuk PO
- Integration dengan B-Finance (auto journal entries)

## Architecture

```
app/Modules/SCM/
├── SCMServiceProvider.php              # Service provider with bindings
├── Models/
│   ├── Supplier.php                    # Supplier master data
│   ├── SupplierContact.php             # Multiple contacts per supplier
│   ├── PurchaseOrder.php               # PO header
│   ├── PurchaseOrderLine.php           # PO line items
│   ├── PurchaseOrderApproval.php       # Approval workflow
│   ├── GoodsReceivedNote.php           # GRN header
│   └── GoodsReceivedNoteLine.php       # GRN line items
├── Services/
│   ├── PurchaseOrderService.php        # PO lifecycle management
│   └── SupplierManagementService.php   # Supplier CRUD & performance
├── Http/Controllers/
│   ├── SupplierController.php          # Supplier CRUD + approve
│   └── PurchaseOrderController.php     # PO CRUD + submit/approve/send/receive/cancel
├── database/migrations/
│   ├── 2026_10_19_000020_create_suppliers.php
│   └── 2026_10_19_000021_create_purchase_orders.php
└── routes/api.php                      # API endpoints
```

## Database Schema

### suppliers
Supplier master data dengan Malaysian compliance fields:
- Business registration (SSM, GST, SST numbers)
- Payment terms & bank details
- Withholding tax rate (WP 10/15/25)
- Performance tracking (on-time delivery, quality issues)
- Approval workflow (approved_by, approved_at)

### supplier_contacts
Multiple contacts per supplier:
- name, position, email, phone
- Primary contact flag

### purchase_orders
Purchase Order lifecycle:
- Status flow: draft → pending_approval → approved → sent → partially_received → fully_received
- Financial details (subtotal, tax/SST, shipping, total)
- Payment terms & due date calculation
- Shipping information & tracking
- Approval tracking (requested_by, approved_by, approved_at)
- Journal entry link (for auto accounting integration)

### purchase_order_lines
PO line items:
- Item reference (from inventory module)
- Quantity ordered/received
- Unit price & line total
- Taxable flag & SST rate
- Delivery status tracking

### purchase_order_approvals
Multi-level approval workflow:
- approver_id, status (pending/approved/rejected)
- Comments & decision timestamp
- Unique constraint per PO+approver

### goods_received_notes (GRN)
Goods receipt records:
- Links to source PO
- Received date & condition (good/damaged/short_shipped)
- Received by (warehouse staff)

### goods_received_note_lines
GRN line items:
- Ordered vs received vs rejected quantities
- Links to PO lines for traceability

## API Endpoints

### Suppliers
```
GET    /api/scm/suppliers              # List suppliers
GET    /api/scm/suppliers/{id}         # Get single supplier
POST   /api/scm/suppliers              # Create supplier
PUT    /api/scm/suppliers/{id}         # Update supplier
POST   /api/scm/suppliers/{id}/approve # Approve supplier
```

### Purchase Orders
```
GET    /api/scm/purchase-orders                # List POs
GET    /api/scm/purchase-orders/{id}           # Get single PO
POST   /api/scm/purchase-orders                # Create draft PO
POST   /api/scm/purchase-orders/{id}/submit    # Submit for approval
POST   /api/scm/purchase-orders/{id}/approve   # Approve PO
POST   /api/scm/purchase-orders/{id}/send      # Send to supplier
POST   /api/scm/purchase-orders/{id}/receive   # Receive goods (create GRN)
POST   /api/scm/purchase-orders/{id}/cancel    # Cancel PO
```

## Permissions

| Permission | Description |
|------------|-------------|
| `inventory.view` | View suppliers & purchase orders |
| `inventory.manage` | Manage suppliers, POs & receive goods |

## Roles

- **inventory_manager**: Full SCM access (view, manage, approve)
- **finance_manager**: View only
- **tenant_admin**: Full access

## Purchase Order Lifecycle

```
┌─────────┐     ┌──────────────┐     ┌──────────┐     ┌────────┐
│  Draft  │────▶│ Pending      │────▶│ Approved │────▶│ Sent   │
└─────────┘     └──────────────┘     └──────────┘     └────────┘
                                                   │
                                          ┌────────▼────────┐
                                          │ Partially       │
                                          │ Received        │
                                          └────────┬────────┘
                                                   │
                                          ┌────────▼────────┐
                                          │ Fully Received  │
                                          └────────┬────────┘
                                                   │
                                          ┌────────▼────────┐
                                          │   Closed        │
                                          └─────────────────┘

Alternative paths:
  Draft ──┐
          ▼
  Rejected (back to Draft)
  
  Any State ──▶ Cancelled
```

## Sample API Requests

### Create Supplier
```json
{
    "company_name": "ABC Supplies Sdn Bhd",
    "ssm_number": "202001012345",
    "gst_number": "GST123456789",
    "contact_person": "Ahmad bin Ali",
    "contact_email": "sales@abcsupplies.com.my",
    "contact_phone": "+603-1234-5678",
    "address": "No 12, Jalan Industri 1",
    "city": "Shah Alam",
    "state": "Selangor",
    "postcode": "40000",
    "category": "raw_materials",
    "payment_terms_days": 30,
    "currency": "MYR",
    "bank_name": "Maybank",
    "bank_account_name": "ABC Supplies Sdn Bhd",
    "bank_account_number": "1234-5678-9012",
    "withholding_tax_rate": 10.00,
    "primary_contact": {
        "name": "Ahmad bin Ali",
        "email": "ahmad@abcsupplies.com.my",
        "phone": "+6012-345-6789"
    }
}
```

### Create Purchase Order
```json
{
    "supplier_id": 1,
    "order_date": "2026-10-19",
    "expected_delivery_date": "2026-10-26",
    "shipping_cost": 50.00,
    "currency": "MYR",
    "payment_terms_days": 30,
    "internal_notes": "Urgent - production needed",
    "lines": [
        {
            "item_code": "RM-001",
            "item_name": "Steel Plate 10mm",
            "description": "MS Steel Plate, Grade 37",
            "quantity_ordered": 100,
            "unit_price": 45.00,
            "uom": "pcs",
            "is_taxable": true,
            "tax_rate": 6.00
        },
        {
            "item_code": "RM-002",
            "item_name": "Bolts M16",
            "quantity_ordered": 500,
            "unit_price": 2.50,
            "uom": "pcs",
            "is_taxable": true,
            "tax_rate": 6.00
        }
    ]
}
```

### Receive Goods
```json
{
    "received_items": [
        {
            "purchase_order_line_id": 1,
            "quantity_received": 100,
            "quantity_rejected": 0
        },
        {
            "purchase_order_line_id": 2,
            "quantity_received": 480,
            "quantity_rejected": 20
        }
    ]
}
```

## Integration Points

### From Inventory Module
When stock item is created:
```php
// Link PO line to inventory item
$poLine->item_id = $itemId;
$poLine->save();
```

### To Finance Module
When PO is approved:
```php
// Auto journal entry for accounting
$journalService->autoJournalFromEvent('purchase_order_confirmed', [
    'po_id' => $poId,
    'total_amount' => $totalAmount,
    'account_code' => '50100', // Purchase expense account
]);
```

When goods are received:
```php
// Update inventory stock levels
$inventoryService->receiveStock([
    'item_id' => $itemId,
    'quantity' => $receivedQuantity,
    'cost' => $unitPrice,
    'grn_id' => $grnId,
]);
```

When payment is made:
```php
// Auto journal entry for payment
$journalService->autoJournalFromEvent('payment_made', [
    'ap_id' => $apId,
    'amount' => $amount,
    'bank_account' => '10100',
]);
```

## Testing

Run tests:
```bash
php artisan test --filter=SCM
```

## Future Enhancements

- [ ] Request for Quotation (RFQ) module
- [ ] Compare quotations from multiple suppliers
- [ ] Drop shipment support
- [ ] Consignment stock management
- [ ] Vendor portal (self-service for suppliers)
- [ ] Automated PO emails to suppliers
- [ ] Three-way matching (PO ↔ GRN ↔ Invoice)
- [ ] Spend analysis reports
- [ ] Supplier scorecard dashboard
- [ ] Integration with Malaysian e-Invoicing system
