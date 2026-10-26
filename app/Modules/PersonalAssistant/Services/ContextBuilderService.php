<?php

namespace App\Modules\PersonalAssistant\Services;

use App\Modules\PersonalAssistant\Models\MemoryItem;
use App\Modules\PersonalAssistant\Models\UserPreference;
use Illuminate\Support\Facades\DB;

/**
 * Context Builder (BAB 17A.5)
 *
 * Menggabungkan semua konteks relevan sebelum dihantar ke AI Gateway.
 * Memory persistent — model boleh ditukar tanpa hilang konteks.
 */
class ContextBuilderService
{
    /**
     * Bina konteks lengkap untuk permintaan AI
     */
    public function buildContext(int $tenantId, int $userId, string $assistantType, string $userQuery): array
    {
        $settings = config('personal_assistant.context_builder', []);

        $context = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'assistant_type' => $assistantType,
            'query' => $userQuery,
            'preferences' => null,
            'business_profile' => null,
            'conversation_history' => [],
            'memory_items' => [],
            'active_insights' => [],
            'documents' => [],
            'metadata' => [
                'built_at' => now()->toIso8601String(),
                'memory_preserved' => true,
            ],
        ];

        if ($settings['include_business_profile'] ?? true) {
            $context['preferences'] = $this->loadPreferences($tenantId, $userId);
            $context['business_profile'] = $this->loadBusinessProfile($tenantId);
        }

        if ($settings['include_conversation_history'] ?? true) {
            $context['conversation_history'] = $this->loadConversationHistory(
                $tenantId,
                $userId,
                $settings['conversation_history_limit'] ?? 20
            );
        }

        if ($settings['include_memory_items'] ?? true) {
            $context['memory_items'] = $this->loadMemoryItems(
                $tenantId,
                $userId,
                $settings['memory_items_limit'] ?? 30
            );
        }

        if ($settings['include_active_insights'] ?? true) {
            $context['active_insights'] = $this->loadActiveInsights(
                $tenantId,
                $settings['insights_limit'] ?? 10
            );
        }

        if ($settings['include_vector_search'] ?? false) {
            $context['documents'] = $this->vectorSearch(
                $tenantId,
                $userQuery,
                $settings['vector_search_top_k'] ?? 5
            );
        }

        // Anggaran token konteks
        $context['metadata']['estimated_tokens'] = $this->estimateTokens($context);
        $context['metadata']['max_context_tokens'] = $settings['max_context_tokens'] ?? 8000;

        return $context;
    }

    protected function loadPreferences(int $tenantId, int $userId): ?array
    {
        $prefs = UserPreference::forUser($tenantId, $userId);

        return [
            'language' => $prefs->language,
            'tone' => $prefs->tone,
            'report_format' => $prefs->report_format,
            'custom_instructions' => $prefs->custom_instructions,
        ];
    }

    protected function loadBusinessProfile(int $tenantId): ?array
    {
        $profile = DB::table('ai_business_profiles')
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$profile) {
            return null;
        }

        return [
            'business_name' => $profile->business_name,
            'industry_code' => $profile->industry_code,
            'business_model' => $profile->business_model,
            'products' => json_decode($profile->products_json ?? '[]', true),
            'customers_summary' => json_decode($profile->customers_summary_json ?? '[]', true),
            'org_structure' => json_decode($profile->org_structure_json ?? '[]', true),
            'custom_instructions' => $profile->custom_instructions,
            'language_pref' => $profile->language_pref,
            'tone_pref' => $profile->tone_pref,
        ];
    }

    protected function loadConversationHistory(int $tenantId, int $userId, int $limit): array
    {
        return DB::table('ai_conversations')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['messages_json', 'created_at'])
            ->map(fn ($row) => [
                'messages' => json_decode($row->messages_json, true),
                'at' => $row->created_at,
            ])
            ->toArray();
    }

    protected function loadMemoryItems(int $tenantId, int $userId, int $limit): array
    {
        return MemoryItem::forTenant($tenantId)
            ->forUser($userId)
            ->active()
            ->orderByDesc('is_verified')
            ->orderByDesc('confidence_score')
            ->limit($limit)
            ->get(['memory_type', 'category', 'content_text', 'confidence_score', 'is_verified', 'source'])
            ->toArray();
    }

    protected function loadActiveInsights(int $tenantId, int $limit): array
    {
        return DB::table('ai_derived_insights')
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['insight_type', 'data_json', 'confidence_score', 'created_at'])
            ->map(fn ($row) => [
                'type' => $row->insight_type,
                'data' => json_decode($row->data_json, true),
                'confidence' => $row->confidence_score,
                'at' => $row->created_at,
            ])
            ->toArray();
    }

    /**
     * Carian semantik dalam dokumen pengetahuan tenant (BAB 17A.4).
     *
     * Graceful degradation: pulangkan [] jika ciri dimatikan atau tiada dokumen.
     * Pengasingan tenant dikuatkuasakan oleh trait BelongsToTenant dalam
     * VectorStoreService — tiada laluan yang boleh membaca tenant lain.
     */
    protected function vectorSearch(int $tenantId, string $query, int $topK): array
    {
        if (trim($query) === '') {
            return [];
        }

        try {
            return app(VectorStoreService::class)->cari($query, $topK);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Vector search gagal', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Anggaran kasar bilangan token konteks
     */
    protected function estimateTokens(array $context): int
    {
        $json = json_encode($context);
        // Anggaran: ~4 aksara per token
        return (int) ceil(strlen($json) / 4);
    }

    /**
     * Semak jika konteks melebihi had
     */
    public function exceedsLimit(array $context): bool
    {
        $tokens = $context['metadata']['estimated_tokens'] ?? 0;
        $max = $context['metadata']['max_context_tokens'] ?? 8000;

        return $tokens > $max;
    }
}
