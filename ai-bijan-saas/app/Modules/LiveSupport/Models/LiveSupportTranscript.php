<?php

namespace App\Modules\LiveSupport\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSupportTranscript extends Model
{
    use BelongsToTenant;

    protected $table = 'live_support_transcripts';

    protected $fillable = [
        'tenant_id',
        'live_support_call_id',
        'speaker',
        'text',
        'confidence',
        'offset_ms',
    ];

    protected $casts = [
        'confidence' => 'float',
        'offset_ms' => 'integer',
    ];

    protected $attributes = [
        'speaker' => 'caller',
        'offset_ms' => 0,
    ];

    public function call(): BelongsTo
    {
        return $this->belongsTo(LiveSupportCall::class, 'live_support_call_id');
    }
}
