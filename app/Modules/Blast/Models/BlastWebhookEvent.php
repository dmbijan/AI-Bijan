<?php

namespace App\Modules\Blast\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Rakam webhook provider — untuk idempotensi & audit.
 */
class BlastWebhookEvent extends Model
{
    use BelongsToTenant;

    protected $table = 'blast_webhook_events';

    protected $fillable = [
        'tenant_id',
        'provider',
        'event_type',
        'event_id',
        'payload',
        'signature_valid',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function sudahDiproses(): bool
    {
        return $this->processed_at !== null;
    }
}
