# B-Sign & Docs — Document Management, Templates & Electronic Signatures

## Overview
Modul **B-Sign & Docs** menyediakan sistem pengurusan dokumen lengkap dengan sokongan templat, tandatangan elektronik (e-signature), dan workflow kelulusan. Modul ini direka bentuk untuk memenuhi keperluan organisasi dalam menguruskan dokumen secara digital dengan keselamatan dan kepatuhan.

## Features

### 📄 Document Management
- Upload pelbagai jenis fail (PDF, DOCX, XLSX, PPTX, JPG, PNG, dll.)
- Versioning automatik setiap kali fail dikemas kini
- Kategori & tag untuk pengorganisasian
- Perkongsian dokumen melalui link dengan tarikh luput
- Akses berasaskan peranan (private/shared/public)
- Watermark & sekatan muat turun/cetak
- Audit trail untuk setiap tindakan

### 📋 Template System
- Cipta templat untuk dokumen berulang (invoice, kontrak, proposal, dll.)
- Placeholder dinamik untuk pengisian data automatik
- Kategori templat (invoice, contract, proposal, report, letter, form, certificate)
- Penggunaan tracking untuk setiap templat
- Duplicate templat untuk variasi

### ✍️ Electronic Signatures (E-Signature)
4 jenis tandatangan:
1. **Drawn** — Lukis tandatangan menggunakan canvas/mouse/stylus
2. **Typed** — Taip nama sebagai tandatangan
3. **Uploaded** — Muat naik imej tandatangan
4. **Digital Certificate** — Tandatangan berdasarkan sijil digital (PKI)

Ciri-ciri tandatangan:
- Posisi boleh ditetapkan (x, y, page)
- Font customization untuk typed signature
- Audit trail lengkap dengan IP address & user agent
- Timestamp untuk setiap tandatangan
- Signing order support (berurutan/serentak)

### 🔄 Workflow & Approval
- Multi-step workflow (draft → review → approval → signature → signed)
- Assigned reviewers/approvers
- Concurrent atau sequential signing
- Auto-advance atau manual progression
- Rejection dengan alasan
- Reminder system (configured via cron)

### 📊 Analytics & Reporting
- Statistik dokumen (total, shared, signed, pending)
- Template usage analytics
- Signature completion tracking
- Download & view counters
- Export capabilities

## API Endpoints

### Documents
```
GET    /api/v1/sign-docs/documents              # List documents
GET    /api/v1/sign-docs/documents/{id}          # Get document
POST   /api/v1/sign-docs/documents               # Upload document
PUT    /api/v1/sign-docs/documents/{id}          # Update document
DELETE /api/v1/sign-docs/documents/{id}          # Delete document
GET    /api/v1/sign-docs/documents/{id}/download # Download document
POST   /api/v1/sign-docs/documents/{id}/share-link # Generate share link
GET    /api/v1/sign-docs/documents/{id}/versions # Get versions
GET    /api/v1/sign-docs/statistics              # Get statistics
GET    /api/v1/sign-docs/documents/shared        # Search shared docs
```

### Templates
```
GET    /api/v1/sign-docs/templates               # List templates
GET    /api/v1/sign-docs/templates/{id}          # Get template
POST   /api/v1/sign-docs/templates               # Create template
PUT    /api/v1/sign-docs/templates/{id}          # Update template
DELETE /api/v1/sign-docs/templates/{id}          # Delete template
POST   /api/v1/sign-docs/templates/{id}/generate # Generate from template
POST   /api/v1/sign-docs/templates/{id}/duplicate # Duplicate template
```

### Signatures
```
GET    /api/v1/sign-docs/documents/{id}/signatures     # List signatures
GET    /api/v1/sign-docs/signatures/pending             # Get pending signatures
POST   /api/v1/sign-docs/documents/{id}/signatures/drawn  # Create drawn signature
POST   /api/v1/sign-docs/documents/{id}/signatures/typed  # Create typed signature
POST   /api/v1/sign-docs/documents/{id}/signatures/upload # Upload signature image
POST   /api/v1/sign-docs/signatures/{id}/complete       # Complete signing
POST   /api/v1/sign-docs/signatures/{id}/reject         # Reject signature
GET    /api/v1/sign-docs/documents/{id}/signed-pdf      # Generate signed PDF
```

### Public Shared Access
```
GET    /api/v1/s/{shareLink}  # Access shared document
```

## Database Schema

### Tables
1. **sign_docs_documents** - Main document storage
2. **sign_docs_templates** - Document templates
3. **sign_docs_signatures** - Electronic signatures
4. **sign_docs_workflow_steps** - Workflow approval steps

### Key Fields

#### sign_docs_documents
| Field | Type | Description |
|-------|------|-------------|
| tenant_id | foreign key | Associated tenant |
| file_path | string | Storage path |
| version | integer | Document version |
| status | enum | draft, pending_review, in_review, pending_approval, approved, pending_signature, signed, archived, rejected |
| is_template | boolean | Whether this is a template |
| share_link | string | Unique share URL |
| access_level | enum | private, shared, public |

#### sign_docs_signatures
| Field | Type | Description |
|-------|------|-------------|
| signature_type | enum | drawn, typed, uploaded, digital_certificate |
| signature_data | json | Signature metadata |
| signature_image_path | string | Stored image path |
| status | enum | pending, signed, rejected, expired |
| audit_trail_data | json | Complete audit log |
| signed_at | timestamp | Signing timestamp |

## Configuration

File: `app/Modules/SignDocs/config/sign-docs.php`

```php
'documents' => [
    'max_file_size_mb' => 50,
    'allowed_types' => ['pdf', 'docx', 'xlsx', 'pptx', 'jpg', 'png'],
    'storage_path' => 'documents/',
    'version_history_enabled' => true,
    'max_versions' => 50,
],

'signatures' => [
    'types' => ['drawn', 'typed', 'uploaded', 'digital_certificate'],
    'timestamp_enabled' => true,
    'audit_trail_enabled' => true,
],

'workflow' => [
    'concurrent_signing' => true,
    'signing_order_required' => false,
    'reminder_interval_hours' => 24,
],
```

## Integration Points

### B-Site
- Embed shared documents in web pages
- Display digital signatures on published content
- Form-to-document generation

### B-CRM
- Sync contracts and proposals with contacts
- Automated document generation from CRM data
- Signature collection during deal closure

### B-Projects
- Project documentation management
- Contract templates for project agreements
- Client signature collection

### B-Marketing
- Marketing material templates
- Campaign document tracking
- Approved content library

### B-HRMS
- Employee document management
- Contract signing workflows
- Policy acknowledgment tracking

## Security Features

1. **Access Control**: Role-based permissions (view/manage/sign/reports)
2. **Audit Trail**: Every action logged with timestamp, IP, user agent
3. **Share Link Expiry**: Configurable expiration for shared documents
4. **Watermarking**: Optional custom watermarks
5. **Download Restrictions**: Control who can download
6. **Print Restrictions**: Control who can print
7. **Password Protection**: Optional document encryption

## Permission Structure

| Permission | View | Manage | Sign | Reports |
|------------|------|--------|------|---------|
| tenant_owner | ✅ | ✅ | ✅ | ✅ |
| tenant_admin | ✅ | ✅ | ✅ | ❌ |
| finance_manager | ✅ | ❌ | ❌ | ✅ |
| hr_manager | ✅ | ✅ | ✅ | ❌ |
| cashier | ✅ | ❌ | ❌ | ❌ |
| staff | ✅ | ❌ | ❌ | ❌ |
| auditor | ✅ | ❌ | ❌ | ✅ |

## Usage Examples

### Create Document
```javascript
const response = await fetch('/api/v1/sign-docs/documents', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer TOKEN' },
    body: formData // file + title + category
});
```

### Generate from Template
```javascript
const response = await fetch('/api/v1/sign-docs/templates/1/generate', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer TOKEN' },
    body: JSON.stringify({
        title: 'Contract - Acme Corp',
        data: {
            company_name: 'Acme Corporation',
            client_name: 'John Doe',
            amount: 'RM 5,000.00',
            date: '2026-10-20'
        }
    })
});
```

### Draw Signature
```javascript
// Capture canvas data
const signatureData = canvas.toDataURL();

const response = await fetch('/api/v1/sign-docs/documents/1/signatures/drawn', {
    method: 'POST',
    headers: { 
        'Authorization': 'Bearer TOKEN',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        signature_data: signatureData,
        position_x: 100,
        position_y: 200,
        page: 1
    })
});
```

### Complete Signing
```javascript
const response = await fetch('/api/v1/sign-docs/signatures/1/complete', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer TOKEN' }
});
```

## Best Practices

1. **Document Organization**: Use consistent categories and tags
2. **Template Management**: Regularly review and update templates
3. **Signature Security**: Enable audit trails for compliance
4. **Version Control**: Review version history before overwriting
5. **Share Links**: Set appropriate expiry dates
6. **Workflow Design**: Keep approval chains simple and clear

## Future Enhancements

- [ ] OCR for scanned documents
- [ ] AI-powered document classification
- [ ] Advanced PDF manipulation (merge, split, compress)
- [ ] Blockchain-based signature verification
- [ ] Multi-language support
- [ ] Collaborative editing
- [ ] Document comparison tool
- [ ] Bulk operations
- [ ] Email integration for signature collection
- [ ] Mobile app optimization
