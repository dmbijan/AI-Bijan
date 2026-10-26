# B-CRM — Customer Relationship Management & Sales Force Automation

## Gambaran Keseluruhan

B-CRM adalah modul lengkap untuk pengurusan hubungan pelanggan dan sales force automation yang direka khas untuk perniagaan di Malaysia. Modul ini merangkumi keseluruhan kitaran jualan dari lead generation hingga quotation approval.

## Ciri-ciri Utama

### 1. Customer Management
- **Customer Master Data**: Rekod lengkap pelanggan individu atau bisnis
- **Malaysian Compliance**: Sokongan SSM, SST Number, Tax ID untuk pelanggan bisnis
- **Credit Management**: Credit limit dan payment terms per pelanggan
- **Multiple Contacts**: Pelbagai contact person per customer
- **Classification**: Kategori retail, wholesale, distributor, enterprise, government
- **Banking Details**: Maklumat akaun bank untuk pembayaran

### 2. Lead Management
- **Lead Tracking**: Pengesan leads dari pelbagai sumber (website, referral, trade show)
- **Status Pipeline**: new → contacted → qualified → proposal → negotiation → won/lost
- **Priority Levels**: low, medium, high, urgent
- **Lead Assignment**: Tugaskan leads kepada salesperson
- **Lead Conversion**: Tukar leads kepada opportunities secara automatik
- **Loss Tracking**: Reason tracking untuk lost leads

### 3. Opportunity Management
- **Sales Pipeline**: Visualisasi pipeline dengan stages yang boleh disesuaikan
- **Probability Tracking**: Win probability berdasarkan stage
- **Forecast Category**: best_case, commit, oodled, pipeline
- **Competitor Analysis**: Tracking competitors dan win strategy
- **Next Action Planning**: Scheduled follow-ups dan reminders
- **Expected Revenue**: Kiraan expected revenue = estimated_value × probability

### 4. Quotation Management
- **Professional Quotations**: Quotation dengan format profesional
- **Line Items**: Multiple items dengan quantity, unit price, discount
- **Tax Calculation**: Auto-calculate SST (10% default)
- **Discount Support**: Percentage-based discounts
- **Approval Workflow**: Submit → Approve → Sent flow
- **Validity Period**: Valid until date dengan auto-expiry
- **Conversion**: Convert accepted quotations ke sales orders

## Struktur Database

### Tables

```sql
-- Customers
customers                  -- Customer master data
customer_contacts          -- Multiple contacts per customer

-- Leads
leads                      -- Lead tracking dan conversion

-- Opportunities  
opportunities              -- Sales opportunities dan pipeline

-- Quotations
quotations                 -- Quotation headers
quotation_lines            -- Quotation line items
```

### Key Relationships

```
Customer 1→N Leads
Customer 1→N Opportunities
Customer 1→N Quotations
Leads 1→1 Opportunities (after conversion)
Opportunities 1→N Quotations
Quotations 1→N Quotation Lines
```

## API Endpoints

### Customers
```
GET    /crm/customers              # List customers
POST   /crm/customers              # Create customer
GET    /crm/customers/{id}         # Show customer
PUT    /crm/customers/{id}         # Update customer
DELETE /crm/customers/{id}         # Delete customer
```

### Leads
```
GET    /crm/leads                  # List leads
POST   /crm/leads                  # Create lead
GET    /crm/leads/{id}             # Show lead
PUT    /crm/leads/{id}             # Update lead
POST   /crm/leads/{id}/convert     # Convert to opportunity
POST   /crm/leads/{id}/assign      # Assign to salesperson
GET    /crm/statistics/leads       # Lead statistics
```

### Opportunities
```
GET    /crm/opportunities          # List opportunities
POST   /crm/opportunities          # Create opportunity
GET    /crm/opportunities/{id}     # Show opportunity
PUT    /crm/opportunities/{id}     # Update opportunity
POST   /crm/opportunities/{id}/assign    # Assign opportunity
GET    /crm/statistics/pipeline    # Pipeline summary
GET    /crm/follow-ups             # Upcoming follow-ups
```

### Quotations
```
GET    /crm/quotations             # List quotations
POST   /crm/quotations             # Create quotation
GET    /crm/quotations/{id}        # Show quotation
PUT    /crm/quotations/{id}        # Update quotation
POST   /crm/quotations/{id}/submit # Submit for approval
POST   /crm/quotations/{id}/approve # Approve quotation
POST   /crm/quotations/{id}/convert-order # Convert to order
GET    /crm/statistics/quotations  # Quotation statistics
```

## Permissions

| Permission | Description |
|------------|-------------|
| `crm.view` | Lihat pelanggan, leads, opportunities & quotations |
| `crm.manage` | Urus pelanggan, leads, opportunities & quotations |
| `crm.approve` | Lulus quotations & teruskan ke sales order |
| `crm.reports` | Akses laporan CRM (pipeline, conversion, statistics) |

## Roles dengan Access CRM

### Sales Manager
```php
'sales_manager' => [
    'name' => 'Sales Manager',
    'permissions' => [
        'crm.view', 'crm.manage', 'crm.approve', 'crm.reports',
        // ... other permissions
    ]
]
```

### Sales Representative
```php
'sales_rep' => [
    'name' => 'Sales Representative',
    'permissions' => [
        'crm.view', 'crm.manage',
        // ... other permissions
    ]
]
```

### Finance Manager
```php
'finance_manager' => [
    'name' => 'Pengurus Kewangan',
    'permissions' => [
        'crm.view', 'crm.reports', // View only + reports
        // ... other permissions
    ]
]
```

## Services

### LeadService
```php
// Create lead
$lead = $leadService->createLead([
    'tenant_id' => 1,
    'source' => 'website',
    'name' => 'Ahmad Abdullah',
    'email' => 'ahmad@example.com',
    'company' => 'ABC Sdn Bhd',
    'estimated_value' => 50000,
]);

// Convert to opportunity
$opportunity = $leadService->convertToOpportunity($lead, [
    'title' => 'ERP Implementation Project',
    'estimated_value' => 50000,
]);

// Get statistics
$stats = $leadService->getLeadStatistics($tenantId);
```

### OpportunityService
```php
// Create opportunity
$opportunity = $opportunityService->createOpportunity([
    'tenant_id' => 1,
    'customer_id' => 1,
    'title' => 'Manufacturing ERP System',
    'estimated_value' => 150000,
    'expected_close_date' => '2026-12-31',
]);

// Update stage
$opportunity = $opportunityService->updateStage($opportunity, 'proposal', [
    'next_action' => 'Present proposal to client',
    'next_action_date' => '2026-11-15',
]);

// Get pipeline summary
$summary = $opportunityService->getPipelineSummary($tenantId);
```

### QuotationService
```php
// Create quotation with items
$quotation = $quotationService->createQuotation([
    'tenant_id' => 1,
    'customer_id' => 1,
    'opportunity_id' => 1,
    'valid_until' => '2026-12-31',
    'payment_terms_days' => 30,
    'lines' => [
        [
            'item_name' => 'ERP License - Enterprise',
            'quantity' => 1,
            'unit_price' => 50000,
        ],
        [
            'item_name' => 'Implementation Services',
            'quantity' => 100,
            'unit_price' => 500,
        ],
    ],
]);

// Approve quotation
$quotation = $quotationService->approveQuotation($quotation, auth()->id());

// Convert to sales order
$orderId = $quotationService->convertToOrder($quotation);
```

## Dashboard Metrics

### Lead Statistics
- Total leads
- New leads
- Contacted leads
- Qualified leads
- Won/Lost count
- Conversion rate (%)
- Total pipeline value

### Pipeline Summary
- Total opportunities
- Total pipeline value
- Total expected revenue
- By stage breakdown
- Won/Lost this month

### Quotation Statistics
- Total quotations
- Draft/Sent/Accepted/Rejected counts
- Conversion rate (%)
- Total quotation value

## Malaysian Compliance Features

### Customer Registration
- **SSM Number**: Untuk pelanggan bisnis (Suruhanjaya Syarikat Malaysia)
- **SST Number**: Pendaftaran Sales and Service Tax
- **Tax ID**: Business tax identification

### Quotation & Invoicing
- **SST Calculation**: Auto-calculate 10% SST (configurable)
- **Payment Terms**: Standard 30 days (customizable)
- **Currency**: MYR default dengan sokongan multi-currency

### Sales Process
- **Government Quotation**: Kategori khusus untuk kerajaan
- **Credit Control**: Credit limit checking sebelum quotation
- **Approval Workflow**: Multi-level approval untuk quotations besar

## Integration Points

### With B-Finance
```php
// When quotation is accepted, create AR
public function onQuotationAccepted(Quotation $quotation) {
    // Update customer credit utilization
    $customer = $quotation->customer;
    $customer->increment('credit_utilized', $quotation->total_amount);
    
    // Create pending AR in Finance module
    FinanceService::createPendingAR($quotation);
}
```

### With B-SCM
```php
// When quotation converted to order, create SO
public function convertToOrder(Quotation $quotation) {
    // Create Sales Order
    $salesOrder = SCMService::createSalesOrder([
        'customer_id' => $quotation->customer_id,
        'items' => $quotation->lines,
        'total_amount' => $quotation->total_amount,
    ]);
    
    return $salesOrder->id;
}
```

### With B-Payroll
```php
// Sales commission tracking
public function trackCommission(Opportunity $opportunity) {
    if ($opportunity->isWon()) {
        $commission = $opportunity->estimated_value * 0.02; // 2% commission
        
        // Record commission in payroll
        PayrollService::addCommission([
            'salesperson_id' => $opportunity->owner_id,
            'amount' => $commission,
            'reference' => $opportunity->opportunity_number,
        ]);
    }
}
```

## Development Status

### ✅ Completed
- [x] Module structure and service provider
- [x] Database migrations (customers, leads, opportunities, quotations)
- [x] Eloquent models dengan relationships
- [x] Services (LeadService, OpportunityService, QuotationService)
- [x] Controllers (CustomerController, LeadController, OpportunityController, QuotationController)
- [x] API routes dengan permission middleware
- [x] Laravel bootstrap registration
- [x] Permissions dan roles configuration
- [x] Comprehensive documentation

### 🔄 Next Steps
- [ ] Frontend Vue.js components
- [ ] Email integration untuk quotations
- [ ] PDF generation untuk quotations
- [ ] Advanced reporting dengan charts
- [ ] Lead scoring algorithm
- [ ] Automated follow-up reminders
- [ ] Bulk import/export functionality

## Testing

```bash
# Run CRM tests
php artisan test --filter=CRM

# Test lead conversion
php artisan test --filter=LeadConversionTest

# Test quotation workflow
php artisan test --filter=QuotationWorkflowTest
```

## Credits

Modul B-CRM dibangunkan sebagai sebahagian daripada AI-Bijan SaaS ERP system untuk pasaran Malaysia.
