<?php

namespace App\Modules\Link\Services;

use App\Modules\Link\Models\DigitalCard;
use Illuminate\Pagination\LengthAwarePaginator;

class DigitalCardService
{
    /**
     * Get all digital cards for a tenant.
     */
    public function getCards(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = DigitalCard::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['template'])) {
            $query->where('template', $filters['template']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('holder_name', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get card by short code.
     */
    public function getCardByCode(string $code, int $tenantId): ?DigitalCard
    {
        return DigitalCard::where('short_code', strtoupper($code))
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Create a new digital card.
     */
    public function createCard(array $data): DigitalCard
    {
        $data['short_code'] = LinkService::generateShortCode();
        $data['views_count'] = 0;
        $data['shares_count'] = 0;
        $data['saves_count'] = 0;

        return DigitalCard::create($data);
    }

    /**
     * Track card view.
     */
    public function trackView(int $cardId): DigitalCard
    {
        $card = DigitalCard::findOrFail($cardId);
        $card->increment('views_count');
        return $card;
    }

    /**
     * Track card share.
     */
    public function trackShare(int $cardId): DigitalCard
    {
        $card = DigitalCard::findOrFail($cardId);
        $card->increment('shares_count');
        return $card;
    }

    /**
     * Track card save.
     */
    public function trackSave(int $cardId): DigitalCard
    {
        $card = DigitalCard::findOrFail($cardId);
        $card->increment('saves_count');
        return $card;
    }

    /**
     * Generate vCard content.
     */
    public function generateVCard(DigitalCard $card): string
    {
        $vcard = "BEGIN:VCARD\n";
        $vcard .= "VERSION:3.0\n";
        $vcard .= "FN:{$card->holder_name}\n";
        
        if ($card->company) {
            $vcard .= "ORG:{$card->company}\n";
        }
        
        if ($card->job_title) {
            $vcard .= "TITLE:{$card->job_title}\n";
        }
        
        if ($card->email) {
            $vcard .= "EMAIL:{$card->email}\n";
        }
        
        if ($card->phone) {
            $vcard .= "TEL;TYPE=CELL:{$card->phone}\n";
        }
        
        if ($card->website) {
            $vcard .= "URL:{$card->website}\n";
        }
        
        if ($card->address) {
            $vcard .= "ADR;TYPE=WORK:{$card->address}\n";
        }
        
        $vcard .= "END:VCARD";
        
        return $vcard;
    }
}
