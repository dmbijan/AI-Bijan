<?php

namespace App\Modules\OpenGenAI\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Provider generatif — satu model bekalan (OpenAI, Anthropic, Ollama, Replicate, ...).
 *
 * Berbeza daripada `ai_models` (katalog model dalam AIGateway): rekod ini
 *jana hos dan kunci API milik tenant sendiri, jadi PKS boleh sambungkan
 * model tempatan atau model sendiri tanpa melalui reseller.
 */
class GenAiProvider extends Model
{
    use BelongsToTenant;

    protected $table = 'gen_ai_providers';

    public const KIND_OPENAI = 'openai';

    public const KIND_ANTHROPIC = 'anthropic';

    public const KIND_GEMINI = 'gemini';

    public const KIND_GROQ = 'groq';

    public const KIND_OLLAMA = 'ollama';

    public const KIND_REPLICATE = 'replicate';

    public const KIND_CUSTOM = 'custom';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'kind',
        'base_url',
        'api_key',
        'default_model',
        'is_active',
        'is_default',
        'capabilities',
        'config',
        'last_error',
        'last_checked_at',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected $attributes = [
        'kind' => self::KIND_CUSTOM,
        'is_active' => true,
        'is_default' => false,
    ];

    /**
     * Simpan kunci API disulitkan (Laravel encryption).
     */
    public function setApiKeyAttribute(?string $nilai): void
    {
        if ($nilai === null || $nilai === '') {
            $this->attributes['api_key'] = null;

            return;
        }

        $this->attributes['api_key'] = Crypt::encryptString($nilai);
    }

    /**
     * Kunci API dalam bentuk plainskip — untuk kegunaan pelayan sahaja.
     */
    public function kunciApi(): string
    {
        $kunci = $this->attributes['api_key'] ?? null;

        if (! $kunci) {
            return '';
        }

        try {
            return Crypt::decryptString($kunci);
        } catch (\Throwable) {
            // Nilai lama (tidak disulitkan) — pulangkan seperti asal.
            return (string) $kunci;
        }
    }

    /**
     * Kunci API tidak pernah dipaparkan penuh — hanya 4 aksara terakhir.
     */
    public function apiKeyMasked(): string
    {
        $key = $this->kunciApi();

        if ($key === '') {
            return '';
        }

        return str_repeat('•', max(0, min(8, strlen($key) - 4))).substr($key, -4);
    }

    public function menyokong(string $kemahiran): bool
    {
        return in_array($kemahiran, $this->capabilities ?? [], true);
    }
}
