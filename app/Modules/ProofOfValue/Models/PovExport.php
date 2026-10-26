<?php

namespace App\Modules\ProofOfValue\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Bukti jualan — PDF "Proof of Value" untuk pelanggan.
 */
class PovExport extends Model
{
    use BelongsToTenant;

    protected $table = 'pov_exports';

    protected $fillable = [
        'tenant_id',
        'name',
        'period_start',
        'period_end',
        'metrics',
        'status',
        'file_path',
        'generated_by',
        'sent_to',
        'sent_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'sent_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    public function sudahDihantar(): bool
    {
        return $this->sent_at !== null;
    }
}
