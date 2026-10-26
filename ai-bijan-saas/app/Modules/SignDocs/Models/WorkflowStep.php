<?php

namespace App\Modules\SignDocs\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'sign_docs_workflow_steps';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'step_number',
        'step_type',
        'assigned_to',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStepType($query, $type)
    {
        return $query->where('step_type', $type);
    }

    // Methods
    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete($notes = '')
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function reject($reason = '')
    {
        $this->update([
            'status' => 'rejected',
            'completed_at' => now(),
            'notes' => $reason,
        ]);
    }
}
