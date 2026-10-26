<?php

namespace App\Modules\Analytics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'analytics_reports';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'type', // financial, sales, operations, hr, marketing, custom
        'format', // pdf, excel, csv, json, html
        'schedule', // once, daily, weekly, monthly, quarterly, yearly
        'schedule_cron',
        'recipients',
        'data_source',
        'filters',
        'columns',
        'group_by',
        'sort_by',
        'is_active',
        'last_run_at',
        'next_run_at',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'group_by' => 'array',
        'recipients' => 'array',
        'schedule' => 'string',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Report types
     */
    const TYPE_FINANCIAL = 'financial';
    const TYPE_SALES = 'sales';
    const TYPE_OPERATIONS = 'operations';
    const TYPE_HR = 'hr';
    const TYPE_MARKETING = 'marketing';
    const TYPE_CUSTOM = 'custom';

    /**
     * Formats
     */
    const FORMAT_PDF = 'pdf';
    const FORMAT_EXCEL = 'excel';
    const FORMAT_CSV = 'csv';
    const FORMAT_JSON = 'json';
    const FORMAT_HTML = 'html';

    /**
     * Schedules
     */
    const SCHEDULE_ONCE = 'once';
    const SCHEDULE_DAILY = 'daily';
    const SCHEDULE_WEEKLY = 'weekly';
    const SCHEDULE_MONTHLY = 'monthly';
    const SCHEDULE_QUARTERLY = 'quarterly';
    const SCHEDULE_YEARLY = 'yearly';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Created by user
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Relationship: Report runs history
     */
    public function runs()
    {
        return $this->hasMany(ReportRun::class);
    }

    /**
     * Scope: Active reports
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Scheduled reports
     */
    public function scopeScheduled($query)
    {
        return $query->where('schedule', '!=', self::SCHEDULE_ONCE);
    }

    /**
     * Check if report is due for run
     */
    public function isDue(): bool
    {
        if ($this->schedule === self::SCHEDULE_ONCE) {
            return false;
        }

        if (!$this->next_run_at) {
            return true;
        }

        return now()->gte($this->next_run_at);
    }

    /**
     * Calculate next run time
     */
    public function calculateNextRun(): void
    {
        if ($this->schedule === self::SCHEDULE_ONCE) {
            $this->next_run_at = null;
            $this->save();
            return;
        }

        switch ($this->schedule) {
            case self::SCHEDULE_DAILY:
                $this->next_run_at = now()->addDay()->startOfDay();
                break;
            case self::SCHEDULE_WEEKLY:
                $this->next_run_at = now()->addWeek()->startOfWeek();
                break;
            case self::SCHEDULE_MONTHLY:
                $this->next_run_at = now()->addMonth()->startOfMonth();
                break;
            case self::SCHEDULE_QUARTERLY:
                $this->next_run_at = now()->addQuarter()->startOfQuarter();
                break;
            case self::SCHEDULE_YEARLY:
                $this->next_run_at = now()->addYear()->startOfYear();
                break;
        }

        $this->save();
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_FINANCIAL => 'Financial',
            self::TYPE_SALES => 'Sales',
            self::TYPE_OPERATIONS => 'Operations',
            self::TYPE_HR => 'Human Resources',
            self::TYPE_MARKETING => 'Marketing',
            self::TYPE_CUSTOM => 'Custom',
            default => $this->type,
        };
    }

    /**
     * Get format label
     */
    public function getFormatLabelAttribute(): string
    {
        return match($this->format) {
            self::FORMAT_PDF => 'PDF Document',
            self::FORMAT_EXCEL => 'Excel Spreadsheet',
            self::FORMAT_CSV => 'CSV File',
            self::FORMAT_JSON => 'JSON Data',
            self::FORMAT_HTML => 'HTML Report',
            default => $this->format,
        };
    }
}
