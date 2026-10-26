<?php

namespace App\Modules\DocumentAI\Services;

use App\Modules\DocumentAI\Models\Document;
use App\Modules\DocumentAI\Models\DocumentExtractionHistory;

class DocumentClassificationService
{
    /**
     * Classify document into categories.
     * 
     * Uses ML model to automatically classify documents:
     * - By type (invoice, receipt, contract, etc.)
     * - By industry (manufacturing, retail, services, etc.)
     * - By sensitivity (public, confidential, restricted)
     */
    public function classify(Document $document): array
    {
        try {
            $startTime = microtime(true);
            
            // Use OCR text if available, otherwise use file metadata
            $text = $document->ocr_text ?? '';
            
            // Multi-label classification
            $classification = $this->runClassification($text, $document);
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            // Update document with classification results
            $document->update([
                'classification_status' => $classification['confidence'] > 80 ? 'classified' : 'review_required',
                'classification_label' => $classification['label'],
                'classification_confidence' => $classification['confidence'],
                'document_type' => $classification['type'] ?? $document->document_type,
            ]);
            
            // Record classification history
            DocumentExtractionHistory::create([
                'document_id' => $document->id,
                'extraction_type' => 'classification',
                'model_version' => config('services.ml.classification_model', 'v1.0'),
                'input_data' => [
                    'text_length' => mb_strlen($text),
                    'file_mime' => $document->file_mime,
                ],
                'output_data' => [
                    'label' => $classification['label'],
                    'type' => $classification['type'],
                    'confidence' => $classification['confidence'],
                    'categories' => $classification['categories'] ?? [],
                ],
                'confidence_score' => $classification['confidence'],
                'processing_time_ms' => $processingTime,
                'status' => 'success',
            ]);
            
            return $classification;
            
        } catch (\Exception $e) {
            $document->update([
                'classification_status' => 'review_required',
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'label' => 'uncategorized',
                'confidence' => 0,
            ];
        }
    }

    /**
     * Run ML classification on document.
     */
    protected function runClassification(string $text, Document $document): array
    {
        // TODO: Integrate with ML classification model
        // For now, use rule-based classification
        
        $result = [
            'label' => 'uncategorized',
            'type' => null,
            'confidence' => 0,
            'categories' => [],
        ];
        
        if (empty($text)) {
            // Classify based on file extension and name
            $result = $this->classifyByMetadata($document);
        } else {
            // Classify based on text content
            $result = $this->classifyByText($text);
        }
        
        return $result;
    }

    /**
     * Classify document based on metadata.
     */
    protected function classifyByMetadata(Document $document): array
    {
        $filename = basename($document->file_path);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Check filename patterns
        $patterns = [
            '/invoice|bil/i' => ['label' => 'invoice', 'type' => 'invoice', 'confidence' => 70],
            '/receipt|resit/i' => ['label' => 'receipt', 'type' => 'receipt', 'confidence' => 70],
            '/purchase.?order|tempahan/i' => ['label' => 'purchase_order', 'type' => 'purchase_order', 'confidence' => 70],
            '/contract|perjanjian/i' => ['label' => 'contract', 'type' => 'contract', 'confidence' => 70],
            '/statement|penyata/i' => ['label' => 'bank_statement', 'type' => 'bank_statement', 'confidence' => 65],
        ];
        
        foreach ($patterns as $pattern => $result) {
            if (preg_match($pattern, $filename)) {
                return $result;
            }
        }
        
        return ['label' => 'uncategorized', 'type' => null, 'confidence' => 30];
    }

    /**
     * Classify document based on text content.
     */
    protected function classifyByText(string $text): array
    {
        $textLower = mb_strtolower($text);
        
        // Invoice detection
        $invoiceIndicators = [
            'invoice' => 95,
            'bil' => 90,
            'tax invoice' => 92,
            'payment due' => 85,
            'amount due' => 80,
        ];
        
        // Receipt detection
        $receiptIndicators = [
            'receipt' => 95,
            'resit' => 90,
            'thank you for your purchase' => 85,
            'payment received' => 80,
        ];
        
        // Contract detection
        $contractIndicators = [
            'agreement' => 90,
            'perjanjian' => 88,
            'terms and conditions' => 85,
            'party of the first part' => 80,
        ];
        
        // Purchase Order detection
        $poIndicators = [
            'purchase order' => 95,
            'tempahan' => 85,
            'order number' => 80,
        ];
        
        // Calculate scores
        $scores = [
            'invoice' => 0,
            'receipt' => 0,
            'contract' => 0,
            'purchase_order' => 0,
        ];
        
        foreach ($invoiceIndicators as $keyword => $score) {
            if (str_contains($textLower, $keyword)) {
                $scores['invoice'] = max($scores['invoice'], $score);
            }
        }
        
        foreach ($receiptIndicators as $keyword => $score) {
            if (str_contains($textLower, $keyword)) {
                $scores['receipt'] = max($scores['receipt'], $score);
            }
        }
        
        foreach ($contractIndicators as $keyword => $score) {
            if (str_contains($textLower, $keyword)) {
                $scores['contract'] = max($scores['contract'], $score);
            }
        }
        
        foreach ($poIndicators as $keyword => $score) {
            if (str_contains($textLower, $keyword)) {
                $scores['purchase_order'] = max($scores['purchase_order'], $score);
            }
        }
        
        // Find highest scoring category
        $bestCategory = array_search(max($scores), $scores);
        $bestScore = $scores[$bestCategory];
        
        if ($bestScore > 50) {
            return [
                'label' => $bestCategory,
                'type' => $bestCategory,
                'confidence' => $bestScore,
                'categories' => array_filter($scores, fn($score) => $score > 50),
            ];
        }
        
        return [
            'label' => 'uncategorized',
            'type' => null,
            'confidence' => min($bestScore, 50),
            'categories' => [],
        ];
    }

    /**
     * Get classification statistics for tenant.
     */
    public function getClassificationStats(int $tenantId): array
    {
        $query = Document::where('tenant_id', $tenantId);
        
        return [
            'total_documents' => (clone $query)->count(),
            'classified' => (clone $query)->where('classification_status', 'classified')->count(),
            'review_required' => (clone $query)->where('classification_status', 'review_required')->count(),
            'pending' => (clone $query)->where('classification_status', 'pending')->count(),
            
            'by_type' => [
                'invoice' => (clone $query)->where('document_type', 'invoice')->count(),
                'receipt' => (clone $query)->where('document_type', 'receipt')->count(),
                'purchase_order' => (clone $query)->where('document_type', 'purchase_order')->count(),
                'contract' => (clone $query)->where('document_type', 'contract')->count(),
                'other' => (clone $query)->whereNotIn('document_type', ['invoice', 'receipt', 'purchase_order', 'contract'])->count(),
            ],
            
            'average_confidence' => Document::where('tenant_id', $tenantId)
                ->whereNotNull('classification_confidence')
                ->avg('classification_confidence'),
        ];
    }
}
