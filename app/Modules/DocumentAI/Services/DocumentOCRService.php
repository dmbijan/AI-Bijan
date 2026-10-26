<?php

namespace App\Modules\DocumentAI\Services;

use App\Modules\DocumentAI\Models\Document;
use App\Modules\DocumentAI\Models\DocumentExtractionHistory;
use Illuminate\Support\Facades\Storage;

class DocumentOCRService
{
    /**
     * Process document with OCR.
     * 
     * Supports:
     * - PDF documents (multi-page)
     * - Image files (PNG, JPG, JPEG, TIFF, BMP)
     * - Multi-language OCR (English, Bahasa Malaysia, Chinese)
     */
    public function processOCR(Document $document, string $filePath): array
    {
        try {
            $startTime = microtime(true);
            
            // Determine file type and process accordingly
            $mime = $document->file_mime;
            
            if (str_contains($mime, 'pdf')) {
                $text = $this->processPDF($filePath);
            } elseif (str_contains($mime, 'image')) {
                $text = $this->processImage($filePath);
            } else {
                throw new \InvalidArgumentException("Unsupported file type: {$mime}");
            }
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            // Calculate confidence score based on text quality
            $confidence = $this->calculateOCRConfidence($text);
            
            // Save results
            $document->update([
                'ocr_processed' => true,
                'ocr_processed_at' => now(),
                'ocr_text' => $text,
                'ocr_confidence' => $confidence,
                'status' => 'completed',
            ]);
            
            // Record extraction history
            DocumentExtractionHistory::create([
                'document_id' => $document->id,
                'extraction_type' => 'ocr',
                'model_version' => config('services.tesseract.version', 'v4.1'),
                'input_data' => [
                    'file_path' => $filePath,
                    'file_mime' => $mime,
                    'language' => config('app.locale', 'ms_MY'),
                ],
                'output_data' => [
                    'text_length' => mb_strlen($text),
                    'page_count' => str_contains($mime, 'pdf') ? $this->countPages($filePath) : 1,
                ],
                'confidence_score' => $confidence,
                'processing_time_ms' => $processingTime,
                'status' => 'success',
            ]);
            
            return [
                'success' => true,
                'text' => $text,
                'confidence' => $confidence,
                'processing_time_ms' => $processingTime,
            ];
            
        } catch (\Exception $e) {
            // Record failure
            DocumentExtractionHistory::create([
                'document_id' => $document->id,
                'extraction_type' => 'ocr',
                'model_version' => config('services.tesseract.version', 'v4.1'),
                'input_data' => ['file_path' => $filePath],
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
     * Process PDF document with OCR.
     */
    protected function processPDF(string $filePath): string
    {
        // TODO: Implement PDF OCR using Tesseract or AWS Textract
        // For now, return placeholder
        $pages = $this->extractPDFFrames($filePath);
        
        $texts = [];
        foreach ($pages as $page) {
            $text = $this->runTesseract($page);
            $texts[] = $text;
        }
        
        return implode("\n\n", $texts);
    }

    /**
     * Process image file with OCR.
     */
    protected function processImage(string $filePath): string
    {
        // Pre-process image for better OCR accuracy
        $preprocessed = $this->preprocessImage($filePath);
        
        return $this->runTesseract($preprocessed);
    }

    /**
     * Run Tesseract OCR on image.
     */
    protected function runTesseract(string $imagePath): string
    {
        // TODO: Integrate with Tesseract OCR or cloud API
        // Example using Tesseract CLI:
        // exec("tesseract {$imagePath} stdout -l msa+eng+chi_sim", $output);
        
        return ''; // Placeholder
    }

    /**
     * Preprocess image for better OCR.
     */
    protected function preprocessImage(string $filePath): string
    {
        // Apply image preprocessing:
        // - Convert to grayscale
        // - Increase contrast
        // - Remove noise
        // - Deskew
        
        $processedPath = Storage::path("processed/" . basename($filePath));
        
        // TODO: Implement image preprocessing with GD or Imagick
        
        return $processedPath;
    }

    /**
     * Extract frames from PDF pages.
     */
    protected function extractPDFFrames(string $pdfPath): array
    {
        // Convert each PDF page to image
        // TODO: Use pdftoppm or pdf2image
        
        return [];
    }

    /**
     * Count pages in PDF.
     */
    protected function countPages(string $pdfPath): int
    {
        // TODO: Count PDF pages
        
        return 1;
    }

    /**
     * Calculate OCR confidence score.
     */
    protected function calculateOCRConfidence(string $text): float
    {
        if (empty($text)) {
            return 0;
        }
        
        // Analyze text quality:
        // - Character recognition confidence
        // - Text coherence
        // - Language detection accuracy
        
        $charCount = mb_strlen($text);
        $wordCount = str_word_count($text);
        
        if ($wordCount === 0) {
            return 0;
        }
        
        // Simple heuristic: ratio of words to characters
        // Better OCR = more words per character
        $ratio = $wordCount / $charCount;
        
        // Scale to 0-100
        $confidence = min(100, $ratio * 100);
        
        return round($confidence, 2);
    }

    /**
     * Detect language from extracted text.
     */
    public function detectLanguage(string $text): string
    {
        // TODO: Implement language detection
        // Use PHP Language Detector or cloud API
        
        return 'ms_MY'; // Default to Malay
    }

    /**
     * Batch process multiple documents.
     */
    public function batchProcess(array $documentIds): array
    {
        $results = [];
        
        foreach ($documentIds as $id) {
            $document = Document::find($id);
            
            if ($document && $document->isReadyForProcessing()) {
                $filePath = Storage::path($document->file_path);
                $result = $this->processOCR($document, $filePath);
                
                $results[] = [
                    'document_id' => $id,
                    'result' => $result,
                ];
            }
        }
        
        return $results;
    }
}
