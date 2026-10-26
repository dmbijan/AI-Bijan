<?php

namespace App\Modules\SCM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierContact extends Model
{
    use HasFactory;

    protected $table = 'supplier_contacts';

    protected $fillable = [
        'supplier_id',
        'name',
        'position',
        'email',
        'phone',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
