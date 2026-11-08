<?php

namespace App\Modules\OpenWhispr\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kamus perkataan tersuai — pengecamalan istilah perniagaan untuk STT.
 *
 * Ciri utama OpenWhispr ialah kosakata yang dikendalikan pengguna; dalam konteks PKS
 * Malaysia ini penting untuk nama produk, jenama dan istilah industri.
 */
class DictationVocabulary extends Model
{
    use BelongsToTenant;

    protected $table = 'dictation_vocabularies';

    protected $fillable = [
        'tenant_id',
        'word',
        'aliases',
        'category',
        'weight',
        'is_active',
        'hit_count',
    ];

    protected $casts = [
        'aliases' => 'array',
        'weight' => 'float',
        'is_active' => 'boolean',
        'hit_count' => 'integer',
    ];

    protected $attributes = [
        'weight' => 1.0,
        'is_active' => true,
        'hit_count' => 0,
    ];
}
