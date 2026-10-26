<?php

namespace App\Modules\ADE\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Sandbox — ruang kerja terasing untuk satu subtugas.
 *
 * Jenis:
 *   worktree   — git worktree sebenar (asing fail, berkongsi objek git)
 *   container  — bekas Docker (asing penuh)
 *   directory  — direktori salinan ringkas
 *   none       — tiada pengasingan (lalai selamat untuk tenant kecil)
 */
class AdeSandbox extends Model
{
    use BelongsToTenant;

    public const STATUS_PROVISIONING = 'provisioning';
    public const STATUS_READY = 'ready';
    public const STATUS_IN_USE = 'in_use';
    public const STATUS_DIRTY = 'dirty';
    public const STATUS_CLEANING = 'cleaning';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REMOVED = 'removed';

    protected $table = 'ade_sandboxes';

    protected $fillable = [
        'tenant_id', 'name', 'slug', 'type', 'status', 'base_branch',
        'branch_name', 'path', 'repo_path', 'baseline', 'meta',
        'open_pr', 'duration_ms', 'error_message',
    ];

    protected $casts = [
        'baseline' => 'array',
        'meta' => 'array',
        'open_pr' => 'integer',
        'duration_ms' => 'integer',
    ];

    protected $attributes = [
        'type' => 'worktree',
        'status' => self::STATUS_PROVISIONING,
        'base_branch' => 'main',
    ];

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_READY, self::STATUS_IN_USE, self::STATUS_DIRTY], true);
    }

    /**
     * Baseline ujian bersih — tiada baseline bermaksud belum dijalankan.
     */
    public function adaBaseline(): bool
    {
        return is_array($this->baseline) && $this->baseline !== [];
    }

    /**
     * Baseline acceptable = semua ujian lulus.
     */
    public function baselineBersih(): bool
    {
        $b = $this->baseline;

        if (! is_array($b) || ! isset($b['passed'], $b['failed'])) {
            return false;
        }

        return (int) $b['failed'] === 0 && (int) $b['passed'] > 0;
    }
}
