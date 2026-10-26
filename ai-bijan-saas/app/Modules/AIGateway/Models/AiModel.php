<?php

namespace App\Modules\AIGateway\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    protected $table = 'ai_model_catalog';

    protected $fillable = [
        'provider_slug',
        'provider_model_id',
        'display_name',
        'tier',
        'capabilities',
        'max_context',
        'input_rate',
        'output_rate',
        'is_active',
        'version_snapshot',
        'availability_status',
        'last_verified',
        'metadata',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'max_context' => 'integer',
        'input_rate' => 'decimal:6',
        'output_rate' => 'decimal:6',
        'last_verified' => 'date',
    ];

    const TIER_FLASH = 'flash';
    const TIER_STANDARD = 'standard';
    const TIER_PREMIUM = 'premium';
    const TIER_ULTRA = 'ultra';

    const STATUS_AVAILABLE = 'available';
    const STATUS_BETA = 'beta';
    const STATUS_DEPRECATED = 'deprecated';
    const STATUS_SUNSET = 'sunset';

    // ===== Scopes =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereIn('availability_status', [self::STATUS_AVAILABLE, self::STATUS_BETA]);
    }

    public function scopeByTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider_slug', $provider);
    }

    // ===== Helpers =====

    /**
     * Dapatkan model mengikut provider_model_id
     */
    public static function findByModelId(?string $modelId): ?self
    {
        if (!$modelId) {
            return null;
        }

        return static::where('provider_model_id', $modelId)->first();
    }

    /**
     * Multiplier mengikut tier
     */
    public function tierMultiplier(): float
    {
        return (float) config("ai_gateway.tiers.{$this->tier}.multiplier", 1.0);
    }

    /**
     * Kira kredit untuk penggunaan token
     */
    public function calculateCredits(int $inputTokens, int $outputTokens): int
    {
        $baseRate = (float) config('ai_gateway.base_credit_rate', 0.0001);
        $multiplier = $this->tierMultiplier();

        $inputRate = $this->input_rate ? (float) $this->input_rate : $baseRate * $multiplier;
        $outputRate = $this->output_rate ? (float) $this->output_rate : $baseRate * $multiplier;

        $raw = ($inputTokens * $inputRate) + ($outputTokens * $outputRate);

        // Guna pembundaran minimum 100 kredit, tetapi tier lebih tinggi mesti tetap dicaj lebih.
        $roundTo = (int) config('ai_gateway.credit_rules.round_to', 100);
        $minimum = (int) config('ai_gateway.credit_rules.minimum', 100);
        $credits = (int) (ceil($raw / $roundTo) * $roundTo);

        return max($credits, (int) round($minimum * $multiplier));
    }

    /**
     * Paparan untuk pelanggan — TANPA provider_model_id dalaman
     */
    public function toCustomerArray(): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'tier' => $this->tier,
            'tier_label' => config("ai_gateway.tiers.{$this->tier}.label", $this->tier),
            'capabilities' => $this->capabilities,
            'max_context' => $this->max_context,
            'availability_status' => $this->availability_status,
            'best_for' => config("ai_gateway.tiers.{$this->tier}.best_for"),
        ];
    }
}
