# B-Industry Packs — Pre-configured Industry Templates

## Overview

Industry Packs provide ready-to-use configuration profiles for different industries. When a tenant signs up, they select their industry and AI-Bijan automatically configures:

- **Dashboard layouts** with industry-specific widgets
- **Pre-configured metrics** (B-Analytics integration)
- **Default cost components** (B-COGS Engine integration)
- **Document templates** (B-Sign & Docs integration)
- **Workflow templates** (module-specific workflows)
- **Module enablement** (only relevant modules activated)

## Supported Industries

| Key | Name | Enabled Modules |
|-----|------|----------------|
| `retail` | Retail & E-commerce | POS, Marketplace, Inventory, Loyalty, Affiliate, Marketing |
| `manufacturing` | Manufacturing | SCM, Manufacturing, Inventory, Projects, Assets, COGS |
| `services` | Services & Consulting | Projects, HRMS, Timesheet, CRM, Virtual Office |
| `healthcare` | Healthcare & Medical | Booking, CRM, Inventory, Finance, HRMS |
| `food_beverage` | Food & Beverage | POS, Inventory, COGS, Loyalty, Marketing |
| `construction` | Construction & Building | Projects, Assets, Inventory, HRMS, CRM |
| `logistics` | Logistics & Transport | Logistics, Fleet, Inventory, CRM, Finance |
| `education` | Education & Training | Booking, CRM, Finance, Documents, Site |
| `real_estate` | Real Estate & Property | CRM, Booking, Finance, Documents, Site |
| `agriculture` | Agriculture & Farming | Inventory, Projects, CRM, Logistics, Finance |

## API Endpoints

### View Access (`/api/v1/industry-packs/*`)

#### List All Available Industries
```http
GET /api/v1/industry-packs/list
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "key": "retail",
      "name": "Retail & E-commerce",
      "description": "Retail stores, online shops, marketplaces",
      "enabled_modules": ["pos", "marketplace", "inventory", "loyalty", "affiliate", "marketing"],
      "default_dashboard": "retail"
    }
  ]
}
```

#### Get Industry Details
```http
GET /api/v1/industry-packs/{industryKey}/details
```

**Response:**
```json
{
  "success": true,
  "data": {
    "info": { "name": "...", "description": "...", ... },
    "metrics": [
      { "name": "Daily Sales", "key": "daily_sales", "type": "sum", "source": "sales_orders" }
    ],
    "cost_components": ["raw_materials", "packaging", "shipping_in"],
    "templates": ["receipt", "invoice", "return_receipt"],
    "workflows": ["point_of_sale", "inventory_reorder"],
    "dashboard": { "widgets": [...] }
  }
}
```

#### Get Dashboard Layout
```http
GET /api/v1/industry-packs/{industryKey}/dashboard
```

#### Get Widgets Filtered by Role
```http
GET /api/v1/industry-packs/{industryKey}/widgets/{role}
```

**Example:**
```http
GET /api/v1/industry-packs/retail/widgets/admin
```

#### Recommend Industry Based on Business Keywords
```http
POST /api/v1/industry-packs/recommend
Content-Type: application/json

{
  "business_keywords": "I run a restaurant in Kuala Lumpur with 3 branches"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "recommendations": ["food_beverage", "retail"],
    "top_recommendation": "food_beverage"
  }
}
```

#### Get Tenant's Current Industry Profile
```http
GET /api/v1/industry-packs/tenant/{tenantId}
```

### Manage Access (`/api/v1/industry-packs/*`)

#### Apply Industry Pack to Tenant
```http
POST /api/v1/industry-packs/apply
Content-Type: application/json

{
  "tenant_id": 123,
  "industry_key": "retail"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Industry pack 'Retail & E-commerce' applied successfully.",
  "profile": { ... }
}
```

#### Change Industry Pack for Tenant
```http
POST /api/v1/industry-packs/change
Content-Type: application/json

{
  "tenant_id": 123,
  "industry_key": "manufacturing"
}
```

#### Customize Dashboard for Tenant
```http
POST /api/v1/industry-packs/customize-dashboard
Content-Type: application/json

{
  "tenant_id": 123,
  "industry_key": "retail",
  "widgets": [
    { "type": "kpi_card", "title": "Today Sales", "metric": "daily_sales" },
    { "type": "line_chart", "title": "Sales Trend", "metric": "sales_trend_7d" }
  ]
}
```

#### Reset Dashboard to Default
```http
POST /api/v1/industry-packs/reset-dashboard
Content-Type: application/json

{
  "tenant_id": 123,
  "industry_key": "retail"
}
```

## Architecture

### Files Structure
```
app/Modules/IndustryPacks/
├── IndustryPackServiceProvider.php    # Service provider registration
├── config/
│   └── industry_packs.php             # Industry configurations (metrics, costs, templates)
├── Models/
│   └── IndustryProfile.php            # Industry profile model
├── Services/
│   ├── IndustryProfileService.php     # Core business logic
│   └── DashboardLayoutService.php     # Dashboard customization
├── Http/Controllers/
│   └── IndustryPackController.php     # API endpoints
database/migrations/
└── 2026_10_20_001900_create_industry_profiles_table.php
```

### Configuration File (`config/industry_packs.php`)

The main configuration file contains:

1. **industries** — List of available industries with enabled modules
2. **dashboards** — Widget layouts per industry
3. **metrics** — Pre-configured analytics metrics
4. **cost_components** — Default COGS components
5. **templates** — Document template defaults
6. **workflows** — Industry-specific workflow templates

### Integration Points

#### B-Analytics (Business Intelligence)
- Pre-configured metrics are created when industry pack is applied
- Dashboard widgets use Analytics metric keys
- Reports can be pre-filtered by industry type

#### B-COGS Engine (Cost Calculation)
- Default cost components are seeded for the industry
- Product costing templates are available
- Margin analysis thresholds are set

#### B-Sign & Docs (Documents)
- Document templates are enabled based on industry
- Signature workflows follow industry standards
- Report formats match industry requirements

#### B-Virtual Office (Dashboards)
- Dashboard layouts are stored in tenant preferences
- Widget ordering can be customized per user role
- Reset to default functionality available

## Usage Examples

### During Tenant Onboarding

```php
use App\Modules\IndustryPacks\Services\IndustryProfileService;

// 1. Get available industries
$industries = app(IndustryProfileService::class)->getAvailableIndustries();

// 2. Recommend industry based on signup form
$recommendation = app(IndustryProfileService::class)
    ->recommendIndustry($signupKeywords);

// 3. Apply selected industry pack
$result = app(IndustryProfileService::class)
    ->applyIndustryPack($tenantId, $selectedIndustry);
```

### Customizing Dashboard After Application

```php
use App\Modules\IndustryPacks\Services\DashboardLayoutService;

// Add custom widget
app(DashboardLayoutService::class)
    ->customizeDashboard($tenantId, 'retail', $customWidgets);

// Reset to default
app(DashboardLayoutService::class)
    ->resetToDefault($tenantId, 'retail');
```

### Getting Industry-Specific Metrics

```php
use App\Modules\IndustryPacks\Models\IndustryProfile;

$profile = IndustryProfile::where('tenant_id', $tenantId)->first();
$metrics = $profile->getMetrics(); // Returns industry metrics config
```

## Permissions

| Permission | Description | Roles |
|------------|-------------|-------|
| `industry_packs.view` | View industry profiles & templates | All roles |
| `industry_packs.manage` | Change tenant settings & apply templates | Owner, Admin |

## Database Schema

### `industry_profiles` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `tenant_id` | BIGINT UNSIGNED | FK to tenants table |
| `industry_key` | VARCHAR(50) | Industry identifier (unique) |
| `name` | VARCHAR(255) | Display name |
| `description` | TEXT | Industry description |
| `enabled_modules` | JSON | Array of enabled module names |
| `dashboard_layout` | VARCHAR(50) | Default dashboard type |
| `settings` | JSON | Industry-specific settings |
| `status` | ENUM | active/inactive/archived |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Update timestamp |

## Extending Industry Packs

### Adding a New Industry

1. Add entry to `config/industry_packs.php`:
```php
'retail' => [
    'name' => 'New Industry',
    'description' => 'Description here',
    'enabled_modules' => ['module1', 'module2'],
    'default_dashboard' => 'new_industry',
],
```

2. Add dashboard layout:
```php
'dashboards' => [
    'new_industry' => [
        'widgets' => [
            ['type' => 'kpi_card', 'title' => 'Metric 1', 'metric' => 'metric_1'],
            // ...
        ],
    ],
],
```

3. Add metrics, cost components, templates, and workflows following the same pattern.

### Creating Custom Dashboard Widgets

Extend `DashboardLayoutService` with custom widget generation logic:

```php
public function generateCustomWidget($widgetType, $metric)
{
    switch ($widgetType) {
        case 'kpi_card':
            return $this->generateKPICard($metric);
        case 'line_chart':
            return $this->generateLineChart($metric);
        // ... more types
    }
}
```

## Notes

- Industry Packs are **configuration-only** — no complex code required
- They integrate seamlessly with existing modules (B-Analytics, B-COGS, B-Sign & Docs)
- Tenants can change their industry pack at any time (previous profile archived)
- The recommendation engine uses simple keyword matching but can be enhanced with NLP
- All configurations are stored as JSON for flexibility
