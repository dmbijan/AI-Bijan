<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Audit setiap cubaan padanan wajah (BAB 56C.6A). */
class BiometricAccessLog extends Model
{
    use BelongsToTenant;

    protected $table = 'biometric_access_logs';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'camera_ref',
        'matched',
        'confidence',
        'purpose',
        'outcome',
    ];

    protected $casts = [
        'matched' => 'boolean',
        'confidence' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->created_at ??= now();
        });
    }
}
