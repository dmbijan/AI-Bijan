<?php

namespace App\Modules\Blast\Services;

use App\Modules\Blast\Models\BlastSuppression;
use App\Modules\Blast\Models\BlastUnsubscribe;
use App\Modules\Prospecting\Services\DedupeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OptOutService — pentadbiran langganan & senarai penindihan.
 *
 * MenJEJAMIN: satu e-mel/nombor yang telah unsubscribe TIDAK PERNAH dihantar
 * semula. Ini syarat wajib PDPA.
 */
class OptOutService
{
    public function __construct(private DedupeService $dedupe) {}

    /**
     * Adakah alamat ini disekat?
     */
    public function disekat(int $tenantId, ?string $email, ?string $phone, string $channel = 'email'): bool
    {
        $hash = $this->hash($email, $phone);

        if (! $hash) {
            return false;
        }

        $suppressed = BlastSuppression::query()
            ->where('tenant_id', $tenantId)
            ->where('hash', $hash)
            ->where('channel', $channel)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($suppressed) {
            return true;
        }

        // Semak juga unsubscribe table (channel wildcard)
        return BlastUnsubscribe::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('email_hash', $email ? [$this->dedupe->hash($email, null)] : [])
            ->orWhere(function ($q) use ($tenantId, $phone) {
                $q->where('tenant_id', $tenantId)
                    ->where('phone_hash', $phone ? $this->dedupe->hash(null, $phone) : '');
            })
            ->exists();
    }

    /**
     * Rekod unsubscribe + tambah ke suppression list.
     */
    public function unsubscribe(
        int $tenantId,
        ?string $email,
        ?string $phone,
        string $channel = 'email',
        string $reason = 'link',
        ?int $prospectId = null,
        ?int $contactId = null
    ): BlastUnsubscribe {
        $emailHash = $email ? $this->dedupe->hash($email, null) : null;
        $phoneHash = $phone ? $this->dedupe->hash(null, $phone) : null;

        $record = BlastUnsubscribe::create([
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
            'prospect_id' => $prospectId,
            'channel' => $channel,
            'email' => $email,
            'phone' => $phone,
            'email_hash' => $emailHash,
            'phone_hash' => $phoneHash,
            'token' => Str::random(40),
            'reason' => $reason,
            'source' => $reason,
            'ip_address' => request()?->ip(),
        ]);

        // Tambah ke suppression list
        $hash = $this->hash($email, $phone);
        if ($hash) {
            BlastSuppression::updateOrCreate(
                ['tenant_id' => $tenantId, 'hash' => $hash, 'channel' => $channel],
                [
                    'email' => $email,
                    'phone' => $phone,
                    'reason' => 'unsubscribe',
                    'source' => $reason,
                    'expires_at' => null,
                ]
            );
        }

        // Kemas kini prospect consent
        if ($prospectId) {
            \App\Modules\Prospecting\Models\Prospect::query()
                ->where('tenant_id', $tenantId)
                ->where('id', $prospectId)
                ->update([
                    'consent_status' => \App\Modules\Prospecting\Models\Prospect::CONSENT_DENIED,
                    'consent_at' => null,
                    'consent_evidence' => null,
                ]);
        }

        Log::info('Blast: unsubscribe direkod', [
            'tenant_id' => $tenantId,
            'channel' => $channel,
            'source' => $reason,
        ]);

        return $record;
    }

    /**
     * Tambah ke suppression list sahaja (bounce, complaint).
     */
    public function suppress(
        int $tenantId,
        ?string $email,
        ?string $phone,
        string $channel,
        string $reason,
        ?\DateTimeInterface $expiresAt = null
    ): ?BlastSuppression {
        $hash = $this->hash($email, $phone);

        if (! $hash) {
            return null;
        }

        return BlastSuppression::updateOrCreate(
            ['tenant_id' => $tenantId, 'hash' => $hash, 'channel' => $channel],
            [
                'email' => $email,
                'phone' => $phone,
                'reason' => $reason,
                'source' => 'system',
                'expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Senarai penindihan.
     */
    public function senarai(int $tenantId, int $limit = 100): array
    {
        return BlastSuppression::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Buang suppression yang sudah luput.
     */
    public function bersihkanLuput(int $tenantId): int
    {
        return BlastSuppression::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }

    private function hash(?string $email, ?string $phone): ?string
    {
        return $this->dedupe->hash($email, $phone);
    }
}
