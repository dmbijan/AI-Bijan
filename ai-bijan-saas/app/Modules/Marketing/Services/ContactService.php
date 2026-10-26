<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactService
{
    /**
     * Get all contacts for a tenant.
     */
    public function getContacts(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Contact::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['segment_id'])) {
            $query->where('segment_id', $filters['segment_id']);
        }

        if (isset($filters['tags']) && is_array($filters['tags'])) {
            foreach ($filters['tags'] as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a contact by ID.
     */
    public function getContactById(int $contactId, int $tenantId): ?Contact
    {
        return Contact::where('id', $contactId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a contact by email.
     */
    public function getContactByEmail(string $email, int $tenantId): ?Contact
    {
        return Contact::where('email', $email)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new contact.
     */
    public function createContact(array $data): Contact
    {
        return DB::transaction(function () use ($data) {
            return Contact::create($data);
        });
    }

    /**
     * Update a contact.
     */
    public function updateContact(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {
            $contact->update($data);
            return $contact->fresh();
        });
    }

    /**
     * Delete a contact.
     */
    public function deleteContact(Contact $contact): bool
    {
        return $contact->delete();
    }

    /**
     * Soft delete a contact (GDPR right to be forgotten).
     */
    public function softDeleteContact(Contact $contact): Contact
    {
        return DB::transaction(function () use ($contact) {
            $contact->update([
                'email' => null,
                'phone' => null,
                'first_name' => '[Deleted]',
                'last_name' => '[Deleted]',
                'properties' => [],
                'status' => 'deleted',
            ]);
            return $contact->fresh();
        });
    }

    /**
     * Add tags to a contact.
     */
    public function addTags(Contact $contact, array $tags): Contact
    {
        $existingTags = $contact->tags ?? [];
        $mergedTags = array_unique(array_merge($existingTags, $tags));

        return $contact->update(['tags' => $mergedTags]) ? $contact->fresh() : null;
    }

    /**
     * Remove tags from a contact.
     */
    public function removeTags(Contact $contact, array $tags): Contact
    {
        $existingTags = $contact->tags ?? [];
        $newTags = array_diff($existingTags, $tags);

        return $contact->update(['tags' => array_values($newTags)]) ? $contact->fresh() : null;
    }

    /**
     * Set opt-in preferences.
     */
    public function setOptIn(Contact $contact, array $optInData): Contact
    {
        $fields = ['opt_in_email', 'opt_in_sms', 'opt_in_whatsapp'];
        $updates = [];

        foreach ($fields as $field) {
            if (isset($optInData[$field])) {
                $updates[$field] = $optInData[$field];
                if ($optInData[$field]) {
                    $updates[$field . '_at'] = now();
                    $updates[$field . '_method'] = $optInData[$field . '_method'] ?? 'web';
                }
            }
        }

        return $contact->update($updates) ? $contact->fresh() : null;
    }

    /**
     * Check if contact has opted in for a channel.
     */
    public function hasOptedIn(Contact $contact, string $channel): bool
    {
        $field = "opt_in_{$channel}";
        return (bool) ($contact->$field ?? false);
    }

    /**
     * Update last activity timestamp.
     */
    public function updateLastActivity(Contact $contact): Contact
    {
        return $contact->update(['last_activity_at' => now()]) ? $contact->fresh() : null;
    }

    /**
     * Sync contact from external source.
     */
    public function syncFromExternal(array $externalData, int $tenantId): Contact
    {
        $email = $externalData['email'] ?? null;

        if ($email) {
            $contact = Contact::where('email', $email)->where('tenant_id', $tenantId)->first();

            if ($contact) {
                // Update existing contact
                $contact->update([
                    'first_name' => $externalData['first_name'] ?? $contact->first_name,
                    'last_name' => $externalData['last_name'] ?? $contact->last_name,
                    'phone' => $externalData['phone'] ?? $contact->phone,
                    'source' => $externalData['source'] ?? $contact->source,
                    'properties' => array_merge($contact->properties ?? [], $externalData['properties'] ?? []),
                ]);
                return $contact->fresh();
            }
        }

        // Create new contact
        return $this->createContact(array_merge($externalData, [
            'tenant_id' => $tenantId,
            'source' => $externalData['source'] ?? 'api',
        ]));
    }

    /**
     * Get contact statistics.
     */
    public function getContactStats(int $tenantId): array
    {
        $totalContacts = Contact::where('tenant_id', $tenantId)->count();
        $activeContacts = Contact::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();
        $withEmail = Contact::where('tenant_id', $tenantId)
            ->whereNotNull('email')
            ->count();
        $withPhone = Contact::where('tenant_id', $tenantId)
            ->whereNotNull('phone')
            ->count();

        return [
            'total' => $totalContacts,
            'active' => $activeContacts,
            'with_email' => $withEmail,
            'with_phone' => $withPhone,
        ];
    }
}
