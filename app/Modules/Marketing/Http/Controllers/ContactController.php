<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\ContactService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
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
    protected ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * List all contacts.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $filters = $request->only(['status', 'source', 'segment_id', 'tags']);
        
        if ($request->has('search')) {
            $filters['search'] = $request->search;
        }

        $contacts = $this->contactService->getContacts($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $contacts,
        ]);
    }

    /**
     * Get a single contact.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }

    /**
     * Create a new contact.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:marketing_contacts,email,NULL,contact_id,tenant_id,' . app(TenantContext::class)->id(),
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|in:web,api,import,crm,site,marketplace,booking',
            'tags' => 'nullable|array',
            'opt_in_email' => 'nullable|boolean',
            'opt_in_sms' => 'nullable|boolean',
            'opt_in_whatsapp' => 'nullable|boolean',
            'properties' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $contact = $this->contactService->createContact($data);

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Contact created successfully.',
        ], 201);
    }

    /**
     * Update a contact.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => "nullable|email|max:255|unique:marketing_contacts,email,$id,contact_id,tenant_id,$tenantId",
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|in:web,api,import,crm,site,marketplace,booking',
            'tags' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,deleted',
            'properties' => 'nullable|array',
        ]);

        $contact = $this->contactService->updateContact($contact, $request->all());

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Contact updated successfully.',
        ]);
    }

    /**
     * Delete a contact (hard delete).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $this->contactService->deleteContact($contact);

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully.',
        ]);
    }

    /**
     * Soft delete (GDPR right to be forgotten).
     */
    public function softDelete(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $contact = $this->contactService->softDeleteContact($contact);

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Contact anonymized (GDPR right to be forgotten).',
        ]);
    }

    /**
     * Add tags to contact.
     */
    public function addTags(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'string|max:100',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $contact = $this->contactService->addTags($contact, $request->tags);

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Tags added successfully.',
        ]);
    }

    /**
     * Remove tags from contact.
     */
    public function removeTags(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'string|max:100',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $contact = $this->contactService->removeTags($contact, $request->tags);

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Tags removed successfully.',
        ]);
    }

    /**
     * Set opt-in preferences.
     */
    public function setOptIn(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'opt_in_email' => 'nullable|boolean',
            'opt_in_sms' => 'nullable|boolean',
            'opt_in_whatsapp' => 'nullable|boolean',
            'opt_in_email_method' => 'nullable|in:web,api,import',
            'opt_in_sms_method' => 'nullable|in:web,api,import',
            'opt_in_whatsapp_method' => 'nullable|in:web,api,import',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $contact = $this->contactService->setOptIn($contact, $request->all());

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Opt-in preferences updated.',
        ]);
    }

    /**
     * Check opt-in status for a channel.
     */
    public function checkOptIn(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'channel' => 'required|in:email,sms,whatsapp',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $hasOptedIn = $this->contactService->hasOptedIn($contact, $request->channel);

        return response()->json([
            'success' => true,
            'data' => [
                'contact_id' => $id,
                'channel' => $request->channel,
                'opted_in' => $hasOptedIn,
            ],
        ]);
    }

    /**
     * Sync contact from external source.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|email|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|in:web,api,import,crm,site,marketplace,booking',
            'properties' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $contact = $this->contactService->syncFromExternal($data, $tenantId);

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Contact synced successfully.',
        ]);
    }

    /**
     * Update last activity timestamp.
     */
    public function updateActivity(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $contact = $this->contactService->getContactById($id, $tenantId);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $contact = $this->contactService->updateLastActivity($contact);

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Activity timestamp updated.',
        ]);
    }

    /**
     * Get contact statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->contactService->getContactStats($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
