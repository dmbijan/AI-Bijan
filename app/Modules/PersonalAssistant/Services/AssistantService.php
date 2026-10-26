<?php

namespace App\Modules\PersonalAssistant\Services;

use App\Modules\PersonalAssistant\Models\UserPreference;

class AssistantService
{
    /**
     * Senarai semua jenis personal assistant yang tersedia
     */
    public function listAssistants(): array
    {
        return config('personal_assistant.assistants', []);
    }

    /**
     * Dapatkan satu assistant mengikut kunci
     */
    public function getAssistant(string $key): ?array
    {
        return config("personal_assistant.assistants.{$key}");
    }

    /**
     * Cadangkan assistant berdasarkan role pengguna
     */
    public function recommendForRole(string $role): array
    {
        $map = [
            'tenant_owner' => ['owner', 'executive', 'finance'],
            'tenant_admin' => ['executive', 'operations'],
            'finance_manager' => ['finance', 'owner', 'executive'],
            'hr_manager' => ['hr', 'executive'],
            'inventory_manager' => ['operations', 'executive'],
            'cashier' => ['support', 'operations'],
            'staff' => ['support', 'operations'],
            'auditor' => ['finance', 'executive'],
        ];

        $keys = $map[$role] ?? ['operations'];

        return array_values(array_filter(
            array_map(fn ($k) => $this->getAssistant($k), $keys)
        ));
    }

    /**
     * Dapatkan preferensi pengguna (dicipta jika tiada)
     */
    public function getPreferences(int $tenantId, int $userId): UserPreference
    {
        return UserPreference::forUser($tenantId, $userId);
    }

    /**
     * Kemas kini preferensi pengguna
     */
    public function updatePreferences(int $tenantId, int $userId, array $data): UserPreference
    {
        $prefs = UserPreference::forUser($tenantId, $userId);

        $allowed = [
            'language', 'tone', 'report_format', 'preferred_model',
            'default_assistant', 'timezone', 'notification_channels',
            'quiet_hours_start', 'quiet_hours_end', 'custom_instructions',
        ];

        $prefs->fill(array_intersect_key($data, array_flip($allowed)));
        $prefs->save();

        return $prefs->fresh();
    }

    /**
     * Tukar model AI tanpa kehilangan memori (BAB 17A.6)
     *
     * Memori kekal sama; hanya "otak" model berubah.
     */
    public function switchModel(int $tenantId, int $userId, string $modelId): array
    {
        $prefs = UserPreference::forUser($tenantId, $userId);

        // Sahkan model wujud dalam katalog (bila modul AI Gateway tersedia)
        $availableModels = $this->getAvailableModels();
        if (!empty($availableModels) && !in_array($modelId, array_column($availableModels, 'provider_model_id'), true)) {
            throw new \InvalidArgumentException("Model '{$modelId}' tidak tersedia dalam katalog.");
        }

        $previous = $prefs->preferred_model;
        $prefs->preferred_model = $modelId;
        $prefs->save();

        return [
            'previous_model' => $previous,
            'current_model' => $modelId,
            'memory_preserved' => true,
            'message' => 'Model ditukar. Semua memori, profil bisnes dan sejarah perbualan kekal sama.',
        ];
    }

    /**
     * Senarai model yang tersedia (dari AI Gateway jika wujud)
     */
    protected function getAvailableModels(): array
    {
        // Jika modul AI Gateway wujud, guna katalognya
        if (class_exists(\App\Modules\AIGateway\Models\AiModel::class)) {
            return \App\Modules\AIGateway\Models\AiModel::where('is_active', true)
                ->get(['provider_model_id', 'display_name'])
                ->toArray();
        }

        return [];
    }

    /**
     * Dapatkan konfigurasi proactive alerts
     */
    public function getProactiveAlertTypes(): array
    {
        return config('personal_assistant.proactive_alerts', []);
    }
}
