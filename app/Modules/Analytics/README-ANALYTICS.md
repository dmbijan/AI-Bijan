# B-Analytics — Business Intelligence & Analytics Engine

## Gambaran Keseluruhan

B-Analytics merupakan modul **Business Intelligence** yang menyediakan kemampuan analytics menyeluruh untuk semua data dari 21+ modul AI-Bijan. Modul ini memberikan:

- **Real-time Metrics** — KPI tracking dengan auto-refresh
- **Custom Dashboards** — Dashboard interaktif dengan widgets yang boleh disesuaikan
- **Report Generation** — Laporan terjadwal dalam pelbagai format (PDF, Excel, CSV, JSON)
- **Trend Analysis** — Analisis tren menggunakan linear regression
- **Anomaly Detection** — Pengesanan anomali dalam data perniagaan
- **Forecasting** — Ramalan berdasarkan data historikal
- **Cross-module Aggregation** — Penggabungan data dari semua modul

## Struktur Modul

```
app/Modules/Analytics/
├── config/
│   └── analytics.php              # Konfigurasi metrics, reports, dashboards
├── database/
│   └── migrations/
│       └── 2026_10_20_001600_create_analytics_tables.php
├── Http/
│   └── Controllers/
│       ├── MetricController.php   # CRUD metrics & calculations
│       ├── ReportController.php   # Report generation & scheduling
│       └── DashboardController.php # Dashboard & widget management
├── Models/
│   ├── Metric.php                 # Metric definitions
│   ├── MetricValue.php            # Metric value history
│   ├── Report.php                 # Report templates
│   ├── ReportRun.php              # Report execution history
│   ├── Dashboard.php              # Dashboard configurations
│   └── DashboardWidget.php        # Widget definitions
├── Services/
│   ├── MetricService.php          # Metric operations
│   ├── ReportService.php          # Report generation & export
│   ├── DashboardService.php       # Dashboard management
│   └── AnalyticsEngine.php        # Advanced analytics engine
└── AnalyticsServiceProvider.php   # Module service provider
```

## API Endpoints

### Base URL: `/api/v1/analytics/`

#### Metrics

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/metrics/` | `analytics.view` | List all metrics |
| GET | `/metrics/{id}` | `analytics.view` | Get metric details |
| GET | `/metrics/{id}/history` | `analytics.view` | Get historical values |
| POST | `/metrics/{id}/calculate` | `analytics.view` | Trigger calculation |
| POST | `/metrics/` | `analytics.manage` | Create metric |
| PUT | `/metrics/{id}` | `analytics.manage` | Update metric |
| DELETE | `/metrics/{id}` | `analytics.manage` | Delete metric |
| POST | `/metrics/batch-calculate` | `analytics.manage` | Batch calculate |

#### Reports

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/reports/` | `analytics.view` | List all reports |
| GET | `/reports/{id}/runs` | `analytics.view` | Get report runs |
| POST | `/reports/{id}/generate` | `analytics.manage` | Generate report |
| POST | `/reports/{id}/schedule` | `analytics.manage` | Schedule report |
| POST | `/reports/{id}/cancel-schedule` | `analytics.manage` | Cancel schedule |

#### Exports

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/exports/{filename}` | `analytics.export` | Download report file |

#### Dashboards

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/dashboards/` | `analytics.view` | List dashboards |
| GET | `/dashboards/default` | `analytics.view` | Get default dashboard |
| GET | `/dashboards/{id}` | `analytics.view` | Get dashboard with widgets |
| POST | `/dashboards/` | `analytics.manage` | Create dashboard |
| PUT | `/dashboards/{id}` | `analytics.manage` | Update dashboard |
| DELETE | `/dashboards/{id}` | `analytics.manage` | Delete dashboard |
| POST | `/dashboards/{id}/share` | `analytics.manage` | Share dashboard |
| POST | `/dashboards/{id}/refresh` | `analytics.manage` | Refresh dashboard data |
| POST | `/dashboards/{id}/widgets` | `analytics.manage` | Add widget |
| PUT | `/widgets/{widgetId}` | `analytics.manage` | Update widget |
| DELETE | `/widgets/{widgetId}` | `analytics.manage` | Delete widget |

## Database Tables

### `analytics_metrics`

Stores metric definitions and current values.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Foreign key to tenants |
| name | VARCHAR(255) | Metric display name |
| code | VARCHAR(100) | Unique metric code |
| category | VARCHAR(50) | finance, sales, operations, hr, marketing, customer, inventory, custom |
| type | VARCHAR(20) | kpi, metric, ratio, rate, score |
| formula | JSON | Calculation formula |
| data_source | VARCHAR(255) | Source module/table |
| aggregation_type | VARCHAR(20) | sum, avg, count, min, max, ratio, cumulative |
| unit | VARCHAR(50) | Measurement unit |
| decimal_places | SMALLINT | Decimal precision |
| target_value | DECIMAL | Target/KPI goal |
| threshold_warning | DECIMAL | Warning threshold |
| threshold_critical | DECIMAL | Critical threshold |
| is_active | BOOLEAN | Active status |
| refresh_interval_minutes | INT | Auto-refresh interval |
| last_value | DECIMAL | Last calculated value |
| previous_value | DECIMAL | Previous period value |
| trend_direction | VARCHAR(10) | up, down, stable |
| trend_percentage | DECIMAL | Percentage change |
| last_calculated_at | TIMESTAMP | Last calculation time |

### `analytics_metric_values`

Time-series storage for metric values.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Foreign key to tenants |
| metric_id | BIGINT | Foreign key to metrics |
| value | DECIMAL | Recorded value |
| calculated_at | TIMESTAMP | When calculated |

### `analytics_reports`

Report template definitions.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Foreign key to tenants |
| name | VARCHAR(255) | Report name |
| code | VARCHAR(100) | Unique report code |
| type | VARCHAR(20) | scheduled, on_demand, automated |
| format | VARCHAR(10) | pdf, excel, csv, json |
| metrics | JSON | Included metrics |
| filters | JSON | Filter configuration |
| schedule | JSON | {interval, time} |
| recipients | JSON | Email recipients |
| is_active | BOOLEAN | Active status |
| last_run_at | TIMESTAMP | Last execution time |
| next_run_at | TIMESTAMP | Next scheduled run |

### `analytics_report_runs`

Report execution history.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Foreign key to tenants |
| report_id | BIGINT | Foreign key to reports |
| status | VARCHAR(20) | pending, running, completed, failed, cancelled |
| started_at | TIMESTAMP | Execution start time |
| completed_at | TIMESTAMP | Execution end time |
| duration_seconds | INT | Execution duration |
| output_file | VARCHAR(500) | Generated file path |

### `analytics_dashboards`

Dashboard configurations.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Foreign key to tenants |
| name | VARCHAR(255) | Dashboard name |
| code | VARCHAR(100) | Unique dashboard code |
| type | VARCHAR(20) | main, executive, operational, custom |
| layout | JSON | Grid layout configuration |
| widgets | JSON | Widget definitions |
| is_active | BOOLEAN | Active status |
| is_default | BOOLEAN | Default dashboard flag |
| shared_with | JSON | Array of user IDs |

### `dashboard_widgets`

Individual widget configurations.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Foreign key to tenants |
| dashboard_id | BIGINT | Foreign key to dashboards |
| title | VARCHAR(255) | Widget title |
| type | VARCHAR(20) | kpi_card, line_chart, bar_chart, pie_chart, table, gauge, trend, metric |
| metric_id | BIGINT | Associated metric |
| chart_config | JSON | Chart-specific settings |
| position_x | INT | Grid position X |
| position_y | INT | Grid position Y |
| width | INT | Grid width (out of 12) |
| height | INT | Grid height |
| refresh_interval | INT | Auto-refresh in seconds |

## Permissions

| Permission | Description |
|------------|-------------|
| `analytics.view` | View dashboards, reports & metrics |
| `analytics.manage` | Manage metrics, reports & dashboard configurations |
| `analytics.export` | Export analytics data to PDF, Excel, CSV |
| `analytics.reports` | Access advanced analytics & insights |

## Roles & Analytics Access

| Role | Access Level |
|------|-------------|
| Tenant Owner | Full access (*) |
| Tenant Admin | Full analytics management |
| Finance Manager | Reports + export |
| HR Manager | View analytics |
| Inventory Manager | View analytics |
| Cashier | View basic metrics |
| Staff | View basic metrics |
| Auditor | Reports + insights |

## Integration Points

### With Other Modules

- **B-Payroll** — Employee cost metrics, payroll trends
- **B-Finance** — Revenue, profit, cash flow metrics
- **B-CRM** — Sales pipeline, conversion rates
- **B-SCM** — Inventory levels, supply chain efficiency
- **B-POS** — Sales transactions, daily revenue
- **B-Marketing** — Campaign ROI, lead metrics
- **B-Loyalty** — Member engagement, points redemption
- **B-Omnichannel** — Communication response times, satisfaction
- **B-Logistics** — Shipment tracking, delivery times

### Future Integrations

- **B-Brain Hub** — AI-powered insights on metrics
- **B-Sign & Docs** — Digital signatures on reports
- **B-Virtual Office** — Analytics in virtual workspace

## Configuration

Edit `config/analytics.php`:

```php
'metric' => [
    'auto_refresh_enabled' => true,
    'refresh_interval_minutes' => 5,
    'max_history_days' => 365,
],

'report' => [
    'scheduling_enabled' => true,
    'supported_formats' => ['pdf', 'excel', 'csv', 'json'],
    'cache_duration_minutes' => 15,
],

'dashboard' => [
    'max_widgets_per_dashboard' => 20,
    'widget_refresh_interval' => 30,
],

'ai_insights' => [
    'enabled' => false,
    'confidence_threshold' => 0.75,
],
```

## Usage Examples

### Creating a Metric

```php
use App\Modules\Analytics\Services\MetricService;

$metricService = app(MetricService::class);

$metric = $metricService->upsertMetric($tenantId, [
    'code' => 'TOTAL_REVENUE',
    'name' => 'Total Revenue',
    'category' => 'finance',
    'type' => 'kpi',
    'aggregation_type' => 'sum',
    'data_source' => 'finance_transactions',
    'data_source_column' => 'amount',
    'unit' => 'MYR',
    'target_value' => 100000,
    'threshold_warning' => 75000,
    'threshold_critical' => 50000,
]);
```

### Generating a Report

```php
use App\Modules\Analytics\Services\ReportService;

$reportService = app(ReportService::class);

$result = $reportService->generateReport($reportId, [
    'date_range' => [
        'from' => '2024-01-01',
        'to' => '2024-01-31',
    ],
]);
```

### Adding Widget to Dashboard

```php
use App\Modules\Analytics\Services\DashboardService;

$dashboardService = app(DashboardService::class);

$widget = $dashboardService->addWidget($tenantId, $dashboardId, [
    'title' => 'Revenue Trend',
    'type' => 'line_chart',
    'metric_id' => $metricId,
    'width' => 8,
    'height' => 4,
    'position_x' => 0,
    'position_y' => 0,
]);
```

## Analytics Engine Features

### Trend Analysis

Uses linear regression to identify trends:
- Direction (increasing/decreasing/stable)
- Strength (R² value)
- Projected future values

### Anomaly Detection

Statistical anomaly detection using:
- Mean + standard deviation thresholds
- Configurable sensitivity
- Historical baseline comparison

### Forecasting

Simple linear projection for:
- Short-term predictions (7-30 days)
- Confidence intervals
- Based on 90-day historical data

### Cross-module Correlation

Pearson correlation analysis between metrics from different modules to discover relationships.

## Migration

```bash
php artisan migrate --path=app/Modules/Analytics/database/migrations/
```

## Service Provider Registration

Already registered in `bootstrap/providers.php`:

```php
use App\Modules\Analytics\AnalyticsServiceProvider;

// ...
AnalyticsServiceProvider::class,
```
