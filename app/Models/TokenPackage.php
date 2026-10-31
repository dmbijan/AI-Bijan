<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pakej token AI yang dijual bersama langganan. Diurus oleh Super Admin. */
class TokenPackage extends Model
{
    protected $fillable = ['code', 'name', 'tokens', 'usage_limit', 'price', 'supplier_cost', 'validity_days', 'is_active', 'sort_order'];

    protected $casts = [
        'tokens' => 'integer',
        'usage_limit' => 'integer',
        'price' => 'decimal:2',
        'supplier_cost' => 'decimal:2',
        'validity_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
