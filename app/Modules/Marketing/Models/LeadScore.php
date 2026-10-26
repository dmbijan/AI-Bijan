<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadScore extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_lead_scores';

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'total_score',
        'demographic_score',
        'firmographic_score',
        'engagement_score',
        'behavioral_score',
        'transactional_score',
        'grade',
        'tier',
        'calculated_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'total_score' => 'integer',
        'demographic_score' => 'integer',
        'firmographic_score' => 'integer',
        'engagement_score' => 'integer',
        'behavioral_score' => 'integer',
        'transactional_score' => 'integer',
        'calculated_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the lead score.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the contact this score belongs to.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Calculate total score from components.
     */
    public function calculateTotal(): int
    {
        $total = ($this->demographic_score ?? 0) +
                 ($this->firmographic_score ?? 0) +
                 ($this->engagement_score ?? 0) +
                 ($this->behavioral_score ?? 0) +
                 ($this->transactional_score ?? 0);

        $this->update([
            'total_score' => $total,
            'calculated_at' => now(),
        ]);

        // Update grade based on score
        $this->updateGrade($total);

        return $total;
    }

    /**
     * Update grade based on total score.
     */
    protected function updateGrade(int $score): void
    {
        if ($score >= 80) {
            $grade = 'A';
            $tier = 'hot';
        } elseif ($score >= 60) {
            $grade = 'B';
            $tier = 'warm';
        } elseif ($score >= 40) {
            $grade = 'C';
            $tier = 'lukewarm';
        } elseif ($score >= 20) {
            $grade = 'D';
            $tier = 'cold';
        } else {
            $grade = 'F';
            $tier = 'frozen';
        }

        $this->update(['grade' => $grade, 'tier' => $tier]);
    }

    /**
     * Scope by grade.
     */
    public function scopeByGrade($query, string $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * Scope by tier.
     */
    public function scopeByTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Scope hot leads.
     */
    public function scopeHotLeads($query)
    {
        return $query->whereIn('grade', ['A', 'B']);
    }
}
