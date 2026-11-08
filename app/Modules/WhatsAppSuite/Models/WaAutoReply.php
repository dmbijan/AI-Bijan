<?php

namespace App\Modules\WhatsAppSuite\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Auto-responder / chatbot — balas automatik berdasarkan kata kunci.
 *
 * Berasal daripada WasapBot MY (Auto Responder, Set Keyword, ChatGPT).
 */
class WaAutoReply extends Model
{
    use BelongsToTenant;

    protected $table = 'wa_auto_replies';

    public const TYPE_EXACT = 'exact';

    public const TYPE_CONTAINS = 'contains';

    public const TYPE_REGEX = 'regex';

    public const TYPE_AI = 'ai';

    public const TYPE_WELCOME = 'welcome';

    public const TYPE_FALLBACK = 'fallback';

    protected $fillable = [
        'tenant_id',
        'wa_device_id',
        'name',
        'type',
        'trigger',
        'reply',
        'use_ai',
        'ai_instructions',
        'send_media_url',
        'priority',
        'is_active',
        'hit_count',
        'last_hit_at',
    ];

    protected $casts = [
        'use_ai' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'hit_count' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => self::TYPE_CONTAINS,
        'use_ai' => false,
        'priority' => 0,
        'is_active' => true,
        'hit_count' => 0,
    ];

    public static function senaraiJenis(): array
    {
        return [
            self::TYPE_EXACT => 'Tepat',
            self::TYPE_CONTAINS => 'Mengandungi',
            self::TYPE_REGEX => 'Regex',
            self::TYPE_AI => 'AI Chatbot',
            self::TYPE_WELCOME => 'Mesej Alu-aluan',
            self::TYPE_FALLBACK => 'Lalai (tiada padanan)',
        ];
    }
}
