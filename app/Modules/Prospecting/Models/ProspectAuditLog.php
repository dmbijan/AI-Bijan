<?php

namespace App\Modules\Prospecting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Jejak audit prospect — who did what to which prospect.
 */
class ProspectAuditLog extends Model
{
    use BelongsToTenant;

    protected $table = 'prospect_audit_logs';

    protected $fillable = [
        'tenant_id',
        'prospect_id',
        'actor_type',
        'actor_id',
        'action',
        'payload',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
}
