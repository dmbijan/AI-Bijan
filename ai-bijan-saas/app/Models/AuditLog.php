<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/** Log audit: tambah sahaja. Kemas kini & padam disekat di peringkat model. */
class AuditLog extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'before', 'after', 'ip_hash', 'user_agent'];

    protected $casts = ['before' => 'array', 'after' => 'array'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Log audit tidak boleh diubah.'));
        static::deleting(fn () => throw new LogicException('Log audit tidak boleh dipadam.'));
    }
}
