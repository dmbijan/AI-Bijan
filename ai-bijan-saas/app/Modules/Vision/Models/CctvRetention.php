<?php

namespace App\Modules\Vision\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Polisi retensi rakaman per kamera (BAB 56C). */
class CctvRetention extends Model
{
    use BelongsToTenant;

    protected $table = 'cctv_retention';

    protected $fillable = ['tenant_id', 'camera_id', 'days', 'storage_used_mb'];

    protected $casts = [
        'days' => 'integer',
        'storage_used_mb' => 'integer',
    ];

    public const HAD_HARI = ['min' => 1, 'max' => 90];

    public function camera()
    {
        return $this->belongsTo(CctvCamera::class, 'camera_id');
    }
}
