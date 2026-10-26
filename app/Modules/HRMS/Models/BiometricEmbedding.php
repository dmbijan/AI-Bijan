<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Embedding wajah — hanya wujud selagi consent aktif (BAB 56C.6A). */
class BiometricEmbedding extends Model
{
    use BelongsToTenant;

    protected $table = 'biometric_embeddings';

    protected $hidden = ['embedding'];

    protected $fillable = [
        'tenant_id',
        'user_id',
        'consent_id',
        'embedding',
        'quality_score',
        'expires_at',
    ];

    protected $casts = [
        'quality_score' => 'decimal:4',
        'expires_at' => 'datetime',
    ];

    public function consent()
    {
        return $this->belongsTo(BiometricConsent::class, 'consent_id');
    }
}
