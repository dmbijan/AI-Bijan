<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Setiap model milik tenant WAJIB guna trait ini:
 * - query sentiasa ditapis ikut tenant semasa (tiada tenant = tiada rekod),
 * - rekod baru diisi tenant_id semasa, dan tenant_id tidak boleh ditukar.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $q) {
            $ctx = app(TenantContext::class);
            $q->where($q->getModel()->getTable().'.tenant_id', $ctx->has() ? $ctx->id() : 0);
        });

        static::creating(function ($m) {
            $ctx = app(TenantContext::class);
            if (! $ctx->has()) {
                throw new RuntimeException('Rekod tenant tidak boleh dicipta tanpa tenant aktif.');
            }
            if ($m->tenant_id && (int) $m->tenant_id !== $ctx->id()) {
                throw new RuntimeException('tenant_id tidak sepadan dengan tenant aktif.');
            }
            $m->tenant_id = $ctx->id();
        });

        static::updating(function ($m) {
            if ($m->isDirty('tenant_id')) {
                throw new RuntimeException('tenant_id tidak boleh diubah.');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
