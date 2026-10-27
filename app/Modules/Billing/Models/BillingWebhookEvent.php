<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jejak webhook CHIP — untuk idempotency, audit dan siasatan.
 *
 * ⚠️ Bukan tenant-scoped: webhook tiba tanpa TenantContext. Jangan tambah
 * `BelongsToTenant` — proses masuk tidak tahu tenant sehingga cas dicari.
 */
class BillingWebhookEvent extends Model
{
    protected $table = 'billing_webhook_events';

    protected $fillable = [
        'provider',
        'event_type',
        'chip_purchase_id',
        'chip_subscription_id',
        'status',
        'signature',
        'payload',
        'processing_error',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
