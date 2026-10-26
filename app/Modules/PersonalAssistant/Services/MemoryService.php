<?php

namespace App\Modules\PersonalAssistant\Services;

use App\Modules\PersonalAssistant\Models\MemoryItem;
use Illuminate\Support\Facades\DB;

class MemoryService
{
    /**
     * Simpan item memori
     */
    public function store(
        int $tenantId,
        ?int $userId,
        string $type,
        string $category,
        string $content,
        array $options = []
    ): MemoryItem {
        $this->validateType($type);

        return MemoryItem::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'memory_type' => $type,
            'category' => $category,
            'content_text' => $content,
            'content_json' => $options['content_json'] ?? null,
            'embedding_id' => $options['embedding_id'] ?? null,
            'source' => $options['source'] ?? 'user_input',
            'confidence_score' => $options['confidence'] ?? 1.00,
            'is_verified' => $options['is_verified'] ?? ($options['source'] ?? 'user_input') === 'user_input',
            'expires_at' => $options['expires_at'] ?? null,
        ]);
    }

    /**
     * Dapatkan memori aktif untuk pengguna
     */
    public function getForUser(int $tenantId, int $userId, ?string $type = null, int $limit = 50): array
    {
        $query = MemoryItem::forTenant($tenantId)
            ->forUser($userId)
            ->active()
            ->orderByDesc('is_verified')
            ->orderByDesc('created_at');

        if ($type) {
            $query->byType($type);
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Kemas kini kandungan memori (review/correction)
     */
    public function update(MemoryItem $item, array $data): MemoryItem
    {
        $allowed = ['content_text', 'content_json', 'confidence_score', 'is_verified', 'expires_at'];
        $item->fill(array_intersect_key($data, array_flip($allowed)));
        $item->save();

        return $item->fresh();
    }

    /**
     * Padam item memori
     */
    public function delete(MemoryItem $item): bool
    {
        return (bool) $item->delete();
    }

    /**
     * Padam semua memori peribadi pengguna (PDPA data subject request)
     *
     * NOTA: Rekod kewangan/statutori TIDAK dipadam — kekal ikut keperluan undang-undang.
     */
    public function purgeUserMemory(int $tenantId, int $userId): array
    {
        return DB::transaction(function () use ($tenantId, $userId) {
            $memoryCount = MemoryItem::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->delete();

            $conversationCount = DB::table('ai_conversations')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->delete();

            $preferenceCount = DB::table('ai_user_preferences')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->delete();

            return [
                'memory_items_deleted' => $memoryCount,
                'conversations_deleted' => $conversationCount,
                'preferences_deleted' => $preferenceCount,
                'note' => 'Rekod kewangan dan statutori dikekalkan mengikut keperluan undang-undang.',
            ];
        });
    }

    /**
     * Buang memori yang telah tamat tempoh
     */
    public function pruneExpired(): int
    {
        return MemoryItem::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }

    /**
     * Ringkasan memori per pengguna
     */
    public function summary(int $tenantId, int $userId): array
    {
        $base = MemoryItem::forTenant($tenantId)->forUser($userId)->active();

        return [
            'total' => (clone $base)->count(),
            'verified' => (clone $base)->verified()->count(),
            'by_type' => (clone $base)
                ->select('memory_type', DB::raw('count(*) as count'))
                ->groupBy('memory_type')
                ->pluck('count', 'memory_type')
                ->toArray(),
            'by_source' => (clone $base)
                ->select('source', DB::raw('count(*) as count'))
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray(),
        ];
    }

    protected function validateType(string $type): void
    {
        $valid = [
            MemoryItem::TYPE_PREFERENCE,
            MemoryItem::TYPE_CONTEXT,
            MemoryItem::TYPE_INSIGHT,
            MemoryItem::TYPE_KNOWLEDGE,
            MemoryItem::TYPE_HISTORY,
        ];

        if (!in_array($type, $valid, true)) {
            throw new \InvalidArgumentException("Jenis memori tidak sah: {$type}");
        }
    }
}
