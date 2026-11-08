<?php

namespace App\Modules\VideoStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Projek video — satu kerja penghasilan video oleh skill AI BIJAN.
 *
 * AI BIJAN membungkus tiga skill video (asalnya VOYNANN):
 *   - `mograph`       — motion-graphics explainer 2D/2.5D (VO + skrip)
 *   - `motion`        — filem sinematik 3D 9:16 (idea atau skrip + MP3)
 *   - `video-editor`  — sunting rakaman sedia ada jadi MP4 60fps
 */
class VideoProject extends Model
{
    use BelongsToTenant;

    protected $table = 'video_projects';

    public const SKILL_MOGRAPH = 'mograph';

    public const SKILL_MOTION = 'motion';

    public const SKILL_EDITOR = 'video-editor';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PREFLIGHT = 'preflight';

    public const STATUS_RENDERING = 'rendering';

    public const STATUS_QC = 'qc';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'name',
        'slug',
        'skill',
        'status',
        'topic',
        'title',
        'gaya',
        'aspect_ratio',
        'frame_rate',
        'duration_seconds',
        'brand_kit_id',
        'voiceover_path',
        'script_path',
        'source_video_path',
        'output_path',
        'output_url',
        'thumb_path',
        'version',
        'progress',
        'stage',
        'qc_report',
        'settings',
        'error_message',
        'preflight_ok',
        'preflight_report',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'qc_report' => 'array',
        'settings' => 'array',
        'preflight_report' => 'array',
        'preflight_ok' => 'boolean',
        'frame_rate' => 'integer',
        'duration_seconds' => 'float',
        'version' => 'integer',
        'progress' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'skill' => self::SKILL_MOGRAPH,
        'status' => self::STATUS_DRAFT,
        'aspect_ratio' => '9:16',
        'frame_rate' => 60,
        'duration_seconds' => 0,
        'version' => 1,
        'progress' => 0,
        'preflight_ok' => false,
    ];

    public function brandKit(): BelongsTo
    {
        return $this->belongsTo(VideoBrandKit::class, 'brand_kit_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function sedangProses(): bool
    {
        return in_array($this->status, [
            self::STATUS_QUEUED,
            self::STATUS_PREFLIGHT,
            self::STATUS_RENDERING,
            self::STATUS_QC,
        ], true);
    }

    /**
     * @return array<string, string>
     */
    public static function senaraiSkill(): array
    {
        return [
            self::SKILL_MOGRAPH => 'Mograph — explainer motion-graphics 2D/2.5D',
            self::SKILL_MOTION => 'Motion — filem sinematik 3D 9:16',
            self::SKILL_EDITOR => 'Video Editor — sunting rakaman sedia ada',
        ];
    }

    /**
     * Gaya yang disokong mengikut skill.
     *
     * @return array<string, array<int, string>>
     */
    public static function gayaMengikutSkill(): array
    {
        return [
            self::SKILL_MOGRAPH => ['MIDNIGHT', 'MINIATUR', 'ARSIP', 'BLUEPRINT', 'SKETSA', 'HIKAYAT', 'GEOMETRIK', 'TERMINAL'],
            self::SKILL_MOTION => ['CINÉSTORY', 'CHARACTERFLOW', 'PANELVERSE', 'INNERWORKS', 'PAPERWORLD', 'VOXELVERSE', 'TIMEFORGE', 'BUILDFORGE', 'WORLDFORGE', 'PIXELSTORY', 'CINESTORY'],
            self::SKILL_EDITOR => ['IMPACT', 'SYSTEM', 'EDITORIAL', 'CREATOR POP', 'MOGRAPH MIX', 'STORYTELLER', 'DEPTH', 'LUMEN', 'POSTER', 'NOTED'],
        ];
    }
}
