<?php

namespace App\Modules\Site\Services;

use App\Modules\Site\Models\Domain;
use Illuminate\Support\Facades\DB;

class DomainService
{
    /**
     * Get all domains for a tenant.
     */
    public function getDomains(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Domain::where('tenant_id', $tenantId)->get();
    }

    /**
     * Get all domains for a site.
     */
    public function getSiteDomains(int $siteId): \Illuminate\Database\Eloquent\Collection
    {
        return Domain::where('site_id', $siteId)->get();
    }

    /**
     * Get a domain by ID.
     */
    public function getDomainById(int $domainId, int $tenantId): ?Domain
    {
        return Domain::where('id', $domainId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new domain.
     */
    public function createDomain(array $data): Domain
    {
        return DB::transaction(function () use ($data) {
            return Domain::create($data);
        });
    }

    /**
     * Update a domain.
     */
    public function updateDomain(Domain $domain, array $data): Domain
    {
        return DB::transaction(function () use ($domain, $data) {
            $domain->update($data);
            return $domain->fresh();
        });
    }

    /**
     * Delete a domain.
     */
    public function deleteDomain(Domain $domain): bool
    {
        return $domain->delete();
    }

    /**
     * Set domain as primary.
     */
    public function setPrimaryDomain(Domain $domain, int $siteId, int $tenantId): ?Domain
    {
        // Deactivate all other primary domains for this site
        Domain::where('site_id', $siteId)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        $domain->update(['is_primary' => true]);
        return $domain->fresh();
    }

    /**
     * Enable SSL for domain.
     */
    public function enableSsl(Domain $domain): Domain
    {
        return $domain->update([
            'ssl_enabled' => true,
            'ssl_status' => 'pending',
        ]) ? $domain->fresh() : null;
    }

    /**
     * Verify SSL for domain.
     */
    public function verifySsl(Domain $domain): Domain
    {
        return $domain->update([
            'ssl_status' => 'active',
        ]) ? $domain->fresh() : null;
    }

    /**
     * Disable SSL for domain.
     */
    public function disableSsl(Domain $domain): Domain
    {
        return $domain->update([
            'ssl_enabled' => false,
            'ssl_status' => 'disabled',
        ]) ? $domain->fresh() : null;
    }

    /**
     * Verify domain ownership.
     */
    public function verifyDomain(Domain $domain): Domain
    {
        return $domain->update([
            'verified' => true,
        ]) ? $domain->fresh() : null;
    }

    /**
     * Generate verification code.
     */
    public function generateVerificationCode(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Check DNS records for domain.
     */
    public function checkDnsRecords(string $domain): array
    {
        // This is a placeholder - implement actual DNS checking logic
        return [
            'status' => 'pending',
            'records' => [],
        ];
    }

    /**
     * Add DNS record to domain.
     */
    public function addDnsRecord(Domain $domain, array $record): Domain
    {
        $records = $domain->dns_records ?? [];
        $records[] = $record;
        $domain->update(['dns_records' => $records]);
        return $domain->fresh();
    }

    /**
     * Validate domain format.
     */
    public function validateDomain(string $domain): bool
    {
        return Domain::validateDomain($domain);
    }
}
