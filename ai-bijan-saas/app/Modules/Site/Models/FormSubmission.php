<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use BelongsToTenant;

    protected $table = 'site_form_submissions';

    protected $fillable = [
        'tenant_id',
        'form_id',
        'data',
        'ip_address',
        'user_agent',
        'is_spam',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_spam' => 'boolean',
    ];

    /**
     * Get the tenant that owns the submission.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the form this submission belongs to.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }
}
