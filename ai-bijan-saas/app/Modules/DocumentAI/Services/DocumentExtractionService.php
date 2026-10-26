<?php

namespace App\Modules\DocumentAI\Services;

use App\Modules\DocumentAI\Models\Document;
use App\Modules\DocumentAI\Models\DocumentExtractionHistory;
use App\Modules\DocumentAI\Models\DocumentTemplate;

class DocumentExtractionService
{
    /**
     * Extract structured data from document using ML models.
     * 
     * Supported extraction types:
     * - Invoice: vendor, date, total, tax, line items
     * - Receipt: merchant, date, total, payment method
     * - Purchase Order: supplier, items, delivery date
     * - Contract: parties, effective date, terms
     */
    public function extractData(Document $document, string $extractionType = null): array
    {
        try {
            $startTime = microtime(true);
            
            // Determine extraction type if not specified
            if (!$extractionType) {
                $extractionType = $this->determineExtractionType($document);
            }
            
            // Get template for this type
            $template = $this->getTemplate($document->tenant_id, $extractionType);
            
            // Extract fields based on template
            $extractedFields = $this->runExtraction(
                $document,
                $extractionType,
                $template
            );
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            // Calculate overall confidence
            $confidence = $this->calculateExtractionConfidence($extractedFields);
            
            // Update document
            $document->update([
                'ml_extracted' => true,
                'ml_extracted_at' => now(),
                'extracted_fields' => $extractedFields,
                'ml_confidence' => $confidence,
                'status' => 'completed',
            ]);
            
            // Update metadata from extracted fields
            $this->updateDocumentMetadata($document, $extractedFields);
            
            // Record extraction history
            DocumentExtractionHistory::create([
                'document_id' => $document->id,
                'extraction_type' => "ml_{$extractionType}",
                'model_version' => config('services.ml.model_version', 'v1.0'),
                'input_data' => [
                    'document_type' => $document->document_type,
                    'template_id' => $template?->id,
                ],
                'output_data' => [
                    'fields_count' => count($extractedFields),
                    'fields' => array_keys($extractedFields),
                ],
                'confidence_score' => $confidence,
                'processing_time_ms' => $processingTime,
                'status' => 'success',
            ]);
            
            // Update template usage
            if ($template) {
                $template->incrementUsage($confidence);
            }
            
            return [
                'success' => true,
                'fields' => $extractedFields,
                'confidence' => $confidence,
                'extraction_type' => $extractionType,
                'processing_time_ms' => $processingTime,
            ];
            
        } catch (\Exception $e) {
            DocumentExtractionHistory::create([
                'document_id' => $document->id,
                'extraction_type' => 'ml_' . ($extractionType ?? 'unknown'),
                'model_version' => config('services.ml.model_version', 'v1.0'),
                'input_data' => ['document_type' => $document->document_type],
                'output_data' => null,
                'confidence_score' => 0,
                'processing_time_ms' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            $document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Determine extraction type based on document type and OCR text.
     */
    protected function determineExtractionType(Document $document): string
    {
        // Use document_type if set correctly
        if (in_array($document->document_type, ['invoice', 'receipt', 'purchase_order', 'contract'])) {
            return $document->document_type;
        }
        
        // Analyze OCR text to determine type
        $ocrText = $document->ocr_text ?? '';
        
        if (empty($ocrText)) {
            return 'custom';
        }
        
        // Keyword-based classification
        $invoiceKeywords = ['invoice', 'bil', 'tax invoice', 'sunset'];
        $receiptKeywords = ['receipt', 'resit', 'payment', 'bayaran'];
        $poKeywords = ['purchase order', 'order', 'tempahan', 'PO'];
        $contractKeywords = ['contract', 'perjanjian', 'agreement', 'syarat'];
        
        $textLower = mb_strtolower($ocrText);
        
        foreach ($invoiceKeywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return 'invoice';
            }
        }
        
        foreach ($receiptKeywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return 'receipt';
            }
        }
        
        foreach ($poKeywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return 'purchase_order';
            }
        }
        
        foreach ($contractKeywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return 'contract';
            }
        }
        
        return 'custom';
    }

    /**
     * Get template for document type.
     */
    protected function getTemplate(int $tenantId, string $type): ?DocumentTemplate
    {
        return DocumentTemplate::where('tenant_id', $tenantId)
            ->where('template_type', $type)
            ->active()
            ->first();
    }

    /**
     * Run ML extraction on document.
     */
    protected function runExtraction(Document $document, string $type, ?DocumentTemplate $template): array
    {
        // TODO: Integrate with ML model (TensorFlow, PyTorch, or cloud API)
        // For now, return placeholder based on template
        
        $fields = [];
        
        if ($template && isset($template->field_definitions)) {
            foreach ($template->field_definitions as $fieldName => $config) {
                $fields[$fieldName] = [
                    'value' => null,
                    'confidence' => 0,
                    'source' => 'pending',
                ];
            }
        }
        
        // Common fields for all document types
        $fields['document_date'] = ['value' => null, 'confidence' => 0, 'source' => 'pending'];
        $fields['total_amount'] = ['value' => null, 'confidence' => 0, 'source' => 'pending'];
        $fields['currency'] = ['value' => 'MYR', 'confidence' => 95, 'source' => 'default'];
        $fields['reference_number'] = ['value' => null, 'confidence' => 0, 'source' => 'pending'];
        
        return $fields;
    }

    /**
     * Calculate extraction confidence score.
     */
    protected function calculateExtractionConfidence(array $fields): float
    {
        $totalConfidence = 0;
        $count = 0;
        
        foreach ($fields as $field) {
            if (isset($field['confidence']) && $field['confidence'] > 0) {
                $totalConfidence += $field['confidence'];
                $count++;
            }
        }
        
        return $count > 0 ? round($totalConfidence / $count, 2) : 0;
    }

    /**
     * Update document metadata from extracted fields.
     */
    protected function updateDocumentMetadata(Document $document, array $fields): void
    {
        $metadataFields = [
            'document_date' => 'document_date',
            'sender_name' => 'sender_name',
            'recipient_name' => 'recipient_name',
            'total_amount' => 'total_amount',
            'currency' => 'currency',
            'reference_number' => 'reference_number',
        ];
        
        foreach ($metadataFields as $fieldKey => $metaField) {
            if (isset($fields[$fieldKey]) && $fields[$fieldKey]['value'] !== null) {
                $document->$metaField = $fields[$fieldKey]['value'];
            }
        }
        
        if ($document->isDirty()) {
            $document->save();
        }
    }

    /**
     * Extract specific field from document.
     */
    public function extractField(Document $document, string $fieldName): array
    {
        $extractedFields = $document->extracted_fields ?? [];
        
        if (!isset($extractedFields[$fieldName])) {
            return [
                'success' => false,
                'error' => "Field '{$fieldName}' not found in extraction results",
            ];
        }
        
        return [
            'success' => true,
            'field' => $fieldName,
            'value' => $extractedFields[$fieldName]['value'],
            'confidence' => $extractedFields[$fieldName]['confidence'],
        ];
    }

    /**
     * Batch extract from multiple documents.
     */
    public function batchExtract(array $documentIds, string $extractionType = null): array
    {
        $results = [];
        
        foreach ($documentIds as $id) {
            $document = Document::find($id);
            
            if ($document && $document->ocr_processed) {
                $result = $this->extractData($document, $extractionType);
                
                $results[] = [
                    'document_id' => $id,
                    'result' => $result,
                ];
            }
        }
        
        return $results;
    }
}
