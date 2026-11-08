<?php

namespace App\Modules\SharedInbox\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Makro — rangkaian tindakan automatik untuk ejen.
 *
 * Contoh: "Tutup tiket + hantar balasan + tanda selesai" dalam satu klik.
 */
class InboxMacro extends Model
{
    use BelongsToTenant;

    protected $table = 'inbox_macros';

    protected $fillable = [
        'tenant_id',
        'shared_inbox_id',
        'name',
        'description',
        'actions',
        'usage_count',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'actions' => 'array',
        'usage_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'usage_count' => 0,
        'is_active' => true,
    ];

    public function sharedInbox(): BelongsTo
    {
        return $this->belongsTo(SharedInbox::class, 'shared_inbox_id');
    }

    /**
     * Jenis tindakan yang disokong.
     *
     * @return array<string, string>
     */
    public static function jenisTindakan(): array
    {
        return [
            'send_reply' => 'Hantar Balasan',
            'set_status' => 'Tukar Status',
            'set_priority' => 'Tukar Keutamaan',
            'assign_agent' => 'Tugaskan Ejen',
            'add_tag' => 'Tambah Tag',
            'remove_tag' => 'Buang Tag',
            'close_conversation' => 'Tutup Perbincangan',
            'resolve_sla' => 'Tandakan SLA Selesai',
        ];
    }
}
