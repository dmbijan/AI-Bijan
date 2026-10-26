<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kontrak perkhidmatan — dijana daripada sebut harga yang diterima.
 *
 * Boleh memaut ke `SignDocs` melalui `sign_document_id` untuk e-tandatangan.
 */
class ServiceContract extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SENT,
        self::STATUS_ACCEPTED,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $table = 'service_contracts';

    protected $fillable = [
        'tenant_id',
        'service_project_id',
        'service_quotation_id',
        'client_id',
        'sign_document_id',
        'contract_no',
        'status',
        'currency',
        'contract_value',
        'deposit_amount',
        'body',
        'clauses',
        'effective_date',
        'expiry_date',
        'accepted_at',
        'accepted_by_name',
        'accepted_by_email',
        'accepted_ip',
        'cancelled_at',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'clauses' => 'array',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'accepted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'service_project_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(ServiceQuotation::class, 'service_quotation_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ServiceClient::class, 'client_id');
    }

    /** Adakah kontrak ini mengikat secara sah? */
    public function mengikat(): bool
    {
        return in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_ACTIVE], true)
            && $this->accepted_at !== null;
    }
}
