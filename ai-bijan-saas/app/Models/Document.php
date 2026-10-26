<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/** Dokumen dikeluarkan: salinan data dikunci (tidak diubah; pembetulan = batal + keluarkan baru). */
class Document extends Model
{
    use BelongsToTenant, HasUlids;

    protected $fillable = ['type', 'number', 'title', 'locale', 'theme', 'template_version', 'data', 'sha256', 'subject_user_id', 'issued_by', 'status', 'void_reason'];

    protected $casts = ['data' => 'array'];

    protected $hidden = ['id', 'tenant_id'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    protected static function booted(): void
    {
        static::updating(function (Document $d) {
            if ($d->isDirty(['data', 'sha256', 'number', 'type', 'template_version'])) {
                throw new \LogicException('Kandungan dokumen yang dikeluarkan tidak boleh diubah. Batalkan dan keluarkan dokumen baru.');
            }
        });
    }
}
