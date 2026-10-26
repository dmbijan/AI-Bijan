<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brainhub_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Insight details
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('general'); // financial, sales, operations, hr, marketing, customer, inventory, logistics
            $table->string('type')->default('recommendation'); // trend, anomaly, opportunity, risk, recommendation
            $table->string('severity')->default('medium'); // low, medium, high, critical
            
            // Data context
            $table->json('data_points')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->string('source_module')->nullable();
            $table->foreignId('metric_id')->nullable()->constrained('analytics_metrics')->nullOnDelete();
            
            // Tracking
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->foreignId('dismissed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dismissed_at')->nullable();
            
            // Action tracking
            $table->string('action_taken')->nullable();
            $table->text('action_notes')->nullable();
            $table->timestamp('actioned_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'category', 'type']);
            $table->index(['tenant_id', 'severity', 'is_read']);
        });

        Schema::create('brainhub_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('metric_id')->constrained('analytics_metrics')->cascadeOnDelete();
            
            // Forecast details
            $table->string('model'); // linear_regression, moving_average, exponential_smoothing, arima
            $table->integer('horizon_days');
            $table->json('forecast_data'); // Array of forecasted values with dates
            $table->json('confidence_intervals')->nullable();
            
            // Accuracy tracking
            $table->decimal('r_squared', 5, 2)->nullable();
            $table->decimal('mape', 5, 2)->nullable();
            $table->decimal('rmse', 10, 2)->nullable();
            $table->string('status')->default('active'); // active, archived, superseded
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'metric_id']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('brainhub_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('insight_id')->nullable()->constrained('brainhub_insights')->nullOnDelete();
            
            // Recommendation details
            $table->string('title');
            $table->text('description');
            $table->string('type')->default('actionable'); // actionable, optimization, risk_mitigation, opportunity, cost_saving
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('category')->default('general');
            
            // Context
            $table->string('related_module')->nullable();
            $table->json('action_steps')->nullable();
            
            // Impact estimates
            $table->decimal('estimated_impact', 15, 2)->nullable();
            $table->decimal('estimated_savings', 15, 2)->nullable();
            $table->decimal('estimated_roi', 5, 2)->nullable();
            
            // User assignment
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Status tracking
            $table->boolean('is_accepted')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            
            // Feedback
            $table->integer('feedback_score')->nullable();
            $table->text('feedback_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_accepted', 'is_completed']);
            $table->index(['tenant_id', 'priority', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brainhub_recommendations');
        Schema::dropIfExists('brainhub_forecasts');
        Schema::dropIfExists('brainhub_insights');
    }
};
