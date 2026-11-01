<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Unit organisasi: kumpulan, syarikat, cawangan, jabatan atau pasukan dalam satu tenant. */
class OrgUnit extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id', 'tenant_id'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
