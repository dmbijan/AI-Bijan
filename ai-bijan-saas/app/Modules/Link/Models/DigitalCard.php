<?php

namespace App\Modules\Link\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalCard extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'digital_cards';

    protected $fillable = [
        'tenant_id',
        'title',
        'short_code',
        'holder_name',
        'company',
        'job_title',
        'email',
        'phone',
        'website',
        'address',
        'linkedin',
        'twitter',
        'facebook',
        'instagram',
        'thumbnail',
        'template',
        'custom_css',
        'vcard_content',
        'qr_code_data',
        'status',
        'expiry_date',
        'views_count',
        'shares_count',
        'saves_count',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    /**
     * Get the tenant that owns the digital card.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Scope for active cards.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if card is valid.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date < now()) {
            return false;
        }

        return true;
    }

    /**
     * Generate vCard content.
     */
    public function generateVCard(): string
    {
        $vcard = "BEGIN:VCARD\n";
        $vcard .= "VERSION:3.0\n";
        $vcard .= "FN:{$this->holder_name}\n";
        
        if ($this->company) {
            $vcard .= "ORG:{$this->company}\n";
        }
        
        if ($this->job_title) {
            $vcard .= "TITLE:{$this->job_title}\n";
        }
        
        if ($this->email) {
            $vcard .= "EMAIL:{$this->email}\n";
        }
        
        if ($this->phone) {
            $vcard .= "TEL;TYPE=CELL:{$this->phone}\n";
        }
        
        if ($this->website) {
            $vcard .= "URL:{$this->website}\n";
        }
        
        if ($this->linkedin) {
            $vcard .= "URL;TYPE=LINKEDIN:{$this->linkedin}\n";
        }
        
        if ($this->twitter) {
            $vcard .= "URL;TYPE=TWITTER:{$this->twitter}\n";
        }
        
        if ($this->address) {
            $vcard .= "ADR;TYPE=WORK:{$this->address}\n";
        }
        
        $vcard .= "END:VCARD";
        
        return $vcard;
    }

    /**
     * Generate QR code data.
     */
    public function generateQrData(): string
    {
        $baseUrl = config('app.url', url('/'));
        return "{$baseUrl}/card/{$this->short_code}";
    }
}
