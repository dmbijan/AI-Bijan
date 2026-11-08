<?php

namespace App\Modules\ScreenRecorder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komen pada rakaman skrin — annotate pada masa tertentu (timeline).
 */
class RecordingComment extends Model
{
    use BelongsToTenant;

    protected $table = 'recording_comments';

    protected $fillable = [
        'tenant_id',
        'recording_id',
        'body',
        'timestamp_seconds',
        'x_position',
        'y_position',
        'created_by',
        'created_by_name',
    ];

    protected $casts = [
        'timestamp_seconds' => 'float',
        'x_position' => 'float',
        'y_position' => 'float',
    ];

    protected $attributes = [
        'timestamp_seconds' => 0,
        'x_position' => 0,
        'y_position' => 0,
    ];

    public function recording(): BelongsTo
    {
        return $this->belongsTo(ScreenRecording::class, 'recording_id');
    }
}
