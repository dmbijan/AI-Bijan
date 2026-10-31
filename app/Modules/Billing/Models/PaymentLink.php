<?php

namespace App\Modules\Billing\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PaymentLink extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'provider', 'payable_type', 'payable_id', 'provider_ref', 'url', 'amount', 'status', 'paid_at'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
}
