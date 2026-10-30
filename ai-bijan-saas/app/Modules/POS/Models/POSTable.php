<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class POSTable extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_tables';

    protected $fillable = ['tenant_id', 'name', 'token', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected $hidden = ['tenant_id'];

    protected static function booted(): void
    {
        static::creating(fn (self $t) => $t->token ??= self::tokenBaharu());
    }

    public static function tokenBaharu(): string
    {
        return Str::lower(Str::random(24));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(POSTableOrder::class, 'table_id');
    }
}
