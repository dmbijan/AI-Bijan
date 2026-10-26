# B-Marketing — Marketing Automation Platform (HubSpot Replacement)

## Overview

B-Marketing adalah modul SaaS lengkap untuk **Marketing Automation** yang menggantikan HubSpot, Salesforce Marketing Cloud, dan ActiveCampaign. Modul ini menyediakan semua alat yang diperlukan untuk mengurus campaign, segmentation, contact management, email sequences, workflow automation, lead scoring, ad integration, dan analytics dalam satu platform terpadu.

### Ciri-ciri Utama

- **Multi-Channel Campaign Management** — Email, SMS, WhatsApp, Social Media, Paid Ads, Multi-Channel
- **Dynamic Segmentation** — Static & dynamic segments dengan criteria evaluation
- **Contact Management** — GDPR-compliant dengan opt-in tracking (email, SMS, WhatsApp)
- **Voucher System** — Generate codes, validation, usage tracking, discount types (percentage/fixed/free shipping)
- **Deal Pipeline** — Sales pipeline management dengan stage tracking (prospect → qualified → proposal → negotiation → closed_won/lost)
- **Email Sequences** — Automated drip campaigns dengan trigger-based execution
- **Workflow Automation** — Visual workflow builder dengan multi-step actions
- **Lead Scoring** — Demographic, firmographic, engagement, behavioral, transactional scoring dengan grade/tier system
- **Ad Integration** — Google Ads & Facebook Ads API integration
- **Analytics Dashboard** — ROI tracking, attribution modeling, funnel metrics, time-series trends

---

## Architecture

### Module Structure

```
app/Modules/Marketing/
├── MarketingServiceProvider.php    # Service bindings & registration
├── config/marketing.php            # Configuration (campaign types, segments, vouchers, etc.)
├── Models/
│   ├── Campaign.php                # Multi-channel campaign management
│   ├── Segment.php                 # Static/dynamic customer segments
│   ├── Contact.php                 # GDPR-compliant contact management
│   ├── Voucher.php                 # Coupon/voucher system
│   ├── Deal.php                    # Sales pipeline deals
│   ├── DealActivity.php            # Deal activity tracking
│   ├── Sequence.php                # Email sequence definitions
│   ├── SequenceExecution.php       # Sequence execution instances
│   ├── Workflow.php                # Workflow automation definitions
│   ├── WorkflowExecution.php       # Workflow execution instances
│   └── LeadScore.php               # Lead scoring with grade/tier
├── Services/
│   ├── CampaignService.php         # Campaign CRUD & multi-channel execution
│   ├── SegmentService.php          # Segment creation & criteria evaluation
│   ├── ContactService.php          # Contact CRUD & GDPR compliance
│   ├── VoucherService.php          # Voucher generation & validation
│   ├── DealService.php             # Deal pipeline & forecasting
│   ├── SequenceService.php         # Email sequence execution
│   ├── WorkflowService.php         # Workflow automation engine
│   ├── LeadScoreService.php        # Lead scoring calculation
│   ├── AdIntegrationService.php    # Google/Facebook Ads API
│   └── AnalyticsService.php        # Analytics & reporting
├── Http/
│   └── Controllers/
│       ├── CampaignController.php
│       ├── SegmentController.php
│       ├── ContactController.php
│       ├── VoucherController.php
│       ├── DealController.php
│       ├── SequenceController.php
│       ├── WorkflowController.php
│       ├── LeadScoreController.php
│       ├── AdIntegrationController.php
│       └── AnalyticsController.php
└── database/migrations/
    └── 2026_10_20_000900_create_marketing_tables.php
```

---

## Database Schema

### Tables Created

| Table | Description | Key Fields |
|-------|-------------|------------|
| `marketing_campaigns` | Campaign definitions | type, channels, status, budget, goals |
| `marketing_segments` | Customer segments | type (static/dynamic), criteria |
| `marketing_contacts` | Contact records | email, phone, opt_in fields, tags |
| `marketing_vouchers` | Voucher/coupon definitions | code, discount_type, valid_from/until |
| `marketing_voucher_usage` | Voucher redemption history | voucher_id, order_id, discount_amount |
| `marketing_deals` | Sales pipeline deals | stage, value, probability, currency |
| `marketing_deal_activities` | Deal activity log | type (call/meeting/email/note/task) |
| `marketing_sequences` | Email sequence definitions | trigger, steps, max_repetitions |
| `marketing_sequence_executions` | Sequence run instances | status, current_step, step_results |
| `marketing_workflows` | Workflow automation definitions | trigger_type, steps, execution_count |
| `marketing_workflow_executions` | Workflow run instances | status, current_step, error_message |
| `marketing_lead_scores` | Lead scoring records | total_score, grade (A-F), tier |

### Indexes

- Composite indexes on `tenant_id` + `status`, `tenant_id` + `type`
- Unique indexes on `code` (campaigns, vouchers, sequences)
- Full-text search ready on contacts (email, name)

---

## API Endpoints

Base URL: `/api/v1/`

### Authentication

All endpoints require Sanctum authentication with tenant context. Permissions enforced via middleware.

### Permission Levels

| Permission | Access |
|------------|--------|
| `marketing.view` | Read-only access to campaigns, segments, contacts, deals, sequences, workflows |
| `marketing.manage` | Full CRUD operations for all marketing entities |
| `marketing.approve` | Approve campaign lifecycle changes & deal closures |
| `marketing.reports` | Access analytics, statistics, and reporting endpoints |

### Role Assignments

| Role | Marketing Permissions |
|------|----------------------|
| `tenant_owner` | `*` (all permissions) |
| `tenant_admin` | `marketing.view`, `marketing.manage` |
| `finance_manager` | `marketing.view`, `marketing.reports` |
| `hr_manager` | `marketing.view`, `marketing.reports` |
| `cashier` | `marketing.view` |
| `staff` | `marketing.view` |
| `auditor` | `marketing.view`, `marketing.reports` |

### Campaigns

```
GET    /api/v1/marketing/campaigns              # List campaigns
GET    /api/v1/marketing/campaigns/{id}         # Show campaign
POST   /api/v1/marketing/campaigns              # Create campaign
PUT    /api/v1/marketing/campaigns/{id}         # Update campaign
DELETE /api/v1/marketing/campaigns/{id}         # Delete campaign
POST   /api/v1/marketing/campaigns/{id}/start   # Start campaign
POST   /api/v1/marketing/campaigns/{id}/pause   # Pause campaign
POST   /api/v1/marketing/campaigns/{id}/complete # Complete campaign
GET    /api/v1/marketing/campaigns/stats        # Campaign statistics
```

### Segments

```
GET    /api/v1/marketing/segments               # List segments
GET    /api/v1/marketing/segments/{id}          # Show segment
POST   /api/v1/marketing/segments               # Create segment
PUT    /api/v1/marketing/segments/{id}          # Update segment
DELETE /api/v1/marketing/segments/{id}          # Delete segment
POST   /api/v1/marketing/segments/{id}/toggle   # Toggle active
POST   /api/v1/marketing/segments/{id}/add-contact      # Add contact
POST   /api/v1/marketing/segments/{id}/remove-contact   # Remove contact
POST   /api/v1/marketing/segments/{id}/calculate        # Recalculate count
```

### Contacts

```
GET    /api/v1/marketing/contacts               # List contacts
GET    /api/v1/marketing/contacts/{id}          # Show contact
POST   /api/v1/marketing/contacts               # Create contact
PUT    /api/v1/marketing/contacts/{id}          # Update contact
DELETE /api/v1/marketing/contacts/{id}          # Hard delete
POST   /api/v1/marketing/contacts/{id}/soft-delete # GDPR soft delete
POST   /api/v1/marketing/contacts/{id}/add-tags         # Add tags
POST   /api/v1/marketing/contacts/{id}/remove-tags      # Remove tags
POST   /api/v1/marketing/contacts/{id}/set-opt-in       # Set opt-in
GET    /api/v1/marketing/contacts/{id}/opt-in           # Check opt-in status
POST   /api/v1/marketing/contacts/sync                  # Sync from external
POST   /api/v1/marketing/contacts/{id}/activity         # Update activity
GET    /api/v1/marketing/contacts/stats                 # Contact statistics
```

### Vouchers

```
GET    /api/v1/marketing/vouchers               # List vouchers
GET    /api/v1/marketing/vouchers/{id}          # Show voucher
POST   /api/v1/marketing/vouchers               # Create voucher
PUT    /api/v1/marketing/vouchers/{id}          # Update voucher
DELETE /api/v1/marketing/vouchers/{id}          # Delete voucher
POST   /api/v1/marketing/vouchers/{id}/activate         # Activate
POST   /api/v1/marketing/vouchers/{id}/deactivate       # Deactivate
POST   /api/v1/marketing/vouchers/validate              # Validate code
POST   /api/v1/marketing/vouchers/{id}/use              # Redeem voucher
POST   /api/v1/marketing/vouchers/generate-codes        # Bulk generate
GET    /api/v1/marketing/vouchers/stats                 # Voucher statistics
```

### Deals

```
GET    /api/v1/marketing/deals                # List deals
GET    /api/v1/marketing/deals/{id}           # Show deal
POST   /api/v1/marketing/deals                # Create deal
PUT    /api/v1/marketing/deals/{id}           # Update deal
DELETE /api/v1/marketing/deals/{id}           # Delete deal
POST   /api/v1/marketing/deals/{id}/move-stage     # Move to next stage
POST   /api/v1/marketing/deals/{id}/activity       # Add activity
GET    /api/v1/marketing/deals/{id}/activities     # Get activities
POST   /api/v1/marketing/deals/{id}/won            # Close as won
POST   /api/v1/marketing/deals/{id}/lost           # Close as lost
GET    /api/v1/marketing/deals/stats               # Deal statistics
GET    /api/v1/marketing/deals/forecast            # Revenue forecast
GET    /api/v1/marketing/deals/overdue             # Overdue deals
```

### Sequences

```
GET    /api/v1/marketing/sequences              # List sequences
GET    /api/v1/marketing/sequences/{id}         # Show sequence
POST   /api/v1/marketing/sequences              # Create sequence
PUT    /api/v1/marketing/sequences/{id}         # Update sequence
DELETE /api/v1/marketing/sequences/{id}         # Delete sequence
POST   /api/v1/marketing/sequences/{id}/toggle          # Toggle active
POST   /api/v1/marketing/sequences/{id}/start-execution # Start execution
POST   /api/v1/marketing/sequences/{id}/execute         # Execute sequence
GET    /api/v1/marketing/sequences/stats                # Sequence statistics
```

### Workflows

```
GET    /api/v1/marketing/workflows              # List workflows
GET    /api/v1/marketing/workflows/{id}         # Show workflow
POST   /api/v1/marketing/workflows              # Create workflow
PUT    /api/v1/marketing/workflows/{id}         # Update workflow
DELETE /api/v1/marketing/workflows/{id}         # Delete workflow
POST   /api/v1/marketing/workflows/{id}/toggle          # Toggle active
POST   /api/v1/marketing/workflows/{id}/trigger         # Trigger execution
POST   /api/v1/marketing/workflows/{id}/execute         # Execute workflow
GET    /api/v1/marketing/workflows/stats                # Workflow statistics
```

### Lead Scores

```
GET    /api/v1/marketing/lead-scores              # List lead scores
GET    /api/v1/marketing/lead-scores/{id}         # Show score
GET    /api/v1/marketing/lead-scores/contact/{contactId}  # Score for contact
POST   /api/v1/marketing/lead-scores/contact/{contactId}/calculate  # Calculate score
PUT    /api/v1/marketing/lead-scores/contact/{contactId}  # Update score components
POST   /api/v1/marketing/lead-scores/bulk-recalculate         # Bulk recalculate
GET    /api/v1/marketing/lead-scores/top                # Top leads
GET    /api/v1/marketing/lead-scores/by-grade           # Leads by grade
GET    /api/v1/marketing/lead-scores/distribution       # Score distribution
GET    /api/v1/marketing/lead-scores/averages           # Average scores
GET    /api/v1/marketing/lead-scores/stats              # Lead score statistics
```

### Ad Integration

```
POST   /api/v1/marketing/ads/sync-google              # Sync Google Ads
POST   /api/v1/marketing/ads/sync-facebook            # Sync Facebook Ads
POST   /api/v1/marketing/ads/google-campaign          # Create Google campaign
POST   /api/v1/marketing/ads/facebook-campaign        # Create Facebook campaign
GET    /api/v1/marketing/ads/performance              # Performance summary
POST   /api/v1/marketing/ads/validate-credentials     # Validate API keys
POST   /api/v1/marketing/ads/pause-all                # Pause all campaigns
POST   /api/v1/marketing/ads/resume-all               # Resume all campaigns
```

### Analytics

```
GET    /api/v1/marketing/analytics/campaigns          # Campaign analytics
GET    /api/v1/marketing/analytics/channels           # Channel breakdown
GET    /api/v1/marketing/analytics/trends             # Time-series data
GET    /api/v1/marketing/analytics/roi                # ROI calculation
GET    /api/v1/marketing/analytics/attribution        # Attribution model
GET    /api/v1/marketing/analytics/acquisition        # Acquisition metrics
GET    /api/v1/marketing/analytics/vouchers           # Voucher analytics
GET    /api/v1/marketing/analytics/funnel             # Funnel metrics
GET    /api/v1/marketing/analytics/dashboard          # Dashboard data
```

---

## Code Examples

### Creating a Campaign

```php
// Via API
POST /api/v1/marketing/campaigns
{
    "name": "Summer Sale 2026",
    "description": "Multi-channel summer promotion",
    "type": "multi_channel",
    "channels": ["email", "sms", "whatsapp"],
    "start_date": "2026-07-01",
    "end_date": "2026-07-31",
    "budget": 5000.00,
    "goals": {
        "impressions": 100000,
        "clicks": 5000,
        "conversions": 500
    }
}

// Response
{
    "success": true,
    "data": {
        "id": 1,
        "code": "MKT-CAMP-00000001",
        "name": "Summer Sale 2026",
        "status": "draft",
        ...
    }
}
```

### Validating a Voucher

```php
// Via API
POST /api/v1/marketing/vouchers/validate
{
    "code": "SUMMER2026",
    "purchase_amount": 150.00,
    "product_ids": [1, 2, 3]
}

// Response
{
    "success": true,
    "valid": true,
    "voucher": {
        "code": "SUMMER2026",
        "discount_type": "percentage",
        "discount_value": 20,
        "min_purchase": 100.00
    },
    "discount_amount": 30.00
}
```

### Calculating Lead Score

```php
// Via API
POST /api/v1/marketing/lead-scores/contact/42/calculate

// Response
{
    "success": true,
    "data": {
        "contact_id": 42,
        "demographic_score": 65,
        "firmographic_score": 80,
        "engagement_score": 90,
        "behavioral_score": 75,
        "transactional_score": 85,
        "total_score": 79,
        "grade": "B",
        "tier": "warm"
    }
}
```

### Getting Funnel Metrics

```php
// Via API
GET /api/v1/marketing/analytics/funnel

// Response
{
    "success": true,
    "data": {
        "funnel": [
            {"stage": "awareness", "count": 15000, "rate": 100},
            {"stage": "interest", "count": 8000, "rate": 53.3},
            {"stage": "consideration", "count": 3500, "rate": 23.3},
            {"stage": "intent", "count": 1800, "rate": 12.0},
            {"stage": "conversion", "count": 650, "rate": 4.3}
        ],
        "overall_conversion_rate": 4.3,
        "dropoff_rates": [...]
    }
}
```

---

## Integration Points

### B-CRM Integration
- **Contact Sync**: Marketing contacts sync with CRM contacts via `crm_contact_id` foreign key
- **Deal Tracking**: Marketing deals link to CRM opportunities via `crm_deal_id`
- **Lead Conversion**: High-scoring leads (Grade A/B) can be converted to CRM customers

### B-Site Integration
- **Form Submissions**: Website form submissions automatically create marketing contacts
- **Event Tracking**: Website visits, email opens, clicks trigger workflow executions
- **Lead Capture**: Landing page forms feed directly into segmentation

### B-Marketplace Integration
- **Purchase Triggers**: Order completions trigger marketing sequences
- **Customer Segments**: Purchase history used for behavioral segmentation
- **Voucher Redemption**: Vouchers integrated with marketplace checkout

### B-Booking Integration
- **Appointment Triggers**: Booking events trigger follow-up sequences
- **Customer Reactivation**: Inactive customers receive re-engagement campaigns

---

## Configuration

### Config File: `config/marketing.php`

```php
return [
    // Campaign configuration
    'campaign' => [
        'types' => ['email', 'sms', 'whatsapp', 'social_media', 'paid_ads', 'multi_channel'],
        'statuses' => ['draft', 'scheduled', 'running', 'paused', 'completed', 'cancelled'],
        'code_prefix' => 'MKT-CAMP-',
    ],
    
    // Segment configuration
    'segment' => [
        'types' => ['static', 'dynamic'],
        'max_dynamic_criteria' => 20,
    ],
    
    // Contact configuration
    'contact' => [
        'gdpr_retention_days' => 365,
        'max_tags_per_contact' => 50,
        'opt_in_methods' => ['web_form', 'api', 'import', 'purchase', 'manual'],
    ],
    
    // Voucher configuration
    'voucher' => [
        'code_length' => 8,
        'code_charset' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        'discount_types' => ['percentage', 'fixed', 'free_shipping'],
        'max_uses_unlimited' => null,
    ],
    
    // Sequence configuration
    'sequence' => [
        'triggers' => ['new_contact', 'form_submission', 'purchase', 'website_visit', 'email_open', 'email_click'],
        'step_types' => ['send_email', 'send_sms', 'send_whatsapp', 'delay', 'wait_for_response'],
        'max_steps' => 20,
        'default_max_repetitions' => 1,
    ],
    
    // Workflow configuration
    'workflow' => [
        'trigger_types' => ['new_contact', 'form_submission', 'purchase', 'website_visit', 'email_open', 'email_click', 'campaign_start', 'scheduled'],
        'step_types' => ['send_email', 'send_sms', 'send_whatsapp', 'delay', 'update_contact', 'create_deal', 'add_tag', 'remove_tag'],
        'max_steps' => 50,
    ],
    
    // Lead scoring configuration
    'lead_score' => [
        'weight_demographic' => 0.15,
        'weight_firmographic' => 0.20,
        'weight_engagement' => 0.25,
        'weight_behavioral' => 0.20,
        'weight_transactional' => 0.20,
        'grades' => [
            ['grade' => 'A', 'min_score' => 80, 'tier' => 'hot'],
            ['grade' => 'B', 'min_score' => 60, 'tier' => 'warm'],
            ['grade' => 'C', 'min_score' => 40, 'tier' => 'lukewarm'],
            ['grade' => 'D', 'min_score' => 20, 'tier' => 'cold'],
            ['grade' => 'F', 'min_score' => 0, 'tier' => 'frozen'],
        ],
    ],
    
    // Ad platform configuration
    'ad_platforms' => [
        'google' => [
            'api_version' => 'v17',
            'endpoint' => 'https://googleads.googleapis.com/$discovery/rest',
        ],
        'facebook' => [
            'api_version' => 'v18.0',
            'endpoint' => 'https://graph.facebook.com/v18.0',
        ],
    ],
    
    // Analytics configuration
    'analytics' => [
        'attribution_models' => ['last_click', 'first_click', 'linear', 'time_decay', 'position_based'],
        'funnel_stages' => ['awareness', 'interest', 'consideration', 'intent', 'conversion'],
        'time_series_default_days' => 30,
    ],
    
    // Compliance configuration
    'compliance' => [
        'gdpr_enabled' => true,
        'can_spam_compliant' => true,
        'consent_required_countries' => ['US', 'CA', 'EU', 'MY', 'SG', 'AU'],
    ],
];
```

---

## Testing

### Unit Tests

```bash
# Run marketing module tests
php artisan test --filter=Marketing

# Run specific service tests
php artisan test --filter=CampaignServiceTest
php artisan test --filter=SegmentServiceTest
php artisan test --filter=LeadScoreServiceTest
```

### Feature Tests

```bash
# Test API endpoints
php artisan test --filter=MarketingCampaignApiTest
php artisan test --filter=MarketingContactApiTest
php artisan test --filter=MarketingVoucherApiTest
```

### Manual Testing Checklist

- [ ] Create campaign with single channel (email)
- [ ] Create campaign with multiple channels (email + SMS + WhatsApp)
- [ ] Create static segment with manual contact addition
- [ ] Create dynamic segment with criteria evaluation
- [ ] Create contact with opt-in preferences
- [ ] Generate voucher codes (single and bulk)
- [ ] Validate voucher with purchase amount
- [ ] Create deal and move through pipeline stages
- [ ] Add activity to deal (call, meeting, note)
- [ ] Create email sequence with multiple steps
- [ ] Trigger sequence execution for contact
- [ ] Create workflow with trigger and actions
- [ ] Calculate lead score for contact
- [ ] View lead score distribution
- [ ] Sync Google Ads performance data
- [ ] View analytics dashboard
- [ ] Test GDPR soft-delete on contact

---

## Deployment

### Migration

```bash
# Run migrations
php artisan migrate --path=database/migrations/2026_10_20_000900_create_marketing_tables.php

# Or run all pending migrations
php artisan migrate
```

### Queue Workers

Marketing module requires queue workers for:
- Email/SMS/WhatsApp delivery
- Sequence execution
- Workflow automation
- Ad sync operations

```bash
# Start queue worker
php artisan queue:work

# Or use supervisor for production
[program:marketing-queue]
command=php /path/to/artisan queue:work --tries=3
autostart=true
autorestart=true
user=www-data
```

### Scheduled Tasks

```php
// In app/Console/Kernel.php or routes/console.php
$schedule->command('marketing:sync-ads')->daily();
$schedule->command('marketing:execute-sequences')->everyFiveMinutes();
$schedule->command('marketing:execute-workflows')->everyMinute();
$schedule->command('marketing:recalculate-scores')->daily();
$schedule->command('marketing:cleanup-executions')->weekly();
```

---

## Performance Considerations

### Database Optimization

1. **Indexes**: All foreign keys indexed, composite indexes on tenant_id + status
2. **Partitioning**: Consider partitioning `marketing_workflow_executions` and `marketing_sequence_executions` by date for high-volume tenants
3. **Archiving**: Archive old executions quarterly to maintain query performance

### Caching Strategy

1. **Segment Counts**: Cache dynamic segment counts with 5-minute TTL
2. **Lead Scores**: Cache calculated scores with 1-hour TTL
3. **Analytics**: Cache dashboard data with 15-minute TTL
4. **Voucher Validation**: Cache voucher status with 1-minute TTL

### Queue Priorities

```php
// In config/queue.php
'connections' => [
    'marketing_high' => ['queue' => 'marketing_high'],  // Sequence/workflow execution
    'marketing_normal' => ['queue' => 'marketing'],      // Email/SMS delivery
    'marketing_low' => ['queue' => 'marketing_low'],     // Ad sync, analytics
],
```

---

## Security

### Data Isolation

All queries scoped to `tenant_id`. No cross-tenant data access possible.

### GDPR Compliance

- **Right to Erasure**: `softDelete()` method anonymizes personal data
- **Opt-in Tracking**: All contacts track consent method, timestamp, channel
- **Data Retention**: Configurable retention period (default 365 days)

### API Rate Limiting

```php
// In AppServiceProvider
RateLimiter::for('marketing', function ($request) {
    return Limit::perMinute(60)->by($request->user()->id);
});
```

---

## Troubleshooting

### Common Issues

**Issue**: Sequences not executing
- Check queue worker is running
- Verify sequence is_active = true
- Check trigger conditions match contact attributes

**Issue**: Lead scores not updating
- Ensure contact has required demographic data
- Check LeadScoreService weight calculations
- Run bulk recalculate if needed

**Issue**: Ad sync failing
- Validate API credentials first
- Check customer account ID format
- Verify date range is within allowed window

**Issue**: Voucher validation not working
- Check valid_from/until dates
- Verify min_purchase amount
- Confirm product applicability if specified

---

## Future Enhancements

- [ ] AI-powered subject line optimization
- [ ] A/B testing framework
- [ ] Predictive lead scoring with ML models
- [ ] SMS two-way conversation handling
- [ ] WhatsApp Business API integration
- [ ] TikTok Ads integration
- [ ] LinkedIn Ads integration
- [ ] Customer lifetime value prediction
- [ ] Churn prediction and prevention workflows
- [ ] Multi-language email templates

---

## Support

For issues or questions:
1. Check this README first
2. Review service class documentation in code comments
3. Check Laravel logs: `storage/logs/laravel.log`
4. Monitor queue failures in Horizon (if installed)

---

## License

Proprietary — AI Bijan SaaS Platform © 2026
