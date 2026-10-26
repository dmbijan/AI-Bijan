<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContact extends Model
{
    protected $table = 'customer_contacts';
    
    protected $fillable = [
        'customer_id',
        'name',
        'position',
        'email',
        'phone',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get customer that owns this contact.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope primary contacts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
