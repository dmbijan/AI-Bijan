<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create fixed_assets table.
     * 
     * Tracks depreciable assets per Malaysian tax law:
     * - Initial allowance (Initial Allowance on Qualifying Plant)
     * - Annual depreciation schedules
     * - Disposal calculations
     */
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // Asset identification
            $table->string('asset_number')->unique()->comment('FA-YYYY-NNNNN');
            $table->string('name');
            $table->text('description')->nullable();
            
            // Classification
            $table->enum('category', [
                'land', 'building', 'machinery', 'equipment', 'vehicles',
                'furniture_fittings', 'computer_equipment', 'software',
                'leasehold_improvements', 'other'
            ])->default('other');
            
            // Acquisition details
            $table->date('purchase_date')->index();
            $table->decimal('cost', 18, 2)->default(0)->comment('Acquisition cost including installation');
            $table->decimal('installation_cost', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0)->comment('cost + installation_cost');
            
            // Depreciation settings
            $table->enum('depreciation_method', ['straight_line', 'reducing_balance', 'units_of_production'])->default('straight_line');
            $table->decimal('annual_depreciation_rate', 5, 2)->default(0)->comment('Annual rate as percentage');
            $table->integer('useful_life_years')->default(5)->comment('Useful life in years');
            
            // Accumulated values
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->decimal('net_book_value', 18, 2)->default(0)->comment('total_cost - accumulated_depreciation');
            
            // Residual/salvage value
            $table->decimal('residual_value', 18, 2)->default(0)->comment('Estimated salvage value at end of useful life');
            
            // Status
            $table->enum('status', ['active', 'fully_depreciated', 'disposed', 'maintenance', 'sold'])->default('active');
            
            // Location/Department
            $table->string('location')->nullable();
            $table->string('department')->nullable();
            $table->string('custodian')->nullable()->comment('Person responsible for this asset');
            
            // Tax treatment (Malaysian IRB guidelines)
            $table->boolean('is_qualifying_asset')->default(false)->comment('Qualifies for initial allowance under Income Tax Act 1967');
            $table->decimal('initial_allowance_amount', 18, 2)->default(0)->comment('First year tax allowance');
            $table->decimal('tax_accumulated_depreciation', 18, 2)->default(0);
            
            // Disposal
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_proceeds', 18, 2)->default(0)->comment('Sale price if disposed');
            $table->decimal('disposal_gain_loss', 18, 2)->default(0)->comment('Proceeds - net book value');
            
            // Journal entry links
            $table->foreignId('acquisition_journal_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('depreciation_journal_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category']);
        });
        
        // Depreciation schedule
        Schema::create('depreciation_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('fixed_assets')->cascadeOnDelete();
            
            $table->integer('year')->comment('Depreciation year (1 = first year)');
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            
            // Calculation
            $table->decimal('opening_nbv', 18, 2)->comment('Net book value at start of period');
            $table->decimal('depreciation_amount', 18, 2)->comment('Depreciation for this period');
            $table->decimal('closing_nbv', 18, 2)->comment('Net book value at end of period');
            
            // Cumulative
            $table->decimal('cumulative_depreciation', 18, 2)->comment('Total depreciation to date');
            
            // Status
            $table->boolean('is_posted')->default(false)->comment('Has this been posted to journal?');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            $table->unique(['asset_id', 'year'], 'asset_year_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depreciation_schedule');
        Schema::dropIfExists('fixed_assets');
    }
};
