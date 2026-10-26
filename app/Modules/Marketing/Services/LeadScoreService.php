<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\LeadScore;
use App\Modules\Marketing\Models\Contact;
use Illuminate\Support\Facades\DB;

class LeadScoreService
{
    /**
     * Get all lead scores for a tenant.
     */
    public function getLeadScores(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = LeadScore::where('tenant_id', $tenantId);

        if (isset($filters['grade'])) {
            $query->byGrade($filters['grade']);
        }

        if (isset($filters['tier'])) {
            $query->byTier($filters['tier']);
        }

        if (isset($filters['hot_leads_only'])) {
            $query->hotLeads();
        }

        return $query->orderBy('total_score', 'desc')->get();
    }

    /**
     * Get a lead score by ID.
     */
    public function getLeadScoreById(int $scoreId, int $tenantId): ?LeadScore
    {
        return LeadScore::where('id', $scoreId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get lead score for a contact.
     */
    public function getLeadScoreForContact(int $contactId, int $tenantId): ?LeadScore
    {
        return LeadScore::where('contact_id', $contactId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create or update lead score for a contact.
     */
    public function upsertLeadScore(int $contactId, int $tenantId, array $scores): LeadScore
    {
        return DB::transaction(function () use ($contactId, $tenantId, $scores) {
            $leadScore = LeadScore::firstOrCreate(
                [
                    'contact_id' => $contactId,
                    'tenant_id' => $tenantId,
                ],
                [
                    'demographic_score' => $scores['demographic'] ?? 0,
                    'firmographic_score' => $scores['firmographic'] ?? 0,
                    'engagement_score' => $scores['engagement'] ?? 0,
                    'behavioral_score' => $scores['behavioral'] ?? 0,
                    'transactional_score' => $scores['transactional'] ?? 0,
                ]
            );

            // Update existing record if already exists
            if (!$leadScore->wasRecentlyCreated) {
                $leadScore->update([
                    'demographic_score' => $scores['demographic'] ?? $leadScore->demographic_score,
                    'firmographic_score' => $scores['firmographic'] ?? $leadScore->firmographic_score,
                    'engagement_score' => $scores['engagement'] ?? $leadScore->engagement_score,
                    'behavioral_score' => $scores['behavioral'] ?? $leadScore->behavioral_score,
                    'transactional_score' => $scores['transactional'] ?? $leadScore->transactional_score,
                ]);
            }

            // Calculate total and update grade/tier
            $leadScore->calculateTotal();

            return $leadScore->fresh();
        });
    }

    /**
     * Calculate demographic score.
     */
    public function calculateDemographicScore(Contact $contact): int
    {
        $score = 0;

        // Age scoring
        if ($contact->age_range) {
            switch ($contact->age_range) {
                case '25-34':
                    $score += 25; // Prime purchasing age
                    break;
                case '35-44':
                    $score += 20;
                    break;
                case '18-24':
                    $score += 15;
                    break;
                default:
                    $score += 10;
            }
        }

        // Location scoring
        if ($contact->location) {
            $score += 10;
        }

        // Gender scoring (if provided)
        if ($contact->gender) {
            $score += 5;
        }

        return min($score, 100);
    }

    /**
     * Calculate firmographic score.
     */
    public function calculateFirmographicScore(Contact $contact): int
    {
        $score = 0;

        // Company size
        if ($contact->company_size) {
            switch ($contact->company_size) {
                case 'large':
                    $score += 30;
                    break;
                case 'medium':
                    $score += 20;
                    break;
                case 'small':
                    $score += 10;
                    break;
            }
        }

        // Industry
        if ($contact->industry) {
            $score += 15;
        }

        // Job title
        if ($contact->job_title) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Calculate engagement score.
     */
    public function calculateEngagementScore(Contact $contact): int
    {
        $score = 0;

        // Email opens
        if ($contact->email_opened_at) {
            $score += 15;
        }

        // Email clicks
        if ($contact->email_clicked_at) {
            $score += 10;
        }

        // Website visits
        if ($contact->last_activity_at) {
            $daysSinceActivity = now()->diffInDays($contact->last_activity_at);
            if ($daysSinceActivity <= 1) {
                $score += 20; // Active today
            } elseif ($daysSinceActivity <= 7) {
                $score += 15; // Active this week
            } elseif ($daysSinceActivity <= 30) {
                $score += 10; // Active this month
            } else {
                $score += 5; // Inactive
            }
        }

        // Social media engagement
        if ($contact->social_engagement) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Calculate behavioral score.
     */
    public function calculateBehavioralScore(Contact $contact): int
    {
        $score = 0;

        // Form submissions
        if ($contact->form_submissions > 0) {
            $score += min($contact->form_submissions * 10, 30);
        }

        // Content downloads
        if ($contact->downloads > 0) {
            $score += min($contact->downloads * 5, 20);
        }

        // Event attendance
        if ($contact->events_attended > 0) {
            $score += min($contact->events_attended * 5, 20);
        }

        // Demo requests
        if ($contact->demo_requested) {
            $score += 20;
        }

        // Trial started
        if ($contact->trial_started) {
            $score += 15;
        }

        return min($score, 100);
    }

    /**
     * Calculate transactional score.
     */
    public function calculateTransactionalScore(Contact $contact): int
    {
        $score = 0;

        // Purchase history
        if ($contact->total_purchases > 0) {
            $score += min($contact->total_purchases * 5, 25);
        }

        // Total spend
        if ($contact->total_spent) {
            if ($contact->total_spent >= 10000) {
                $score += 25;
            } elseif ($contact->total_spent >= 5000) {
                $score += 20;
            } elseif ($contact->total_spent >= 1000) {
                $score += 15;
            } elseif ($contact->total_spent >= 100) {
                $score += 10;
            } else {
                $score += 5;
            }
        }

        // Recency of purchase
        if ($contact->last_purchase_date) {
            $daysSincePurchase = now()->diffInDays($contact->last_purchase_date);
            if ($daysSincePurchase <= 7) {
                $score += 20; // Purchased this week
            } elseif ($daysSincePurchase <= 30) {
                $score += 15; // Purchased this month
            } elseif ($daysSincePurchase <= 90) {
                $score += 10; // Purchased last quarter
            } else {
                $score += 5; // Inactive buyer
            }
        }

        // Frequency
        if ($contact->purchase_frequency) {
            switch ($contact->purchase_frequency) {
                case 'high':
                    $score += 20;
                    break;
                case 'medium':
                    $score += 10;
                    break;
                case 'low':
                    $score += 5;
                    break;
            }
        }

        return min($score, 100);
    }

    /**
     * Recalculate all scores for a contact.
     */
    public function recalculateContactScore(Contact $contact): LeadScore
    {
        $demographic = $this->calculateDemographicScore($contact);
        $firmographic = $this->calculateFirmographicScore($contact);
        $engagement = $this->calculateEngagementScore($contact);
        $behavioral = $this->calculateBehavioralScore($contact);
        $transactional = $this->calculateTransactionalScore($contact);

        return $this->upsertLeadScore(
            $contact->id,
            $contact->tenant_id,
            [
                'demographic' => $demographic,
                'firmographic' => $firmographic,
                'engagement' => $engagement,
                'behavioral' => $behavioral,
                'transactional' => $transactional,
            ]
        );
    }

    /**
     * Bulk recalculate all contact scores for a tenant.
     */
    public function bulkRecalculateScores(int $tenantId): int
    {
        $contacts = Contact::where('tenant_id', $tenantId)->get();
        $count = 0;

        foreach ($contacts as $contact) {
            try {
                $this->recalculateContactScore($contact);
                $count++;
            } catch (\Exception $e) {
                // Log error but continue with next contact
                \Log::error("Failed to recalculate score for contact {$contact->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    /**
     * Get lead score distribution.
     */
    public function getScoreDistribution(int $tenantId): array
    {
        $scores = LeadScore::where('tenant_id', $tenantId)->get();

        $distribution = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'F' => 0,
        ];

        foreach ($scores as $score) {
            if (isset($distribution[$score->grade])) {
                $distribution[$score->grade]++;
            }
        }

        return $distribution;
    }

    /**
     * Get tier distribution.
     */
    public function getTierDistribution(int $tenantId): array
    {
        $scores = LeadScore::where('tenant_id', $tenantId)->get();

        $distribution = [
            'hot' => 0,
            'warm' => 0,
            'lukewarm' => 0,
            'cold' => 0,
            'frozen' => 0,
        ];

        foreach ($scores as $score) {
            if (isset($distribution[$score->tier])) {
                $distribution[$score->tier]++;
            }
        }

        return $distribution;
    }

    /**
     * Get top leads.
     */
    public function getTopLeads(int $tenantId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return LeadScore::where('tenant_id', $tenantId)
            ->with('contact')
            ->orderBy('total_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get lead score statistics.
     */
    public function getLeadScoreStats(int $tenantId): array
    {
        $totalScores = LeadScore::where('tenant_id', $tenantId)->count();
        $avgScore = LeadScore::where('tenant_id', $tenantId)->avg('total_score') ?? 0;
        $hotLeads = LeadScore::where('tenant_id', $tenantId)
            ->whereIn('grade', ['A', 'B'])
            ->count();

        return [
            'total_scores' => $totalScores,
            'average_score' => round($avgScore, 2),
            'hot_leads' => $hotLeads,
            'hot_lead_percentage' => ($totalScores > 0) ? ($hotLeads / $totalScores) * 100 : 0,
        ];
    }
}
