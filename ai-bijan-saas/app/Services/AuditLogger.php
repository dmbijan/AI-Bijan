<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    private const RAHSIA = ['password', 'two_factor_secret', 'remember_token', 'hash', 'invite_token_hash', 'path'];

    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    /** Rekod satu tindakan. Untuk tindakan platform (tiada tenant), tenant_id null ditulis terus. */
    public function log(string $action, ?Model $subject = null, ?array $before = null, ?array $after = null, ?int $tenantId = null): void
    {
        $req = request();
        $row = [
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'before' => $before ? $this->bersih($before) : null,
            'after' => $after ? $this->bersih($after) : null,
            'ip_hash' => $req?->ip() ? substr(hash('sha256', $req->ip().'|'.config('app.key')), 0, 24) : null,
            'user_agent' => $req ? substr((string) $req->userAgent(), 0, 255) : null,
        ];
        $tid = $tenantId ?? $this->ctx()->id();
        if ($tid !== null && $tid === $this->ctx()->id()) {
            AuditLog::create($row);
        } else {
            DB::table('audit_logs')->insert(array_merge($row, ['tenant_id' => $tid, 'before' => $row['before'] === null ? null : json_encode($row['before']), 'after' => $row['after'] === null ? null : json_encode($row['after']), 'created_at' => now()]));
        }
    }

    private function bersih(array $d): array
    {
        return collect($d)->except(self::RAHSIA)->all();
    }
}
