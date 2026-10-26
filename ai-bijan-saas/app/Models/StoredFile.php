<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class StoredFile extends Model
{
    use BelongsToTenant, HasUlids;

    protected $table = 'files';

    protected $fillable = ['disk', 'path', 'original_name', 'mime', 'size', 'sha256', 'uploaded_by', 'attachable_type', 'attachable_id'];

    protected $hidden = ['path', 'disk', 'id', 'tenant_id'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
