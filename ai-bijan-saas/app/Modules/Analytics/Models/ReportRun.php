<?php

namespace App\Modules\Analytics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportRun extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'analytics_report_runs';

    protected $fillable = [
        'tenant_id',
        'report_id',
        'status', // pending, running, completed, failed, cancelled
        'started_at',
        'completed_at',
        'duration_seconds',
        'output_file',
        'recipients_sent',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'recipients_sent' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Report
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Scope: By status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Recent runs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => $this->status,
        };
    }
}
