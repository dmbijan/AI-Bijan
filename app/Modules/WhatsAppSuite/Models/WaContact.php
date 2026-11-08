<?php

namespace App\Modules\WhatsAppSuite\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Hubungan WhatsApp — kontak, label dan status.
 *
 * Berasal daripada WasapBot MY (Contacts, My Leads, Label Management).
 */
class WaContact extends Model
{
    use BelongsToTenant;

    protected $table = 'wa_contacts';

    public const STATUS_VALID = 'valid';

    public const STATUS_INVALID = 'invalid';

    public const STATUS_UNKNOWN = 'unknown';

    protected $fillable = [
        'tenant_id',
        'wa_device_id',
        'phone_number',
        'name',
        'email',
        'labels',
        'status',
        'is_duplicate',
        'source',
        'last_messaged_at',
        'message_count',
        'notes',
    ];

    protected $casts = [
        'labels' => 'array',
        'is_duplicate' => 'boolean',
        'last_messaged_at' => 'datetime',
        'message_count' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_UNKNOWN,
        'is_duplicate' => false,
        'message_count' => 0,
    ];

    public function sah(): bool
    {
        return $this->status === self::STATUS_VALID && ! $this->is_duplicate;
    }
}
