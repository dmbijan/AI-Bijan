<?php

namespace App\Modules\Seo\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoAuditIssue extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_audit_issues';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_ERROR = 'error';

    // Rules
    public const RULE_TITLE_MISSING = 'title_missing';
    public const RULE_TITLE_TOO_LONG = 'title_too_long';
    public const RULE_TITLE_TOO_SHORT = 'title_too_short';
    public const RULE_DESCRIPTION_MISSING = 'description_missing';
    public const RULE_DESCRIPTION_TOO_LONG = 'description_too_long';
    public const RULE_DESCRIPTION_TOO_SHORT = 'description_too_short';
    public const RULE_KEYWORDS_MISSING = 'keywords_missing';
    public const RULE_OG_IMAGE_MISSING = 'og_image_missing';
    public const RULE_OG_TITLE_MISSING = 'og_title_missing';
    public const RULE_CANONICAL_MISSING = 'canonical_missing';
    public const RULE_JSONLD_MISSING = 'jsonld_missing';
    public const RULE_SLUG_TOO_LONG = 'slug_too_long';
    public const RULE_NO_INDEX_SET = 'no_index_set';

    protected $fillable = [
        'tenant_id',
        'seo_metadata_id',
        'object_type',
        'object_id',
        'rule',
        'severity',
        'message',
        'suggestion',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'severity' => self::SEVERITY_WARNING,
        'is_resolved' => false,
    ];

    public function metadata(): BelongsTo
    {
        return $this->belongsTo(SeoMetadata::class, 'seo_metadata_id');
    }
}
