<?php

namespace App\Modules\ADE\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Review — permukaan semakan kod: diff walkthrough + test runner + preview.
 *
 * Status mencerminkan aliran superpowers `requesting-code-review`:
 * open → in_review → (approved | changes_requested | rejected) → merged
 */
class AdeReview extends Model
{
    use BelongsToTenant;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_MERGED = 'merged';

    public const SEVERITY = ['low', 'medium', 'high', 'critical'];

    protected $table = 'ade_reviews';

    protected $fillable = [
        'tenant_id', 'ade_coordination_id', 'ade_sandbox_id', 'title', 'status',
        'severity', 'diff', 'files', 'test_results', 'summary', 'findings',
        'comments', 'reviewed_by', 'additions', 'deletions',
    ];

    protected $casts = [
        'diff' => 'array',
        'files' => 'array',
        'test_results' => 'array',
        'findings' => 'array',
        'comments' => 'array',
        'reviewed_by' => 'integer',
        'additions' => 'integer',
        'deletions' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_OPEN,
        'severity' => 'medium',
        'additions' => 0,
        'deletions' => 0,
    ];

    public function coordination(): BelongsTo
    {
        return $this->belongsTo(AdeCoordination::class, 'ade_coordination_id');
    }

    public function sandbox(): BelongsTo
    {
        return $this->belongsTo(AdeSandbox::class, 'ade_sandbox_id');
    }

    /**
     * Ujian lulus = tiada kegagalan dalam test_results.
     */
    public function ujianLulus(): bool
    {
        $t = $this->test_results;

        if (! is_array($t) || ! isset($t['failed'])) {
            return false;
        }

        return (int) $t['failed'] === 0;
    }

    /**
     * Review kritikal menyekat kelulusan automatik.
     */
    public function adaKritikal(): bool
    {
        $f = $this->findings;

        if (! is_array($f)) {
            return false;
        }

        foreach ($f as $penemuan) {
            $sev = is_array($penemuan) ? ($penemuan['severity'] ?? null) : ($penemuan->severity ?? null);

            if ($sev === 'critical') {
                return true;
            }
        }

        return false;
    }

    /**
     * Boleh diluluskan secara automatik — tiada kritikal & ujian lulus.
     */
    public function bolehLulusAuto(): bool
    {
        return ! $this->adaKritikal() && $this->ujianLulus();
    }
}
