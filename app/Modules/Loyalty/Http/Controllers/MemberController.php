<?php

namespace App\Modules\Loyalty\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Modules\Loyalty\Services\MemberService;
use App\Modules\Loyalty\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * List all members.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $members = $this->memberService->getMembers(
            $tenantId,
            $request->only(['status', 'tier_id', 'search', 'high_value']),
            $request->input('per_page', 20)
        );

        return response()->json($members);
    }

    /**
     * Get a single member.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $member = $this->memberService->getMemberById($id, $tenantId);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        return response()->json($member);
    }

    /**
     * Get member by code.
     */
    public function getByCode(string $code, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $member = $this->memberService->getMemberByCode($code, $tenantId);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        return response()->json($member);
    }

    /**
     * Create a new member.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'crm_contact_id' => 'nullable|exists:crm_contacts,id',
            'tier_id' => 'nullable|exists:loyalty_tiers,id',
            'notes' => 'nullable|string',
        ]);

        $member = $this->memberService->createMember(array_merge($validated, [
            'tenant_id' => app(TenantContext::class)->id(),
        ]));

        return response()->json($member, 201);
    }

    /**
     * Update member details.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $member = Member::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $member = $this->memberService->updateMember($member, $validated);

        return response()->json($member);
    }

    /**
     * Enroll CRM contact as loyalty member.
     */
    public function enrollFromContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:crm_contacts,id',
        ]);

        try {
            $member = $this->memberService->enrollFromContact(
                $validated['contact_id'],
                app(TenantContext::class)->id()
            );

            return response()->json($member, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Deactivate a member.
     */
    public function deactivate(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $member = Member::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $this->memberService->deactivateMember($member);

        return response()->json(['message' => 'Member deactivated']);
    }

    /**
     * Reactivate a member.
     */
    public function reactivate(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $member = Member::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $this->memberService->reactivateMember($member);

        return response()->json(['message' => 'Member activated']);
    }

    /**
     * Update member tier based on points balance.
     */
    public function updateTier(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $member = Member::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $newTier = $this->memberService->updateMemberTier($member);

        if ($newTier) {
            return response()->json([
                'message' => "Member upgraded to {$newTier->name}",
                'member' => $member->fresh(),
                'new_tier' => $newTier,
            ]);
        }

        return response()->json(['message' => 'No tier change needed', 'member' => $member]);
    }

    /**
     * Get member statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->memberService->getMemberStats($tenantId);

        return response()->json($stats);
    }
}
