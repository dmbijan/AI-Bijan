<?php

namespace App\Modules\Site\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\DomainService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected DomainService $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    /**
     * List all domains for a tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $domains = $this->domainService->getDomains($this->tenantId());
        
        return response()->json([
            'success' => true,
            'data' => $domains,
        ]);
    }

    /**
     * List all domains for a site.
     */
    public function indexForSite(Request $request, int $siteId): JsonResponse
    {
        $domains = $this->domainService->getSiteDomains($siteId);
        
        return response()->json([
            'success' => true,
            'data' => $domains,
        ]);
    }

    /**
     * Get a single domain.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $domain,
        ]);
    }

    /**
     * Create a new domain.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|integer',
            'domain' => 'required|string|max:255',
            'type' => 'nullable|string|in:primary,secondary,www,non-www',
            'redirect_to' => 'nullable|string|max:255',
        ]);

        // Validate domain format
        if (!$this->domainService->validateDomain($validated['domain'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid domain format.',
            ], 422);
        }

        $validated['tenant_id'] = $this->tenantId();

        $domain = $this->domainService->createDomain($validated);

        return response()->json([
            'success' => true,
            'message' => 'Domain added successfully.',
            'data' => $domain,
        ], 201);
    }

    /**
     * Update a domain.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        $validated = $request->validate([
            'domain' => 'sometimes|string|max:255',
            'type' => 'nullable|string|in:primary,secondary,www,non-www',
            'is_primary' => 'nullable|boolean',
            'redirect_to' => 'nullable|string|max:255',
        ]);

        $domain = $this->domainService->updateDomain($domain, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Domain updated successfully.',
            'data' => $domain,
        ]);
    }

    /**
     * Delete a domain.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        $this->domainService->deleteDomain($domain);

        return response()->json([
            'success' => true,
            'message' => 'Domain deleted successfully.',
        ]);
    }

    /**
     * Set domain as primary.
     */
    public function setPrimary(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|integer',
        ]);

        $domain = $this->domainService->setPrimaryDomain(
            $this->domainService->getDomainById($id, $this->tenantId()),
            $validated['site_id'],
            $this->tenantId()
        );

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Primary domain updated.',
            'data' => $domain,
        ]);
    }

    /**
     * Enable SSL for domain.
     */
    public function enableSsl(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        $domain = $this->domainService->enableSsl($domain);

        return response()->json([
            'success' => true,
            'message' => 'SSL enabled. Verification in progress.',
            'data' => $domain,
        ]);
    }

    /**
     * Verify SSL for domain.
     */
    public function verifySsl(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        $domain = $this->domainService->verifySsl($domain);

        return response()->json([
            'success' => true,
            'message' => 'SSL verified successfully.',
            'data' => $domain,
        ]);
    }

    /**
     * Verify domain ownership.
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        $domain = $this->domainService->verifyDomain($domain);

        return response()->json([
            'success' => true,
            'message' => 'Domain verified successfully.',
            'data' => $domain,
        ]);
    }

    /**
     * Generate verification code.
     */
    public function generateVerificationCode(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        $code = $this->domainService->generateVerificationCode();
        $domain->update(['verification_code' => $code]);

        return response()->json([
            'success' => true,
            'data' => [
                'verification_code' => $code,
                'domain' => $domain->domain,
            ],
        ]);
    }

    /**
     * Check DNS records.
     */
    public function checkDns(Request $request, int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id, $this->tenantId());

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found.',
            ], 404);
        }

        $dnsInfo = $this->domainService->checkDnsRecords($domain->domain);

        return response()->json([
            'success' => true,
            'data' => $dnsInfo,
        ]);
    }
}
