<?php

namespace App\Modules\AIAgents\Services;

use Illuminate\Support\Facades\DB;

class AgentMemoryManager
{
    /**
     * Save conversation to memory
     */
    public function saveConversation($tenantId, $agentKey, $userId, $messages)
    {
        DB::table('ai_conversations')->insert([
            'tenant_id' => $tenantId,
            'agent_key' => $agentKey,
            'user_id' => $userId,
            'messages_json' => json_encode($messages),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Get conversation history for agent
     */
    public function getConversationHistory($tenantId, $agentKey, $limit = 50)
    {
        return DB::table('ai_conversations')
            ->where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Save business profile context
     */
    public function saveBusinessProfile($tenantId, $profileData)
    {
        DB::table('ai_business_profiles')->updateOrInsert(
            ['tenant_id' => $tenantId],
            [
                'business_name' => $profileData['business_name'] ?? '',
                'industry_code' => $profileData['industry_code'] ?? '',
                'business_model' => $profileData['business_model'] ?? 'retail',
                'products_json' => json_encode($profileData['products'] ?? []),
                'customers_summary_json' => json_encode($profileData['customers_summary'] ?? []),
                'org_structure_json' => json_encode($profileData['org_structure'] ?? []),
                'custom_instructions' => $profileData['custom_instructions'] ?? '',
                'language_pref' => $profileData['language_pref'] ?? 'ms',
                'tone_pref' => $profileData['tone_pref'] ?? 'formal',
                'report_format' => $profileData['report_format'] ?? 'standard',
                'preferred_model' => $profileData['preferred_model'] ?? 'asai/minimax-m3',
                'updated_at' => now(),
            ]
        );

        return true;
    }

    /**
     * Get business profile for agent context
     */
    public function getBusinessProfile($tenantId)
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
            'products' => json_decode($profile->products_json, true),
            'customers_summary' => json_decode($profile->customers_summary_json, true),
            'org_structure' => json_decode($profile->org_structure_json, true),
            'custom_instructions' => $profile->custom_instructions,
            'language_pref' => $profile->language_pref,
            'tone_pref' => $profile->tone_pref,
            'report_format' => $profile->report_format,
            'preferred_model' => $profile->preferred_model,
        ];
    }

    /**
     * Save derived insights
     */
    public function saveInsight($tenantId, $agentKey, $insightType, $data)
    {
        DB::table('ai_derived_insights')->insert([
            'tenant_id' => $tenantId,
            'agent_key' => $agentKey,
            'insight_type' => $insightType,
            'data_json' => json_encode($data),
            'confidence_score' => $data['confidence'] ?? 0.8,
            'source' => 'ai_agent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Get recent insights
     */
    public function getRecentInsights($tenantId, $limit = 20)
    {
        return DB::table('ai_derived_insights')
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete conversation history
     */
    public function deleteConversation($tenantId, $conversationId)
    {
        DB::table('ai_conversations')
            ->where('tenant_id', $tenantId)
            ->where('id', $conversationId)
            ->delete();

        return true;
    }

    /**
     * Clear all memory for tenant (GDPR compliance)
     */
    public function clearTenantMemory($tenantId)
    {
        DB::table('ai_conversations')->where('tenant_id', $tenantId)->delete();
        DB::table('ai_derived_insights')->where('tenant_id', $tenantId)->delete();
        DB::table('ai_business_profiles')->where('tenant_id', $tenantId)->delete();

        return true;
    }
}
