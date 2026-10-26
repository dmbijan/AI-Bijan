# B-Brain Hub — AI-Powered Insight, Forecast & Recommendation Engine

## Overview

B-Brain Hub adalah lapisan AI yang berkuasa yang memberikan insight automatik, ramalan statistik, dan cadangan boleh tindakan berdasarkan data dari SEMUA modul AI-Bijan lain. Modul ini berfungsi sebagai "otak" sistem yang menganalisis data dan memberikan panduan strategik.

## Features

### 1. Insight Engine
- **Automated Insight Generation** — Menghasilkan insight daripada metrics B-Analytics
- **Threshold Monitoring** — Mengesan metrik yang melebihi had ditetapkan
- **Trend Analysis** — Mengenal pasti trend naik/turun menggunakan linear regression
- **Anomaly Detection** — Mengesan anomali menggunakan standard deviation
- **Category Support** — Financial, Sales, Operations, HR, Marketing, Customer, Inventory, Logistics
- **Severity Levels** — Low, Medium, High, Critical
- **Action Tracking** — Track actions taken terhadap insights

### 2. Forecasting Engine
- **Multiple Models**:
  - Linear Regression — Trend projection dengan R² calculation
  - Moving Average — Configurable window size
  - Exponential Smoothing (Holt's Double) — Untuk data dengan trend
  - ARIMA — Advanced time series (placeholder)
- **Confidence Intervals** — Upper/lower bounds untuk setiap forecast
- **Accuracy Scoring** — MAPE, RMSE, R² metrics
- **Horizon Configuration** — 1-365 days prediction horizon
- **Model Comparison** — Auto-evaluate model performance

### 3. Recommendation Engine
- **Insight-to-Recommendation Conversion** — Auto-generate recommendations from insights
- **Category-Specific Actions** — Action steps per business category
- **Priority Levels** — Low, Medium, High, Urgent
- **Accept/Complete Workflow** — Full lifecycle tracking
- **Feedback System** — Score recommendations (1-5 stars)
- **Impact Estimation** — Estimated savings/ROI per recommendation

### 4. Pattern Recognition (AIPatternEngine)
- **Seasonality Detection** — Autocorrelation-based periodicity analysis
- **Trend Detection** — Linear regression dengan R² strength
- **Anomaly Detection** — Z-score based outlier identification
- **Correlation Analysis** — Pearson correlation between metrics
- **Pattern Summary** — Dashboard-ready pattern overview

## API Endpoints

### Insights

```http
GET    /api/v1/brainhub/insights              # List insights (perm:brainhub.view)
GET    /api/v1/brainhub/insights/{id}         # Get single insight
GET    /api/v1/brainhub/insights/statistics   # Get insight statistics
POST   /api/v1/brainhub/insights/generate     # Generate insights manually
POST   /api/v1/brainhub/insights/{id}/mark-read    # Mark as read
POST   /api/v1/brainhub/insights/{id}/dismiss    # Dismiss insight
POST   /api/v1/brainhub/insights/{id}/action     # Record action taken
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Revenue declining 15% over past 30 days",
      "description": "Sales revenue shows a decreasing trend...",
      "category": "financial",
      "type": "trend",
      "severity": "high",
      "confidence_score": 0.85,
      "is_read": false,
      "data_points": {
        "change_percentage": -15.2,
        "period_days": 30
      }
    }
  ]
}
```

### Forecasts

```http
POST   /api/v1/brainhub/forecasts/metric/{metricId}/generate  # Generate forecast
GET    /api/v1/brainhub/forecasts/metric/{metricId}           # Get forecast history
POST   /api/v1/brainhub/forecasts/batch-generate              # Batch forecast
POST   /api/v1/brainhub/forecasts/{id}/evaluate               # Evaluate accuracy
```

**Example Request:**
```json
{
  "horizon_days": 30,
  "model": "linear_regression"
}
```

**Example Response:**
```json
{
  "success": true,
  "forecast": {
    "model": "linear_regression",
    "horizon_days": 30,
    "r_squared": 0.87,
    "forecast_data": [
      {"date": "2026-10-21", "value": 10500, "lower_bound": 9800, "upper_bound": 11200},
      {"date": "2026-10-22", "value": 10700, "lower_bound": 9900, "upper_bound": 11500}
    ]
  }
}
```

### Recommendations

```http
GET    /api/v1/brainhub/recommendations          # List recommendations
GET    /api/v1/brainhub/recommendations/{id}     # Get single recommendation
GET    /api/v1/brainhub/recommendations/statistics # Get statistics
POST   /api/v1/brainhub/recommendations/{id}/accept    # Accept recommendation
POST   /api/v1/brainhub/recommendations/{id}/complete  # Complete recommendation
POST   /api/v1/brainhub/recommendations/{id}/feedback  # Provide feedback
POST   /api/v1/brainhub/recommendations/bulk-generate  # Bulk generate
```

**Example Request (Accept):**
```json
{}
```

**Example Response:**
```json
{
  "message": "Recommendation accepted"
}
```

## Database Schema

### Tables

#### `brainhub_insights`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant reference |
| title | VARCHAR | Insight title |
| description | TEXT | Detailed description |
| category | VARCHAR | financial, sales, operations, etc. |
| type | VARCHAR | trend, anomaly, opportunity, risk |
| severity | VARCHAR | low, medium, high, critical |
| data_points | JSON | Metric values and context |
| confidence_score | DECIMAL | 0-1 confidence level |
| source_module | VARCHAR | Originating module |
| metric_id | BIGINT | Related analytics metric |
| is_read | BOOLEAN | Read status |
| is_dismissed | BOOLEAN | Dismissal status |
| action_taken | VARCHAR | Action category |
| action_notes | TEXT | User notes |

#### `brainhub_forecasts`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant reference |
| metric_id | BIGINT | Related metric |
| model | VARCHAR | linear_regression, moving_average, etc. |
| horizon_days | INT | Prediction horizon |
| forecast_data | JSON | Array of predicted values |
| confidence_intervals | JSON | Upper/lower bounds |
| r_squared | DECIMAL | Model fit (0-1) |
| mape | DECIMAL | Mean absolute % error |
| rmse | DECIMAL | Root mean square error |

#### `brainhub_recommendations`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant reference |
| insight_id | BIGINT | Source insight |
| title | VARCHAR | Recommendation title |
| description | TEXT | Detailed description |
| type | VARCHAR | actionable, optimization, risk_mitigation, etc. |
| priority | VARCHAR | low, medium, high, urgent |
| action_steps | JSON | Array of action items |
| estimated_impact | DECIMAL | Expected impact value |
| estimated_savings | DECIMAL | Expected savings |
| user_id | BIGINT | Assigned user |
| is_accepted | BOOLEAN | Acceptance status |
| is_completed | BOOLEAN | Completion status |
| feedback_score | INT | 1-5 rating |

## Integration Points

### With B-Analytics
- **Consumes**: Metrics, MetricValues
- **Purpose**: Source data for insight generation and forecasting
- **Trigger**: Auto-generate insights when metrics updated

### With B-Sign & Docs
- **Produces**: Signed insight reports
- **Purpose**: Document approved recommendations

### With B-Omnichannel
- **Delivers**: Insight notifications
- **Purpose**: Alert users via WhatsApp/Email/SMS

### With B-Finance
- **Context**: Financial insights & forecasts
- **Purpose**: Revenue projections, cash flow alerts

## Configuration

Edit `config/brainhub.php`:

```php
'ai_provider' => [
    'default' => 'openai', // openai, anthropic, azure
    'models' => [
        'openai' => 'gpt-4',
        'anthropic' => 'claude-3-opus',
    ],
    'timeout' => 30,
],

'forecasting' => [
    'default_model' => 'linear_regression',
    'max_horizon_days' => 365,
    'min_data_points' => 14,
],

'pattern_recognition' => [
    'min_pattern_strength' => 0.7, // R² or correlation threshold
    'anomaly_z_score' => 2.5, // Standard deviations
    'seasonality_min_periods' => 3,
],

'recommendations' => [
    'auto_generate' => true, // Auto-create from insights
    'max_per_tenant' => 100,
    'expiry_days' => 90,
],
```

## Usage Examples

### Generate Insights Manually
```bash
POST /api/v1/brainhub/insights/generate
Authorization: Bearer {token}
```

### Create Forecast
```bash
POST /api/v1/brainhub/forecasts/metric/1/generate
{
  "horizon_days": 30,
  "model": "exponential_smoothing"
}
```

### Accept Recommendation
```bash
POST /api/v1/brainhub/recommendations/42/accept
{
  "user_id": 123
}
```

### Evaluate Forecast Accuracy
```bash
POST /api/v1/brainhub/forecasts/15/evaluate
```

## Service Architecture

```
BrainHubServiceProvider
├── InsightService
│   ├── Threshold checking
│   ├── Trend analysis
│   └── Anomaly detection
├── ForecastService
│   ├── Linear regression
│   ├── Moving average
│   └── Exponential smoothing
├── RecommendationService
│   ├── Insight conversion
│   ├── Action step generation
│   └── Feedback tracking
└── AIPatternEngine
    ├── Seasonality detection
    ├── Correlation analysis
    └── Pattern summary
```

## Security & Permissions

| Permission | Access |
|------------|--------|
| `brainhub.view` | View insights, forecasts, recommendations |
| `brainhub.insights` | Mark read, dismiss, record actions |
| `brainhub.forecasts` | Generate & evaluate forecasts |
| `brainhub.recommendations` | Accept, complete, provide feedback |

## Best Practices

1. **Regular Insight Generation** — Schedule daily insight generation via cron
2. **Forecast Validation** — Evaluate forecast accuracy monthly
3. **Feedback Loop** — Encourage users to score recommendations
4. **Pattern Review** — Quarterly review of detected patterns
5. **Model Tuning** — Adjust thresholds based on business context

## Future Enhancements

- [ ] ML-based forecasting (Prophet, LSTM)
- [ ] Natural language insights generation
- [ ] Automated action execution
- [ ] Cross-tenant pattern learning
- [ ] Real-time streaming insights
- [ ] Custom alert rules engine
