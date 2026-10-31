<?php

namespace App\Modules\DataImport\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ImportRow extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'import_job_id', 'row_no', 'data', 'errors', 'action', 'target_id'];

    protected $casts = ['data' => 'array', 'errors' => 'array'];
}
