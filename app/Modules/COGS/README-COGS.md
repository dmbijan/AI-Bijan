# B-COGS Engine — Cost of Goods Sold Calculation & Margin Analysis

## Overview

B-COGS Engine adalah modul yang mengurus pengiraan **Cost of Goods Sold (COGS)** dan **margin analysis** untuk produk. Modul ini membantu perniagaan memahami kos sebenar produk, mengawal margin keuntungan, dan membuat keputusan pricing yang tepat.

## Features

### 1. Cost Calculation Engine
- **Multiple Valuation Methods**: FIFO, LIFO, Weighted Average, Specific ID
- **Direct Cost Tracking**: Raw materials, direct labor, manufacturing overhead, packaging, shipping in, duties & taxes
- **Overhead Allocation**: Automated allocation based on production hours, direct labor cost, or material cost
- **Cost Component Management**: Add/remove individual cost components per product
- **Cost History**: Track cost changes over time

### 2. Margin Analysis
- **Gross Margin Calculation**: Real-time margin calculation per sale
- **Margin Thresholds**: Set minimum and target margins with alerts
- **Category Analysis**: Margin breakdown by product category
- **Trend Analysis**: Margin trends over time (6-24 months)
- **Low Margin Detection**: Identify products below threshold
- **Profitability Ranking**: Rank products by gross profit contribution

### 3. Break-Even Analysis
- **Break-Even Point**: Calculate units needed to break even
- **Fixed vs Variable Costs**: Separate cost behavior analysis
- **Contribution Margin**: Per-unit contribution analysis

### 4. Reporting
- **Margin Report**: Period-based margin summary
- **Product Costing**: Detailed cost breakdown per product
- **COGS Transactions**: Track actual COGS from sales

## API Endpoints

### Cost Calculation

```http
GET    /api/v1/cogs/costs/product/{id}/current          # Get current cost (perm:cogs.view)
GET    /api/v1/cogs/costs/product/{id}/history           # Get cost history
POST   /api/v1/cogs/costs/product/{id}/calculate         # Calculate product cost (perm:cogs.manage)
POST   /api/v1/cogs/costs/product/{id}/components        # Add cost component
DELETE /api/v1/cogs/costs/components/{id}                 # Remove cost component
POST   /api/v1/cogs/costs/allocate-overheads             # Allocate overheads
POST   /api/v1/cogs/costs/recalculate-all                # Recalculate all costs
```

**Example Request (Calculate):**
```json
{
  "method": "weighted_average"
}
```

**Example Response:**
```json
{
  "id": 1,
  "product_id": 5,
  "cost_date": "2026-10-20",
  "method": "weighted_average",
  "raw_material_cost": 15.50,
  "direct_labor_cost": 8.00,
  "manufacturing_overhead_cost": 4.50,
  "packaging_cost": 1.20,
  "shipping_in_cost": 2.00,
  "total_direct_cost": 31.20,
  "allocated_overhead": 8.80,
  "total_unit_cost": 40.00,
  "selling_price": 65.00,
  "gross_margin": 25.00,
  "gross_margin_percentage": 38.46,
  "status": "draft"
}
```

### Margin Reports

```http
GET    /api/v1/cogs/margins/all                          # All product margins (perm:cogs.view)
GET    /api/v1/cogs/margins/by-category                  # Margin by category
GET    /api/v1/cogs/margins/low-margin                   # Low margin products
GET    /api/v1/cogs/margins/profitability-ranking        # Profitability ranking
GET    /api/v1/cogs/margins/product/{id}/trend           # Margin trend (perm:cogs.view)
POST   /api/v1/cogs/margins/product/{id}/calculate       # Calculate margin (perm:cogs.reports)
POST   /api/v1/cogs/margins/product/{id}/break-even      # Break-even analysis (perm:cogs.reports)
POST   /api/v1/cogs/margins/generate-report              # Generate report (perm:cogs.reports)
```

**Example Request (Calculate Margin):**
```json
{
  "selling_price": 75.00
}
```

**Example Response:**
```json
{
  "success": true,
  "product_id": 5,
  "selling_price": 75.00,
  "unit_cost": 40.00,
  "gross_margin": 35.00,
  "gross_margin_percentage": 46.67,
  "target_margin": 35.0,
  "min_margin": 20.0,
  "status": "healthy",
  "message": "Gross margin 46.67% meets target of 35.00%"
}
```

**Example Request (Break-Even):**
```json
{
  "fixed_costs": 10000
}
```

**Example Response:**
```json
{
  "success": true,
  "fixed_costs": 10000.00,
  "selling_price": 75.00,
  "variable_cost": 40.00,
  "contribution_margin": 35.00,
  "break_even_units": 286,
  "break_even_revenue": 21450.00
}
```

## Database Schema

### Tables

#### `cogs_cost_components`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant reference |
| product_id | BIGINT | Product reference |
| component_type | VARCHAR | raw_material, direct_labor, etc. |
| quantity | DECIMAL | Quantity used |
| unit_price | DECIMAL | Price per unit |
| is_allocated | BOOLEAN | For overhead allocation |
| source_document | VARCHAR | Purchase order, work order, etc. |

#### `cogs_product_costs`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant reference |
| product_id | BIGINT | Product reference |
| cost_date | DATE | Cost effective date |
| method | VARCHAR | fifo, lifo, weighted_average |
| raw_material_cost | DECIMAL | Total raw material cost |
| direct_labor_cost | DECIMAL | Direct labor cost |
| manufacturing_overhead_cost | DECIMAL | Overhead cost |
| total_unit_cost | DECIMAL | Final unit cost |
| selling_price | DECIMAL | Current selling price |
| gross_margin_percentage | DECIMAL | Margin percentage |
| status | VARCHAR | draft, finalized, archived |

#### `cogs_transactions`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant reference |
| sales_order_id | BIGINT | Related sales order |
| transaction_date | DATE | Transaction date |
| transaction_type | VARCHAR | sale_return, adjustment |
| quantity | INT | Quantity sold |
| unit_cost | DECIMAL | Cost at time of sale |
| total_cogs | DECIMAL | Total COGS amount |

## Configuration

Edit `config/cogs.php`:

```php
'cost_calculation' => [
    'default_method' => 'weighted_average', // fifo, lifo, weighted_average
    'include_shipping' => true,
    'include_taxes' => false,
    'rounding_decimals' => 2,
],

'direct_costs' => [
    'raw_materials' => true,
    'direct_labor' => true,
    'manufacturing_overhead' => true,
    'packaging' => true,
    'shipping_in' => true,
    'duties_taxes' => false,
],

'overhead_allocation' => [
    'method' => 'production_hours', // production_hours, direct_labor_cost
    'period' => 'monthly',
],

'margin_analysis' => [
    'min_gross_margin' => 20.0, // Minimum acceptable %
    'target_gross_margin' => 35.0, // Target %
    'alert_threshold' => 10.0, // Alert when below target - threshold
],
```

## Service Architecture

```
COGSServiceProvider
├── CostCalculationService
│   ├── calculateProductCost()
│   ├── applyInventoryMethod()
│   ├── allocateOverheads()
│   └── recalculateAll()
└── MarginAnalysisService
    ├── calculateMargin()
    ├── getAllProductMargins()
    ├── getMarginByCategory()
    ├── getMarginTrend()
    ├── calculateBreakEven()
    └── generateMarginReport()
```

## Integration Points

### With B-Inventory
- **Consumes**: Products, stock quantities
- **Purpose**: Product costing basis

### With B-Manufacturing
- **Consumes**: BOM, work orders
- **Purpose**: Production cost calculation

### With B-Finance
- **Produces**: COGS journal entries
- **Purpose**: General ledger posting

### With B-CRM
- **Consumes**: Deal profitability
- **Purpose**: Quote margin checking

### With B-POS
- **Consumes**: Sale prices
- **Purpose**: Real-time margin calculation

## Usage Examples

### Add Cost Components
```bash
POST /api/v1/cogs/costs/product/5/components
{
  "component_type": "raw_material",
  "description": "Steel sheet A36",
  "quantity": 2.5,
  "unit_price": 6.20
}
```

### Calculate Product Cost
```bash
POST /api/v1/cogs/costs/product/5/calculate
{
  "method": "weighted_average"
}
```

### Check Margin Before Sale
```bash
POST /api/v1/cogs/margins/product/5/calculate
{
  "selling_price": 75.00
}
```

### Generate Monthly Margin Report
```bash
POST /api/v1/cogs/margins/generate-report
{
  "start_date": "2026-10-01",
  "end_date": "2026-10-31"
}
```

## Best Practices

1. **Regular Cost Updates** — Recalculate costs monthly or after major price changes
2. **Margin Monitoring** — Review low-margin products weekly
3. **Overhead Allocation** — Use production hours for most accurate allocation
4. **Pricing Strategy** — Set prices based on target margin, not just competition
5. **Cost Component Documentation** — Always document source documents for traceability

## Security & Permissions

| Permission | Access |
|------------|--------|
| `cogs.view` | View product costs, COGS reports, margin analysis |
| `cogs.manage` | Calculate costs, add components, allocate overheads |
| `cogs.reports` | Generate margin reports, break-even analysis |

## Future Enhancements

- [ ] Activity-based costing (ABC)
- [ ] Standard vs actual cost variance analysis
- [ ] Supplier cost comparison
- [ ] Automated price recommendations
- [ ] Integration with procurement module
- [ ] Multi-currency costing
