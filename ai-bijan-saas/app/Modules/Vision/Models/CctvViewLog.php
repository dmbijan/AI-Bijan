<?php

namespace App\Modules\Vision\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Audit setiap tontonan rakaman/peristiwa (BAB 56C.6). */
class CctvViewLog extends Model
{
    use BelongsToTenant;

    protected $table = 'cctv_view_logs';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'camera_id',
        'event_id',
        'user_id',
        'action',
        'ip_address',
        'purpose',
    ];

    protected $casts = ['created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->created_at ??= now();
        });
    }
}
