<?php

namespace App\Modules\BrainHub\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Forecast extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'brainhub_forecasts';

    protected $fillable = [
        'tenant_id',
        'metric_id',
        'source_module',
        'title',
        'description',
        'model_used', // linear_regression, moving_average, exponential_smoothing, arima
        'forecast_horizon_days',
        'predicted_value',
        'confidence_lower',
        'confidence_upper',
        'confidence_level',
        'accuracy_score',
        'actual_value',
        'actual_confirmed_at',
        'metadata',
    ];

    protected $casts = [
        'predicted_value' => 'decimal:2',
        'confidence_lower' => 'decimal:2',
        'confidence_upper' => 'decimal:2',
        'confidence_level' => 'decimal:2',
        'accuracy_score' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'actual_confirmed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Models used
     */
    const MODEL_LINEAR_REGRESSION = 'linear_regression';
    const MODEL_MOVING_AVERAGE = 'moving_average';
    const MODEL_EXPONENTIAL_SMOOTHING = 'exponential_smoothing';
    const MODEL_ARIMA = 'arima';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Metric
     */
    public function metric()
    {
        return $this->belongsTo(\App\Modules\Analytics\Models\Metric::class);
    }

    /**
     * Scope: By model
     */
    public function scopeByModel($query, $model)
    {
        return $query->where('model_used', $model);
    }

    /**
     * Scope: Unconfirmed forecasts
     */
    public function scopeUnconfirmed($query)
    {
        return $query->whereNull('actual_value');
    }

    /**
     * Confirm actual value
     */
    public function confirmActual(float $actualValue): void
    {
        $this->update([
            'actual_value' => $actualValue,
            'actual_confirmed_at' => now(),
        ]);

        // Calculate accuracy
        if ($this->predicted_value != 0) {
            $accuracy = 1 - abs($this->predicted_value - $actualValue) / abs($this->predicted_value);
            $this->update(['accuracy_score' => max(0, $accuracy)]);
        }
    }

    /**
     * Get model label
     */
    public function getModelLabelAttribute(): string
    {
        return match($this->model_used) {
            self::MODEL_LINEAR_REGRESSION => 'Linear Regression',
            self::MODEL_MOVING_AVERAGE => 'Moving Average',
            self::MODEL_EXPONENTIAL_SMOOTHING => 'Exponential Smoothing',
            self::MODEL_ARIMA => 'ARIMA',
            default => $this->model_used,
        };
    }
}
