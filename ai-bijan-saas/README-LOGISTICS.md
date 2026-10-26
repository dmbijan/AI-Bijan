# B-Logistics — Warehouse, Stock Movement & Shipping Management

## Gambaran Keseluruhan

B-Logistics adalah modul pengurusan logistik yang mengendalikan warehouse operations, stock movements, dan shipping/delivery tracking. Modul ini mengintegrasikan dengan B-Inventory, B-SCM, B-Invoicing, dan B-Marketplace untuk end-to-end supply chain management.

## Ciri-ciri Utama

### 1. Multi-Warehouse Management
- Sokongan untuk multiple warehouses
- Default warehouse configuration
- Warehouse types (main, warehouse, store, distribution center)
- Location tracking dengan GPS coordinates
- Manager assignment dan contact info
- Capacity management

### 2. Stock Movement Tracking
- **Incoming** — Receive goods dari suppliers/purchase orders
- **Outgoing** — Dispatch goods ke customers/sales orders
- **Transfer** — Move stock between warehouses
- **Adjustment** — Stock adjustments (counting, damage, loss)
- **Return** — Customer returns dan restocking
- Lot/serial number tracking
- Batch number dan expiry date tracking
- Approval workflow untuk high-value moves

### 3. Shipment & Delivery
- Shipment creation dari orders
- Courier integration (DHL, J&N, GDEX, PosLaju, Ninja Van)
- Shipping label generation
- Real-time tracking via courier APIs
- Consignment note generation
- Proof of delivery capture
- Multiple package types (parcel, envelope, pallet, custom)

### 4. Shipping Cost Management
- Freight-in cost tracking
- Shipping cost allocation methods (weight, volume, value, equal)
- Insurance cost calculation
- Declared value tracking
- Currency support

### 5. Analytics & Reporting
- Stock levels across all warehouses
- Stock movement history dan trends
- Shipment statistics dan delivery performance
- Courier comparison (cost, speed, reliability)
- Warehouse utilization reports

## Struktur Database

### Tables

#### `logistics_warehouses`
Warehouse/storage location records.

```sql
- id (bigint, primary key)
- tenant_id (bigint, foreign key)
- name (string) — Warehouse name
- code (string, unique) — Warehouse code (e.g., WH-001)
- type (enum: main, warehouse, store, distribution_center, popup)
- address, city, state, postal_code, country
- phone, email
- manager_name, manager_phone
- is_active (boolean)
- is_default (boolean) — Primary warehouse
- latitude, longitude (decimal) — GPS location
- settings (json) — Custom settings per warehouse
- notes (text)
- created_at, updated_at
- deleted_at (soft delete)
```

#### `logistics_stock_moves`
Stock movement transactions.

```sql
- id (bigint, primary key)
- tenant_id (bigint, foreign key)
- warehouse_id (bigint, foreign key)
- product_id (bigint, foreign key)
- lot_number (string) — Batch/lot identifier
- serial_number (string) — Individual serial tracking
- quantity (decimal) — Positive for incoming, negative for outgoing
- unit_cost (decimal) — Cost per unit
- total_value (decimal) — quantity × unit_cost
- move_type (enum: incoming, outgoing, transfer, adjustment, return)
- reference_type (string) — Source document type
- reference_id (unsigned bigint) — Source document ID
- from_warehouse_id (bigint, FK) — For transfers
- to_warehouse_id (bigint, FK) — For transfers
- batch_number (string)
- expiry_date (date) — For perishable items
- moved_by (bigint, FK → users)
- approved_by (bigint, FK → users)
- status (enum: pending, completed, cancelled)
- moved_at (timestamp)
- notes (text)
- created_at, updated_at
- deleted_at (soft delete)
```

#### `logistics_shipments`
Shipping and delivery records.

```sql
- id (bigint, primary key)
- tenant_id (bigint, foreign key)
- shipment_number (string, unique) — Auto-generated (e.g., SHIP-2026-00001)
- order_type (enum: sales, purchase, transfer)
- order_id (unsigned bigint) — Related order ID
- from_warehouse_id (bigint, FK)
- to_warehouse_id (bigint, FK) — For transfers/purchases
- recipient_name, recipient_phone, recipient_email
- shipping_address, city, state, postal_code, country
- courier (string) — Courier provider
- service_type (string) — Service level
- tracking_number (string) — Courier tracking
- consignment_note (string)
- weight_kg, length_cm, width_cm, height_cm, volume_cm3
- package_type (enum: parcel, envelope, pallet, custom)
- number_of_packages (integer)
- declared_value (decimal)
- currency (string) — ISO currency code
- shipping_cost (decimal)
- insurance_cost (decimal)
- cost_allocation_method (string) — weight/volume/value/equal
- status (enum: created, label_generated, picked_up, in_transit, out_for_delivery, delivered, failed, returned, cancelled)
- estimated_delivery (timestamp)
- delivered_at (timestamp)
- signed_by (string) — Person who received
- proof_of_delivery (text) — POD notes/photos
- labels (json) — Generated shipping labels
- customs_documents (json) — For international shipments
- notes (text)
- created_at, updated_at
- deleted_at (soft delete)
```

## API Endpoints

### Warehouses Management

#### List Warehouses
```http
GET /api/v1/logistics/warehouses?active=true&type=warehouse&search=kuala+lumpur
Authorization: Bearer {token}
Permission: logistics.view
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tenant_id": 1,
      "name": "Main Warehouse",
      "code": "WH-MAIN",
      "type": "main",
      "address": "Lot 123, Industrial Park",
      "city": "Shah Alam",
      "state": "Selangor",
      "postal_code": "40000",
      "is_active": true,
      "is_default": true,
      "latitude": 3.0738,
      "longitude": 101.5183,
      "manager_name": "Ahmad Ali",
      "manager_phone": "+60123456789"
    }
  ]
}
```

#### Create Warehouse
```http
POST /api/v1/logistics/warehouses
Authorization: Bearer {token}
Permission: logistics.manage
Content-Type: application/json

{
  "name": "East Coast Warehouse",
  "code": "WH-EAST",
  "type": "warehouse",
  "address": "Lot 456, Kuantan Industrial",
  "city": "Kuantan",
  "state": "Pahang",
  "postal_code": "25000",
  "phone": "+609-1234567",
  "manager_name": "Mohd Razak",
  "is_active": true,
  "is_default": false,
  "latitude": 3.8077,
  "longitude": 103.3261
}
```

#### Get Primary Warehouse
```http
GET /api/v1/logistics/warehouses/primary
Authorization: Bearer {token}
Permission: logistics.view
```

#### Get Warehouse Statistics
```http
GET /api/v1/logistics/warehouses/stats
Authorization: Bearer {token}
Permission: logistics.view
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_warehouses": 3,
    "active_warehouses": 3,
    "total_stock_units": 15420,
    "warehouses_by_type": {
      "main": 1,
      "warehouse": 1,
      "store": 1
    }
  }
}
```

### Stock Moves

#### Receive Incoming Stock
```http
POST /api/v1/logistics/stock-moves/receive
Authorization: Bearer {token}
Permission: logistics.manage
Content-Type: application/json

{
  "warehouse_id": 1,
  "product_id": 42,
  "quantity": 100,
  "unit_cost": 15.50,
  "lot_number": "LOT-2026-001",
  "batch_number": "BN-20261020",
  "expiry_date": "2027-10-20",
  "reference_type": "purchase_order",
  "reference_id": 123,
  "notes": "Received from Supplier ABC"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "warehouse_id": 1,
    "product_id": 42,
    "quantity": 100,
    "unit_cost": 15.50,
    "total_value": 1550.00,
    "move_type": "incoming",
    "status": "completed",
    "moved_at": "2026-10-20T10:30:00Z"
  },
  "message": "Stock received successfully"
}
```

#### Dispatch Outgoing Stock
```http
POST /api/v1/logistics/stock-moves/dispatch
Authorization: Bearer {token}
Permission: logistics.manage
Content-Type: application/json

{
  "warehouse_id": 1,
  "product_id": 42,
  "quantity": 50,
  "lot_number": "LOT-2026-001",
  "reference_type": "sales_order",
  "reference_id": 456,
  "notes": "Dispatched for Order #456"
}
```

#### Get Available Stock
```http
GET /api/v1/logistics/stock-moves/product/{productId}/available?warehouse_id=1&lot_number=LOT-2026-001
Authorization: Bearer {token}
Permission: logistics.view
```

**Response:**
```json
{
  "success": true,
  "data": {
    "warehouse_id": 1,
    "product_id": 42,
    "lot_number": "LOT-2026-001",
    "available_quantity": 50
  }
}
```

#### Get Stock Levels Across Warehouses
```http
GET /api/v1/logistics/stock-moves/stock-levels?product_id=42
Authorization: Bearer {token}
Permission: logistics.view
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "warehouse_id": 1,
      "warehouse_name": "Main Warehouse",
      "product_id": 42,
      "total_quantity": 50,
      "avg_unit_cost": 15.50
    },
    {
      "warehouse_id": 2,
      "warehouse_name": "Store Bangsar",
      "product_id": 42,
      "total_quantity": 25,
      "avg_unit_cost": 15.50
    }
  ]
}
```

#### Get Stock Movement History
```http
GET /api/v1/logistics/stock-moves/warehouse/{warehouseId}?move_type=incoming&status=completed&date_from=2026-10-01&date_to=2026-10-20
Authorization: Bearer {token}
Permission: logistics.view
```

#### Approve Pending Stock Move
```http
POST /api/v1/logistics/stock-moves/{moveId}/approve
Authorization: Bearer {token}
Permission: logistics.manage
```

### Shipments

#### List Shipments
```http
GET /api/v1/logistics/shipments?status=in_transit&courier=dhl&order_type=sales
Authorization: Bearer {token}
Permission: logistics.view
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "shipment_number": "SHIP-2026-00001",
        "order_type": "sales",
        "order_id": 789,
        "from_warehouse_id": 1,
        "recipient_name": "Ali Bin Abu",
        "recipient_phone": "+60123456789",
        "shipping_address": "No 12, Jalan Merdeka",
        "shipping_city": "Kuala Lumpur",
        "shipping_state": "Wilayah Persekutuan",
        "shipping_postal_code": "50000",
        "courier": "dhl",
        "service_type": "express",
        "tracking_number": "DHL123456789",
        "weight_kg": 2.5,
        "package_type": "parcel",
        "number_of_packages": 1,
        "declared_value": 250.00,
        "shipping_cost": 15.00,
        "currency": "MYR",
        "status": "in_transit",
        "estimated_delivery": "2026-10-22T17:00:00Z",
        "created_at": "2026-10-20T10:00:00Z"
      }
    ],
    "meta": {
      "total": 50,
      "per_page": 50,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

#### Create Shipment
```http
POST /api/v1/logistics/shipments
Authorization: Bearer {token}
Permission: logistics.shipping
Content-Type: application/json

{
  "order_type": "sales",
  "order_id": 789,
  "from_warehouse_id": 1,
  "recipient_name": "Ali Bin Abu",
  "recipient_phone": "+60123456789",
  "recipient_email": "ali@example.com",
  "shipping_address": "No 12, Jalan Merdeka",
  "shipping_city": "Kuala Lumpur",
  "shipping_state": "Wilayah Persekutuan",
  "shipping_postal_code": "50000",
  "courier": "dhl",
  "service_type": "express",
  "weight_kg": 2.5,
  "length_cm": 30,
  "width_cm": 20,
  "height_cm": 15,
  "package_type": "parcel",
  "number_of_packages": 1,
  "declared_value": 250.00,
  "currency": "MYR"
}
```

#### Create Shipment from Order (Auto)
```http
POST /api/v1/logistics/shipments/from-order
Authorization: Bearer {token}
Permission: logistics.shipping
Content-Type: application/json

{
  "order_type": "sales",
  "order_id": 789,
  "from_warehouse_id": 1
}
```

This will:
1. Fetch order details from B-Invoicing/B-Marketplace
2. Create shipment record
3. Create stock moves for each item
4. Generate shipment number

#### Update Shipment Status
```http
PATCH /api/v1/logistics/shipments/{id}/status
Authorization: Bearer {token}
Permission: logistics.shipping
Content-Type: application/json

{
  "status": "delivered",
  "signed_by": "Ali Bin Abu",
  "proof_of_delivery": "Delivered in good condition"
}
```

**Status Workflow:**
```
created → label_generated → picked_up → in_transit → out_for_delivery → delivered
                                                                 ↓
                                                            failed → returned
                                                                 ↓
                                                        (retry or cancel)
```

#### Generate Shipping Label
```http
POST /api/v1/logistics/shipments/{id}/label
Authorization: Bearer {token}
Permission: logistics.shipping
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tracking_number": "DHL123456789",
    "labels": [
      {
        "type": "shipping_label",
        "url": "https://storage.aibijan.com/labels/SHIP-2026-00001.pdf",
        "format": "pdf"
      },
      {
        "type": "packing_slip",
        "url": "https://storage.aibijan.com/labels/SHIP-2026-00001-slip.pdf",
        "format": "pdf"
      }
    ]
  },
  "message": "Shipping label generated"
}
```

#### Track Shipment
```http
GET /api/v1/logistics/shipments/{id}/track
Authorization: Bearer {token}
Permission: logistics.view
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tracking_number": "DHL123456789",
    "courier": "dhl",
    "status": "in_transit",
    "events": [
      {
        "timestamp": "2026-10-20T14:00:00Z",
        "status": "in_transit",
        "location": "DHL Hub, Kuala Lumpur",
        "description": "Shipment processed at sorting facility"
      },
      {
        "timestamp": "2026-10-20T10:00:00Z",
        "status": "picked_up",
        "location": "Shah Alam",
        "description": "Picked up from sender"
      }
    ],
    "estimated_delivery": "2026-10-22T17:00:00Z"
  }
}
```

#### Get Shipment Statistics
```http
GET /api/v1/logistics/shipments/statistics?date_from=2026-10-01&date_to=2026-10-20
Authorization: Bearer {token}
Permission: logistics.view
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_shipments": 150,
    "delivered_shipments": 130,
    "in_transit_shipments": 15,
    "delivery_rate": 86.67,
    "total_shipping_cost": 2250.00,
    "avg_shipping_cost": 15.00,
    "by_courier": [
      {"courier": "dhl", "count": 80, "total_cost": 1200.00},
      {"courier": "jandeln", "count": 50, "total_cost": 600.00},
      {"courier": "gdex", "count": 20, "total_cost": 450.00}
    ],
    "by_status": [
      {"status": "delivered", "count": 130},
      {"status": "in_transit", "count": 15},
      {"status": "out_for_delivery", "count": 5}
    ]
  }
}
```

## Configuration

### config/logistics.php

```php
return [
    // Warehouse Settings
    'warehouse' => [
        'enable_multi_warehouse' => true,
        'default_warehouse_id' => null,
        'max_locations_per_warehouse' => 1000,
        'enable_bin_tracking' => true,
        'auto_create_default_warehouse' => true,
    ],

    // Stock Move Settings
    'stock_move' => [
        'enable_lot_tracking' => true,
        'enable_serial_tracking' => false,
        'enable_expiry_tracking' => false,
        'auto_reserve_on_order' => true,
        'reservation_expiration_hours' => 48,
        'allow_negative_stock' => false,
        'require_approval_above_value' => 10000, // MYR
    ],

    // Shipment Settings
    'shipment' => [
        'enable_courier_integration' => true,
        'default_courier' => 'dhl',
        'supported_couriers' => ['dhl', 'ups', 'fedex', 'jandeln', 'gdex', 'poslaju', 'ninja'],
        'auto_create_label_on_payment' => true,
        'tracking_prefix' => 'BIJAN',
        'max_weight_kg' => 50,
        'default_package_type' => 'parcel',
    ],

    // Courier API Integration
    'courier' => [
        'dhl' => [
            'api_url' => env('DHL_API_URL'),
            'account_number' => env('DHL_ACCOUNT_NUMBER'),
            'api_key' => env('DHL_API_KEY'),
            'is_test_mode' => true,
        ],
        'jandeln' => [
            'api_url' => env('JANDельN_API_URL'),
            'account_number' => env('JANDельN_ACCOUNT_NUMBER'),
            'api_key' => env('JANDельN_API_KEY'),
            'is_test_mode' => true,
        ],
    ],

    // Shipping Cost Policy
    'shipping_cost' => [
        'allocation_method' => 'weight', // weight, volume, value, equal
        'include_freight_in' => true,
        'freight_in_allocation' => 'cost_of_goods',
    ],
];
```

## Integration Points

### B-Inventory Integration
- **Stock Sync** — B-Logistics stock moves sync dengan B-Inventory levels
- **Product Catalog** — Products shared dari B-Inventory
- **Low Stock Alerts** — Triggers reorder dalam B-Inventory

### B-SCM (Supply Chain Management) Integration
- **Purchase Orders** — PO receipt creates incoming stock moves
- **Supplier Deliveries** — Track supplier deliveries sebagai stock moves
- **Freight-in Costs** — Inbound freight allocated ke COGS

### B-Invoicing Integration
- **Sales Orders** — SO triggers shipment creation
- **Invoice Generation** — Shipment confirmation triggers invoice
- **Shipping Costs** — Added ke invoice as line item

### B-Marketplace Integration
- **Order Fulfillment** — Marketplace orders auto-create shipments
- **Multi-channel Sync** — Unified inventory across channels
- **Tracking Updates** — Customer notification on delivery

### B-Analytics Integration
- **Performance Metrics** — Shipment times, delivery rates
- **Cost Analysis** — Shipping costs vs revenue
- **Forecasting** — Predictive stock requirements

## Workflow Examples

### 1. Receive Goods from Supplier
```
Purchase Order Created → Goods Received → Stock Move (incoming)
→ Update warehouse stock → Notify purchaser → Close PO
```

### 2. Process Sales Order
```
Sales Order → Check stock availability → Reserve stock
→ Create Shipment → Generate label → Pick & pack
→ Mark as picked up → Update stock (outgoing)
→ Ship → Track → Deliver → Update POD
```

### 3. Transfer Between Warehouses
```
Transfer Request → Approve → Outgoing from A
→ In-transit → Incoming to B → Update both warehouses
```

### 4. Stock Adjustment
```
Physical Count → Discrepancy Found → Adjustment Record
→ Approve (if above threshold) → Update system stock
```

### 5. Courier Integration Flow
```
Create Shipment → Call courier API → Generate label
→ Print label → Attach to package → Handover to courier
→ Webhook/callback → Update status → Notify customer
```

## Architecture

```
┌─────────────────────────────────────────────────┐
│           Warehouse Operations                  │
│  Receive | Dispatch | Transfer | Adjust         │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│          StockMoveService                       │
│   - Validate stock availability                 │
│   - Create stock moves                          │
│   - Calculate totals                            │
│   - Handle approvals                            │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│       Inventory Sync Layer                      │
│   (Sync with B-Inventory product stock)         │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│          ShipmentService                        │
│   - Create shipments                            │
│   - Integrate with couriers                     │
│   - Track deliveries                            │
│   - Generate labels                             │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│       Courier Adapter Layer                     │
│  DHL | J&N | GDEX | PosLaju | Ninja Van        │
└─────────────────────────────────────────────────┘
```

## Testing

### Unit Tests
```bash
php artisan test --filter=LogisticsTest
```

### Test Stock Receipt
```bash
curl -X POST http://localhost/api/v1/logistics/stock-moves/receive \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "warehouse_id": 1,
    "product_id": 42,
    "quantity": 100,
    "unit_cost": 15.50,
    "reference_type": "purchase_order",
    "reference_id": 123
  }'
```

### Test Shipment Creation
```bash
curl -X POST http://localhost/api/v1/logistics/shipments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "order_type": "sales",
    "order_id": 789,
    "from_warehouse_id": 1,
    "recipient_name": "John Doe",
    "recipient_phone": "+60123456789",
    "shipping_address": "123 Main St",
    "shipping_city": "Kuala Lumpur",
    "shipping_state": "Wilayah Persekutuan",
    "shipping_postal_code": "50000",
    "courier": "dhl",
    "weight_kg": 1.5
  }'
```

### Test Stock Transfer
```bash
curl -X POST http://localhost/api/v1/logistics/stock-moves/receive \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "warehouse_id": 2,
    "product_id": 42,
    "quantity": 50,
    "unit_cost": 15.50,
    "move_type": "transfer",
    "from_warehouse_id": 1,
    "to_warehouse_id": 2,
    "reference_type": "transfer",
    "reference_id": 1
  }'
```

## Security Considerations

1. **Permission Control** — Role-based access ke warehouse/stock operations
2. **Audit Trail** — All stock moves logged dengan user/IP
3. **Approval Workflows** — High-value moves require approval
4. **Data Isolation** — Tenant-isolated data access
5. **Stock Integrity** — Transaction-safe operations dengan rollback
6. **Negative Stock Prevention** — Configurable protection

## Future Enhancements

- [ ] Barcode/QR code scanning integration
- [ ] RFID tracking support
- [ ] Automated reordering based on min/max levels
- [ ] Cross-border customs documentation
- [ ] Drone/robot warehouse automation support
- [ ] Real-time GPS fleet tracking
- [ ] Route optimization for deliveries
- [ ] IoT sensor integration (temperature, humidity)
- [ ] Advanced analytics dengan ML forecasting
- [ ] Mobile app for warehouse workers
