<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Akses sokongan Super Admin ke tenant: bersebab, terhad masa, boleh dibatalkan, dilog. */
class SupportAccessGrant extends Model
{
    protected $fillable = ['tenant_id', 'admin_user_id', 'reason', 'expires_at', 'revoked_at'];

    protected $casts = ['expires_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function isValid(): bool
    {
        return ! $this->revoked_at && $this->expires_at->isFuture();
    }
}
