# B-Document AI — Intelligent Document Processing

## Gambaran Keseluruhan

B-Document AI adalah modul pemprosesan dokumen pintar yang menggunakan **OCR (Optical Character Recognition)** dan **ML (Machine Learning)** untuk mengekstrak data secara automatik dari dokumen. Direka khas untuk perniagaan di Malaysia dengan sokongan multi-bahasa.

## Ciri-ciri Utama

### 1. OCR Processing
- **Multi-format**: PDF, PNG, JPG, JPEG, TIFF, BMP
- **Multi-language**: English, Bahasa Malaysia, Chinese (Simplified)
- **Confidence Scoring**: Setiap extraction ada confidence score
- **Preprocessing**: Image enhancement untuk accuracy lebih tinggi
- **Batch Processing**: Process multiple documents sekaligus

### 2. ML Data Extraction
- **Invoice Extraction**: Vendor, date, total, tax, line items
- **Receipt Extraction**: Merchant, date, total, payment method
- **Purchase Order**: Supplier, items, delivery date
- **Contract Analysis**: Parties, effective date, key terms
- **Custom Templates**: Define your own field extraction rules

### 3. Document Classification
- **Auto-classification**: Automatically categorize documents
- **Type Detection**: Invoice, receipt, contract, PO, etc.
- **Confidence Threshold**: Flag low-confidence for review
- **Industry-specific**: Custom categories per industry

### 4. Document Management
- **Version Control**: Track document changes
- **Share & Access**: Secure sharing with external parties
- **Retention Policy**: Automatic retention and archival
- **Confidentiality**: Mark sensitive documents
- **Search**: Full-text search on OCR'd documents

## Architecture

```
┌─────────────┐
│   Upload    │ ← User uploads document
└──────┬──────┘
       ↓
┌─────────────┐
│  Validate   │ ← File type, size, hash
└──────┬──────┘
       ↓
┌─────────────┐
│    OCR      │ ← Extract text from image/PDF
└──────┬──────┘
       ↓
┌─────────────┐
│  Classify   │ ← Determine document type
└──────┬──────┘
       ↓
┌─────────────┐
│  Extract    │ ← ML model extracts fields
└──────┬──────┘
       ↓
┌─────────────┐
│  Store      │ ← Save to database + metadata
└─────────────┘
```

## Struktur Database

### Tables

```sql
documents                    -- Document master with OCR/ML results
document_versions            -- Version control
document_extraction_history  -- Audit trail for all extractions
document_templates           -- ML training templates
document_shares              -- External sharing
```

### Key Fields

**documents:**
- `ocr_text` — Raw extracted text
- `ocr_confidence` — OCR accuracy score (0-100)
- `extracted_fields` — JSON structured data from ML
- `ml_confidence` — ML extraction accuracy
- `classification_label` — Auto-detected type
- `status` — uploaded → processing → completed/failed

## API Endpoints

### Documents
```
GET    /documents                  # List documents
POST   /documents                  # Upload document
GET    /documents/{id}             # Show document
PUT    /documents/{id}             # Update document
DELETE /documents/{id}             # Delete document
POST   /documents/{id}/reprocess   # Re-run processing pipeline
GET    /documents/{id}/download    # Download file
GET    /documents/statistics       # Processing statistics
```

## Permissions

| Permission | Description |
|------------|-------------|
| `documents.view` | Lihat & muat turun dokumen |
| `documents.issue` | Keluarkan & proses dokumen |
| `documents.sensitive` | Akses dokumen sulit |
| `documents.void` | Batalkan dokumen |

## Services

### DocumentOCRService

```php
// Process single document
$ocrService = app(DocumentOCRService::class);
$result = $ocrService->processOCR($document, $filePath);

// Result:
// [
//     'success' => true,
//     'text' => 'Extracted text content...',
//     'confidence' => 92.5,
//     'processing_time_ms' => 1250,
// ]

// Batch process
$results = $ocrService->batchProcess([$docId1, $docId2]);
```

### DocumentExtractionService

```php
// Extract data using ML
$extractionService = app(DocumentExtractionService::class);
$result = $extractionService->extractData($document);

// Result:
// [
//     'success' => true,
//     'fields' => [
//         'invoice_number' => ['value' => 'INV-001', 'confidence' => 98],
//         'total_amount' => ['value' => 1500.00, 'confidence' => 95],
//         'document_date' => ['value' => '2026-10-19', 'confidence' => 92],
//     ],
//     'confidence' => 95.0,
//     'extraction_type' => 'invoice',
// ]

// Extract specific field
$fieldResult = $extractionService->extractField($document, 'total_amount');
```

### DocumentClassificationService

```php
// Classify document
$classificationService = app(DocumentClassificationService::class);
$result = $classificationService->classify($document);

// Result:
// [
//     'label' => 'invoice',
//     'type' => 'invoice',
//     'confidence' => 94.5,
//     'categories' => ['invoice' => 94.5, 'receipt' => 15.2],
// ]

// Get classification stats
$stats = $classificationService->getClassificationStats($tenantId);
```

## Controllers

### DocumentController

```php
// Upload document
POST /documents
{
    "title": "Invoice ABC Sdn Bhd",
    "description": "Invoice for October services",
    "document_type": "invoice",
    "file": <binary>,
    "tags": ["vendor", "october"],
    "assigned_to": 5
}

// Reprocess document
POST /documents/{id}/reprocess

// Get statistics
GET /documents/statistics
```

## Integration Points

### With B-Finance
```php
// When invoice is processed, create AP entry
public function onDocumentProcessed(Document $document) {
    if ($document->document_type === 'invoice' && $document->ml_extracted) {
        $fields = $document->extracted_fields;
        
        // Create Accounts Payable
        FinanceService::createAP([
            'supplier_name' => $fields['vendor_name']['value'],
            'invoice_number' => $fields['invoice_number']['value'],
            'amount' => $fields['total_amount']['value'],
            'due_date' => $fields['payment_due_date']['value'],
            'reference_document' => $document->id,
        ]);
    }
}
```

### With B-SCM
```php
// When PO document is processed
public function onPOProcessed(Document $document) {
    if ($document->document_type === 'purchase_order') {
        $fields = $document->extracted_fields;
        
        // Create SCM Purchase Order
        SCMService::createPO([
            'supplier_id' => $this->findSupplier($fields['vendor_name']['value']),
            'po_number' => $fields['po_number']['value'],
            'items' => $fields['line_items']['value'],
            'delivery_date' => $fields['delivery_date']['value'],
        ]);
    }
}
```

### With B-CRM
```php
// When contract document is processed
public function onContractProcessed(Document $document) {
    if ($document->document_type === 'contract') {
        $fields = $document->extracted_fields;
        
        // Link to opportunity
        Opportunity::where('reference_number', $fields['contract_number']['value'])
            ->update(['stage' => 'closed_won']);
            
        // Trigger onboarding workflow
        OnboardingService::start($document);
    }
}
```

## Malaysian Compliance Features

### Multi-language OCR
- **Bahasa Malaysia**: Primary language support
- **English**: Business language
- **Chinese**: For Chinese business community
- **Tamil**: For Indian business community

### Document Retention
- **Companies Act 2016**: 7 years retention for financial documents
- **Tax Act 1967**: 7 years for tax records
- **Employment Act 1955**: 6 years for employment records

### Data Privacy
- **PDPA 2010**: Personal Data Protection Act compliance
- **Encryption**: File encryption at rest
- **Access Control**: Role-based access to sensitive documents
- **Audit Trail**: Complete extraction history logging

## Configuration

### .env Settings
```env
# OCR Configuration
OCR_ENGINE=tesseract
OCR_LANGUAGE=msa+eng+chi_sim
OCR_CONFIDENCE_THRESHOLD=70

# ML Configuration
ML_MODEL_VERSION=v1.0
ML_EXTRACTION_ENDPOINT=http://ml-service:8080/predict
ML_CONFIDENCE_THRESHOLD=80

# Document Storage
DOCUMENT_STORAGE_PATH=storage/app/public/documents
MAX_FILE_SIZE=52428800  # 50MB
ALLOWED_MIMES=pdf,png,jpg,jpeg,tiff,bmp,gif
```

### Config File
```php
// config/services.php
'ocr' => [
    'engine' => env('OCR_ENGINE', 'tesseract'),
    'language' => env('OCR_LANGUAGE', 'msa+eng'),
    'confidence_threshold' => env('OCR_CONFIDENCE_THRESHOLD', 70),
],

'ml' => [
    'model_version' => env('ML_MODEL_VERSION', 'v1.0'),
    'extraction_endpoint' => env('ML_EXTRACTION_ENDPOINT'),
    'confidence_threshold' => env('ML_CONFIDENCE_THRESHOLD', 80),
],
```

## Development Status

### ✅ Completed
- [x] Module structure and service provider
- [x] Database migrations (5 tables)
- [x] Eloquent models dengan relationships
- [x] OCR Service (placeholder integration)
- [x] ML Extraction Service (template-based)
- [x] Classification Service (rule-based)
- [x] Controllers (DocumentController)
- [x] API routes dengan permission middleware
- [x] Laravel bootstrap registration
- [x] Comprehensive documentation

### 🔄 Next Steps
- [ ] Tesseract OCR integration
- [ ] Cloud OCR APIs (AWS Textract, Azure Form Recognizer)
- [ ] ML model training pipeline
- [ ] Vue.js document upload component
- [ ] PDF preview in browser
- [ ] Advanced search with filters
- [ ] Bulk upload functionality
- [ ] Email-to-document conversion

## Testing

```bash
# Run Document AI tests
php artisan test --filter=DocumentAI

# Test OCR processing
php artisan test --filter=OCRProcessingTest

# Test ML extraction
php artisan test --filter=MLExtractionTest

# Test classification
php artisan test --filter=DocumentClassificationTest
```

## Performance Benchmarks

| Metric | Target | Current |
|--------|--------|---------|
| OCR Processing Time | < 5 seconds/page | N/A |
| ML Extraction Time | < 3 seconds | N/A |
| Classification Accuracy | > 90% | ~70% (rule-based) |
| Field Extraction Accuracy | > 85% | N/A |
| Batch Processing Speed | 10 docs/min | N/A |

## Credits

Modul B-Document AI dibangunkan sebagai sebahagian daripada AI-Bijan SaaS ERP system untuk pasaran Malaysia.
