<?php

namespace App\Modules\SignDocs\Services;

use Illuminate\Support\Facades\DB;

class TemplateService
{
    /**
     * Create a new template
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $template = \App\Modules\SignDocs\Models\Template::create([
                'tenant_id' => $data['tenant_id'],
                'user_id' => $data['user_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'category' => $data['category'] ?? 'other',
                'content' => $data['content'] ?? '',
                'placeholders' => $data['placeholders'] ?? [],
                'file_path' => $data['file_path'] ?? null,
                'file_type' => $data['file_type'] ?? null,
                'thumbnail_path' => $data['thumbnail_path'] ?? null,
                'is_active' => true,
                'tags' => $data['tags'] ?? [],
                'metadata' => $data['metadata'] ?? [],
            ]);

            return $template;
        });
    }

    /**
     * Update template
     */
    public function update($templateId, array $data)
    {
        $template = \App\Modules\SignDocs\Models\Template::findOrFail($templateId);
        $template->update($data);
        return $template;
    }

    /**
     * Delete template
     */
    public function delete($templateId)
    {
        $template = \App\Modules\SignDocs\Models\Template::findOrFail($templateId);
        $template->delete();
        return true;
    }

    /**
     * Generate document from template
     */
    public function generateFromTemplate($templateId, $data)
    {
        $template = \App\Modules\SignDocs\Models\Template::findOrFail($templateId);

        // Replace placeholders in content
        $content = $template->replacePlaceholders($template->content, $data);

        // Create document from template
        $document = \App\Modules\SignDocs\Models\Document::create([
            'tenant_id' => app(TenantContext::class)->id(),
            'user_id' => auth()->id(),
            'title' => $data['title'] ?? $template->name,
            'description' => 'Generated from template: ' . $template->name,
            'file_path' => $data['file_path'] ?? null,
            'file_type' => $data['file_type'] ?? null,
            'file_size' => $data['file_size'] ?? 0,
            'mime_type' => $data['mime_type'] ?? null,
            'category' => $template->category,
            'tags' => $template->tags ?? [],
            'version' => 1,
            'status' => 'draft',
            'is_template' => false,
            'template_id' => $template->id,
            'access_level' => 'private',
            'metadata' => [
                'template_name' => $template->name,
                'generated_data' => $data,
            ],
        ]);

        // Increment template usage count
        $template->incrementUsageCount();

        return $document;
    }

    /**
     * Get all templates
     */
    public function getAll($tenantId, $filters = [])
    {
        $query = \App\Modules\SignDocs\Models\Template::where('tenant_id', $tenantId);

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['active_only'])) {
            $query->active();
        }

        return $query->orderBy('usage_count', 'desc')->orderBy('updated_at', 'desc')->paginate(20);
    }

    /**
     * Get template by ID
     */
    public function getById($templateId)
    {
        return \App\Modules\SignDocs\Models\Template::findOrFail($templateId);
    }

    /**
     * Duplicate template
     */
    public function duplicate($templateId)
    {
        $template = \App\Modules\SignDocs\Models\Template::findOrFail($templateId);

        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->save();

        return $newTemplate;
    }
}
