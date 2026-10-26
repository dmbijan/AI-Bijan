<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ContentModerationRule extends Model
{
    use BelongsToTenant;

    protected $table = 'content_moderation_rules';

    public const TYPE_KEYWORD = 'keyword';
    public const TYPE_TOXICITY = 'toxicity';
    public const TYPE_SPAM = 'spam';
    public const TYPE_REGEX = 'regex';
    public const TYPE_BLOCKLIST = 'blocklist';

    public const ACTION_HIDE = 'hide';
    public const ACTION_FLAG = 'flag';
    public const ACTION_BLOCK = 'block';
    public const ACTION_AUTO_REPLY = 'auto_reply';
    public const ACTION_ESCALATE = 'escalate';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'pattern',
        'action',
        'severity',
        'is_active',
        'trigger_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trigger_count' => 'integer',
    ];

    protected $attributes = [
        'type' => self::TYPE_KEYWORD,
        'action' => self::ACTION_HIDE,
        'severity' => 'medium',
        'is_active' => true,
        'trigger_count' => 0,
    ];

    /**
     * Periksa teks terhadap corak rules.
     */
    public function sepadan(string $teks): bool
    {
        $pattern = trim((string) $this->pattern);

        if ($pattern === '') {
            return false;
        }

        return match ($this->type) {
            self::TYPE_REGEX => (bool) @preg_match($pattern, $teks),
            self::TYPE_KEYWORD, self::TYPE_BLOCKLIST => stripos($teks, $pattern) !== false,
            default => false,
        };
    }
}
