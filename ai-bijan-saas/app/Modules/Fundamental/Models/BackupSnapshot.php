<?php

namespace App\Modules\Fundamental\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Inventori snapshot backup.
 *
 * ⚠️ Platform-level — TIDAK tenant-scoped (backup keseluruhan platform).
 * Justeru itu model ini TIDAK guna trait `BelongsToTenant`.
 */
class BackupSnapshot extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const TYPE_FULL = 'full';
    public const TYPE_INCREMENTAL = 'incremental';

    protected $table = 'backup_snapshots';

    protected $fillable = [
        'snapshot_id',
        'disk',
        'filename',
        'type',
        'size_bytes',
        'checksum',
        'is_encrypted',
        'status',
        'error_message',
        'started_at',
        'finished_at',
        'duration_seconds',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'is_encrypted' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
