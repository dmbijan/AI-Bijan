<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Segment;
use App\Modules\Marketing\Models\Contact;
use Illuminate\Support\Facades\DB;

class SegmentService
{
    /**
     * Get all segments for a tenant.
     */
    public function getSegments(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Segment::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a segment by ID.
     */
    public function getSegmentById(int $segmentId, int $tenantId): ?Segment
    {
        return Segment::where('id', $segmentId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a segment by slug.
     */
    public function getSegmentBySlug(string $slug, int $tenantId): ?Segment
    {
        return Segment::where('slug', $slug)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new segment.
     */
    public function createSegment(array $data): Segment
    {
        if (empty($data['slug'])) {
            $data['slug'] = Segment::generateSlug($data['name']);
        }

        return DB::transaction(function () use ($data) {
            $segment = Segment::create($data);
            
            // Calculate initial count for dynamic segments
            if ($data['type'] === 'dynamic' && !empty($data['criteria'])) {
                $count = $this->calculateSegmentCount($data['tenant_id'], $data['criteria']);
                $segment->update(['count' => $count]);
            }

            return $segment;
        });
    }

    /**
     * Update a segment.
     */
    public function updateSegment(Segment $segment, array $data): Segment
    {
        return DB::transaction(function () use ($segment, $data) {
            $segment->update($data);
            
            // Recalculate count for dynamic segments if criteria changed
            if ($segment->type === 'dynamic' && isset($data['criteria'])) {
                $count = $this->calculateSegmentCount(
                    tenantId: $segment->tenant_id,
                    criteria: $data['criteria']
                );
                $segment->update(['count' => $count]);
            }

            return $segment->fresh();
        });
    }

    /**
     * Delete a segment.
     */
    public function deleteSegment(Segment $segment): bool
    {
        return $segment->delete();
    }

    /**
     * Toggle segment active status.
     */
    public function toggleActive(Segment $segment): Segment
    {
        return $segment->update(['is_active' => !$segment->is_active]) ? $segment->fresh() : null;
    }

    /**
     * Calculate segment count based on criteria.
     */
    public function calculateSegmentCount(int $tenantId, array $criteria): int
    {
        $query = Contact::where('tenant_id', $tenantId);

        // Apply criteria filters
        if (isset($criteria['source'])) {
            $query->where('source', $criteria['source']);
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['tags']) && is_array($criteria['tags'])) {
            foreach ($criteria['tags'] as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        if (isset($criteria['min_lead_score'])) {
            $query->where('lead_score', '>=', $criteria['min_lead_score']);
        }

        if (isset($criteria['opt_in_email'])) {
            $query->where('opt_in_email', $criteria['opt_in_email']);
        }

        if (isset($criteria['last_activity_days'])) {
            $date = now()->subDays($criteria['last_activity_days']);
            $query->where('last_activity_at', '>=', $date);
        }

        return $query->count();
    }

    /**
     * Add contact to segment.
     */
    public function addContactToSegment(int $contactId, int $segmentId, int $tenantId): bool
    {
        return DB::transaction(function () use ($contactId, $segmentId, $tenantId) {
            $contact = Contact::where('id', $contactId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$contact) {
                return false;
            }

            $contact->update(['segment_id' => $segmentId]);
            return true;
        });
    }

    /**
     * Remove contact from segment.
     */
    public function removeContactFromSegment(int $contactId, int $tenantId): bool
    {
        return Contact::where('id', $contactId)
            ->where('tenant_id', $tenantId)
            ->update(['segment_id' => null]) > 0;
    }

    /**
     * Get unique slug.
     */
    public function getUniqueSlug(string $name, int $tenantId): string
    {
        $baseSlug = Segment::generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Segment::where('slug', $slug)->where('tenant_id', $tenantId)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
