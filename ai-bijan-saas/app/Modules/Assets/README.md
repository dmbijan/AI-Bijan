# B-Assets — Asset Management Module

## Overview

**B-Assets** adalah modul pengurusan aset tetap lengkap untuk perniagaan Malaysia. Modul ini mengurus kitar hayat aset dari pembelian hingga pelupusan, termasuk pengiraan susut nilai (depreciation), penyelenggaraan (maintenance), pemindahan (transfers), dan dokumen sokongan.

### Ciri-ciri Utama

- **Asset Lifecycle Management** — Pengurusan penuh dari purchased → active → maintenance → disposed
- **Depreciation Calculation** — 4 kaedah: Straight Line, Declining Balance, Sum of Years Digits, Units of Production
- **Maintenance Management** — Preventive, corrective, predictive & emergency maintenance
- **Warranty Tracking** — Pantau tempoh waranti setiap aset
- **Transfer History** — Rekod semua pemindahan aset antara pengguna & lokasi
- **Document Management** — Upload manual, sijil, waranti, insurance & foto
- **Disposal Approval** — Workflow kelulusan untuk pelupusan aset
- **Barcode/Serial Search** — Cari aset dengan pantas menggunakan barcode atau nombor siri

---

## Database Schema

### Tables Created (8 tables)

```
asset_categories          # Kategori aset (equipment, vehicles, furniture, dll)
assets                    # Rekod aset tetap
depreciation_schedules    # Jadual susut nilai bulanan/tahunan
maintenance_requests      # Permintaan penyelenggaraan
maintenance_parts         # Alat ganti & bahan digunakan
asset_transfers           # Sejarah pemindahan aset
asset_documents           # Dokumen sokongan aset
asset_disposals           # Kelulusan pelupusan aset
```

### Key Relationships

```
asset_categories
    └── assets (category_id)
            ├── depreciation_schedules (asset_id)
            ├── maintenance_requests (asset_id)
            │       └── maintenance_parts (maintenance_request_id)
            ├── asset_transfers (asset_id)
            ├── asset_documents (asset_id)
            └── asset_disposals (asset_id)
```

---

## API Endpoints

### Base URL: `/api/v1/assets`

#### Asset Categories

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/categories` | `assets.view` | Senarai semua kategori |
| GET | `/categories/{id}` | `assets.view` | Lihat butiran kategori |
| POST | `/categories` | `assets.manage` | Cipta kategori baru |
| PUT | `/categories/{id}` | `assets.manage` | Kemaskini kategori |
| DELETE | `/categories/{id}` | `assets.manage` | Padam kategori |

#### Assets

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/` | `assets.view` | Senarai semua aset |
| GET | `/summary` | `assets.view` | Statistik ringkasan aset |
| GET | `/search` | `assets.view` | Cari mengikut barcode/serial |
| GET | `/{id}` | `assets.view` | Lihat butiran aset |
| POST | `/` | `assets.manage` | Cipta aset baru |
| PUT | `/{id}` | `assets.manage` | Kemaskini aset |
| DELETE | `/{id}` | `assets.manage` | Padam aset |
| POST | `/{id}/assign` | `assets.manage` | Tugaskan aset ke pengguna |
| POST | `/{id}/unassign` | `assets.manage` | Buang tugasan aset |
| POST | `/{id}/documents` | `assets.manage` | Mu naik dokumen aset |

#### Maintenance Requests

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/maintenance` | `assets.view` | Senarai permintaan penyelenggaraan |
| GET | `/maintenance/summary` | `assets.view` | Statistik penyelenggaraan |
| GET | `/maintenance/upcoming` | `assets.view` | Jadual preventive maintenance |
| GET | `/maintenance/{id}` | `assets.view` | Lihat butiran permintaan |
| GET | `/maintenance/asset/{assetId}/history` | `assets.view` | Sejarah maintenance aset |
| POST | `/maintenance` | `assets.manage` | Cipta permintaan baru |
| PUT | `/maintenance/{id}` | `assets.manage` | Kemaskini permintaan |
| DELETE | `/maintenance/{id}` | `assets.manage` | Padam permintaan |
| POST | `/maintenance/{id}/complete` | `assets.manage` | Selesaikan permintaan |
| POST | `/maintenance/{id}/cancel` | `assets.manage` | Batalkan permintaan |
| POST | `/maintenance/{id}/assign-technician` | `assets.manage` | Tugaskan teknisi |
| POST | `/maintenance/{id}/parts` | `assets.manage` | Tambah alat ganti/bahan |
| DELETE | `/maintenance/parts/{partId}` | `assets.manage` | Padam alat ganti |

---

## Models

### AssetCategory

```php
// Generate category code
$code = AssetCategory::generateCode('Computer Equipment');
// Returns: CAT-2026-0001

// Scopes
AssetCategory::forTenant($tenantId)->active()->get();
```

### Asset

```php
// Generate asset number
$no = Asset::generateAssetNo();
// Returns: AST-2026-0001

// Check warranty status
$asset->isUnderWarranty(); // bool

// Check if active
$asset->isActive(); // bool

// Calculate book value
$bookValue = $asset->calculateBookValue(); // float

// Check maintenance needs
$asset->needsMaintenanceSoon(30); // bool - needs maintenance within 30 days?

// Scopes
Asset::forTenant($tenantId)
    ->active()
    ->byStatus('active')
    ->byCategory($categoryId)
    ->get();
```

### MaintenanceRequest

```php
// Check overdue
$request->isOverdue(); // bool

// Get total cost including parts
$totalCost = $request->total_cost; // float

// Scopes
MaintenanceRequest::forTenant($tenantId)
    ->byStatus('scheduled')
    ->byType('preventive')
    ->get();
```

---

## Services

### AssetService

```php
// List assets with filters
$service->listAssets($tenantId, [
    'status' => 'active',
    'category_id' => 5,
    'search' => 'laptop',
    'location' => 'KL Office'
]);

// Create asset
$asset = $service->createAsset([
    'name' => 'Dell Laptop i7',
    'purchase_price' => 4500.00,
    'purchase_date' => '2026-01-15',
    'useful_life_years' => 5,
    'depreciation_method' => 'straight_line',
], $tenantId, $userId);

// Assign to user
$transfer = $service->assignAsset($assetId, $userId, 'KL Office', $tenantId, $requesterId);

// Upload document
$document = $service->uploadDocument($assetId, [
    'file' => $request->file('manual'),
    'type' => 'manual',
], $tenantId, $userId);

// Get summary
$summary = $service->getSummary($tenantId);
// Returns: total_assets, active_assets, total_value, total_book_value, etc.

// Search by identifier
$asset = $service->searchByIdentifier('BARCODE123');
```

### DepreciationService

```php
// Generate depreciation schedule for asset
$service->generateDepreciationSchedule($asset);

// Calculate depreciation
$schedule = $service->calculateDepreciation($asset);

// Post depreciation for a year
$count = $service->postDepreciation($assetId, 2026, $tenantId);

// Get depreciation for period
$total = $service->getDepreciationForPeriod($tenantId, 2026, 1);
```

### MaintenanceService

```php
// List maintenance requests
$service->listRequests($tenantId, [
    'status' => 'scheduled',
    'type' => 'preventive',
    'priority' => 'high'
]);

// Create maintenance request
$request = $service->createRequest([
    'asset_id' => 1,
    'type' => 'preventive',
    'priority' => 'medium',
    'title' => 'Annual Service',
    'description' => 'Full inspection and servicing',
    'scheduled_date' => '2026-12-01',
], $tenantId, $userId);

// Add parts/materials
$part = $service->addPart($requestId, [
    'part_name' => 'Oil Filter',
    'part_number' => 'OF-1234',
    'quantity' => 2,
    'unit_cost' => 25.00,
], $tenantId);

// Complete maintenance
$service->completeRequest($requestId, [
    'resolution' => 'Replaced oil filter and checked brakes',
    'actual_cost' => 150.00,
    'downtime_hours' => 2.5,
], $tenantId);

// Get upcoming preventive maintenance
$upcoming = $service->getUpcomingPreventive($tenantId, 30);

// Get asset maintenance history
$history = $service->getAssetHistory($assetId, $tenantId);
// Returns: total_maintenance, total_cost, total_downtime, requests
```

---

## Configuration

File: `config/assets.php`

```php
return [
    // Asset categories
    'categories' => [
        'equipment', 'vehicles', 'furniture', 'electronics',
        'buildings', 'land', 'machinery', 'software', 'intangible',
    ],

    // Depreciation methods
    'depreciation_methods' => [
        'straight_line' => 'Straight Line',
        'declining_balance' => 'Declining Balance',
        'units_of_production' => 'Units of Production',
        'sum_years_digits' => 'Sum of Years Digits',
    ],

    // Default settings
    'default_depreciation' => [
        'method' => 'straight_line',
        'useful_life_years' => 10,
        'salvage_value_percentage' => 10,
    ],

    // Maintenance types
    'maintenance' => [
        'types' => ['preventive', 'corrective', 'predictive', 'emergency'],
        'priorities' => ['low', 'medium', 'high', 'critical'],
        'statuses' => ['scheduled', 'in_progress', 'completed', 'cancelled'],
    ],
];
```

---

## Permissions & Roles

### Permissions

| Permission | Description |
|------------|-------------|
| `assets.view` | Lihat aset |
| `assets.manage` | Urus aset, susut nilai & pelupusan |

### Role Assignments

| Role | Permissions |
|------|-------------|
| `tenant_owner` | `*` (all permissions) |
| `tenant_admin` | `assets.view`, `assets.manage` |
| `finance_manager` | `assets.view`, `assets.manage` |
| `auditor` | `assets.view` |
| `staff` | (not assigned by default) |

---

## Depreciation Methods

### 1. Straight Line

```
Annual Depreciation = (Purchase Price - Salvage Value) / Useful Life
Monthly Depreciation = Annual / 12
```

### 2. Declining Balance

```
Rate = 2 / Useful Life (Double Declining)
Annual Depreciation = Current Book Value × Rate
```

### 3. Sum of Years Digits

```
SYD = Life × (Life + 1) / 2
Annual Depreciation = (Depreciable Amount × Remaining Life) / SYD
```

### 4. Units of Production

```
Per Unit = (Purchase Price - Salvage Value) / Total Expected Units
Depreciation = Units Produced × Per Unit Rate
```

---

## Usage Examples

### Create Asset with Full Details

```php
$asset = app(\App\Modules\Assets\Services\AssetService::class)->createAsset([
    'name' => 'Toyota HiAce Van',
    'serial_number' => 'ABCD1234',
    'barcode' => 'BARCODE-VAN-001',
    'category_id' => 2, // Vehicles
    'purchase_price' => 125000.00,
    'purchase_date' => '2026-01-10',
    'vendor_id' => 15, // Supplier
    'warranty_provider' => 'Toyota Motor Sales',
    'warranty_start' => '2026-01-10',
    'warranty_end' => '2031-01-10',
    'salvage_value' => 12500.00, // 10%
    'useful_life_years' => 10,
    'depreciation_method' => 'straight_line',
], $tenantId, $userId);

// Auto-generates depreciation schedule for 10 years
```

### Schedule Preventive Maintenance

```php
$maintenance = app(\App\Modules\Assets\Services\MaintenanceService::class)->createRequest([
    'asset_id' => $asset->id,
    'type' => 'preventive',
    'priority' => 'medium',
    'title' => 'Annual Vehicle Service',
    'description' => 'Full inspection, oil change, brake check',
    'scheduled_date' => '2027-01-10',
    'estimated_cost' => 800.00,
], $tenantId, $userId);
```

### Assign Asset to Employee

```php
$transfer = app(\App\Modules\Assets\Services\AssetService::class)->assignAsset(
    $asset->id,
    $employeeId,
    'Branch Office Penang',
    $tenantId,
    $managerId
);
```

### Get Asset Summary Report

```php
$summary = app(\App\Modules\Assets\Services\AssetService::class)->getSummary($tenantId);
/*
Returns:
[
    'total_assets' => 150,
    'active_assets' => 120,
    'in_maintenance' => 5,
    'stored_assets' => 15,
    'disposed_assets' => 10,
    'total_value' => 2500000.00,
    'total_book_value' => 1800000.00,
    'total_depreciation' => 700000.00,
    'under_warranty' => 45,
    'categories' => [1 => 30, 2 => 25, ...]
]
*/
```

---

## Malaysian Compliance Notes

- **Pembelian Aset**: Rekod harga beli, tarikh, dan vendor untuk tujuan tuntutan CPA/MT
- **Susut Nilai**: Kiraan susut nilai untuk tujuan akauntan dan laporan kewangan
- **Waranti**: Pantau tempoh waranti untuk tuntutan jaminan
- **Pelupusan**: Workflow kelulusan untuk pelupusan aset tetap (>RM1000 perlu kelulusan finance)

---

## File Structure

```
app/Modules/Assets/
├── AssetsServiceProvider.php         # Service provider with bindings
├── README.md                         # This documentation
├── config/
│   └── assets.php                    # Module configuration
├── Models/
│   ├── AssetCategory.php             # Asset categories
│   ├── Asset.php                     # Main asset model
│   ├── DepreciationSchedule.php      # Depreciation records
│   ├── MaintenanceRequest.php        # Maintenance requests
│   ├── MaintenancePart.php           # Parts/materials used
│   ├── AssetTransfer.php             # Transfer history
│   ├── AssetDocument.php             # Documents & photos
│   └── AssetDisposal.php             # Disposal approvals
├── Services/
│   ├── AssetService.php              # CRUD, assign, upload, search
│   ├── DepreciationService.php       # Depreciation calculations
│   └── MaintenanceService.php        # Maintenance workflow
├── Http/Controllers/
│   ├── AssetController.php           # Asset endpoints
│   ├── AssetCategoryController.php   # Category endpoints
│   └── MaintenanceController.php     # Maintenance endpoints
├── database/migrations/
│   └── 2026_10_19_000400_create_assets_tables.php
└── routes/
    └── api.php                       # Module-specific routes
```

---

## Testing

Run migration:
```bash
php artisan migrate
```

Test asset creation:
```bash
curl -X POST /api/v1/assets \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Asset",
    "purchase_price": 1000,
    "purchase_date": "2026-01-01"
  }'
```

---

## Future Enhancements

- [ ] Integration with B-Finance for asset purchase journal entries
- [ ] Barcode/QR code generation for physical tagging
- [ ] Insurance policy tracking
- [ ] Location-based alerts (asset moved outside approved zone)
- [ ] IoT sensor integration for predictive maintenance
- [ ] Tax depreciation calculation (different from accounting)
- [ ] Lease vs buy analysis
- [ ] Asset replacement scheduling
