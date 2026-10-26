<?php

namespace App\Modules\Vision\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Peristiwa yang dikesan oleh Vision Service (BAB 56C Fasa 1). */
class CctvEvent extends Model
{
    use BelongsToTenant;

    protected $table = 'cctv_events';

    protected $fillable = [
        'tenant_id',
        'camera_id',
        'event_type',
        'confidence',
        'started_at',
        'ended_at',
        'clip_path',
        'thumbnail_path',
        'acknowledged_by',
        'acknowledged_at',
        'note',
    ];

    protected $casts = [
        'confidence' => 'decimal:4',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    /** Jenis peristiwa yang diterima dalam Fasa 1 (tiada face recognition). */
    public const JENIS = ['person', 'vehicle', 'loitering', 'perimeter_breach', 'after_hours', 'motion'];

    /** Jenis yang memerlukan perhatian segera. */
    public const KRITIKAL = ['perimeter_breach', 'after_hours'];

    public function camera()
    {
        return $this->belongsTo(CctvCamera::class, 'camera_id');
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'acknowledged_by');
    }

    public function sudahDiakui(): bool
    {
        return $this->acknowledged_at !== null;
    }
}
