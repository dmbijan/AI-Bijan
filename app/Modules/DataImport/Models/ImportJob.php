<?php

namespace App\Modules\DataImport\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'type', 'status', 'filename', 'mapping', 'options', 'total_rows', 'valid_rows', 'error_rows', 'result', 'created_by', 'committed_by', 'committed_at'];

    protected $casts = ['mapping' => 'array', 'options' => 'array', 'result' => 'array', 'committed_at' => 'datetime'];

    public function rows()
    {
        return $this->hasMany(ImportRow::class);
    }
}
