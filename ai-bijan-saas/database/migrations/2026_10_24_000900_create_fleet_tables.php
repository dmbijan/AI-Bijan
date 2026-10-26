<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fleet & GPS (BAB 84 / Idea #10) — pengurusan kenderaan & GPS.
 *
 * ✅ IDEA #10: ServiceTitan "Fleet Pro"/SCF Pro"-75% reduction in accident risk".
 * Tiada modul fleet dalam AI BIJAN. Untuk syarikat perkhidmatan, logistik,
 * dan penghantaran, ini kritikal.
 *
 * Ciri:
 * - Kenderaan + pemandu (kennung AI BIJAN)
 * - Lokasi terakhir (lat/lng) + sejarah perjalanan
 * - Per & Routes (Nhận dari Google Routes / Route4Me)
 * - Pemantauan GPS via webhook (Samsara, Fleetio, Motive adapter)
 * - Alert breakdown & amaran risiko (pola ServiceTitan)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Kenderaan
        Schema::create('fleet_vehicles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('plate_number', 20);
            $t->string('make', 60)->nullable();
            $t->string('model', 60)->nullable();
            $t->string('color', 40)->nullable();
            $t->string('type', 40)->default('van');
            $t->decimal('odometer_km', 12, 2)->default(0);
            $t->string('fuel_type', 30)->nullable();
            $t->decimal('fuel_level_percent', 5, 2)->nullable();
            $t->enum('status', ['active', 'maintenance', 'idle', 'retired'])->default('active');
            $t->foreignId('assigned_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'plate_number']);
        });

        // Pemandu
        Schema::create('fleet_drivers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 180);
            $t->string('phone', 30);
            $t->string('license_no', 40)->nullable();
            $t->string('license_class', 20)->nullable();
            $t->date('license_expiry')->nullable();
            $t->enum('status', ['active', 'on_leave', 'suspended', 'inactive'])->default('active');
            $t->decimal('safety_score', 5, 2)->default(100);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'phone']);
        });

        // Lokasi terakhir (satu baris semasa setiap kenderaan)
        Schema::create('fleet_vehicle_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->decimal('speed_kmh', 8, 2)->nullable();
            $t->decimal('heading', 6, 2)->nullable();
            $t->decimal('battery_percent', 5, 2)->nullable();
            $t->boolean('ignition_on')->nullable();
            $t->timestamp('recorded_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'vehicle_id']);
            $t->unique('vehicle_id');
        });

        // Sejarah perjalanan
        Schema::create('fleet_location_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $t->decimal('latitude', 10, 7);
            $t->decimal('longitude', 10, 7);
            $t->decimal('speed_kmh', 8, 2)->nullable();
            $t->boolean('harsh_braking')->default(false);
            $t->boolean('harsh_acceleration')->default(false);
            $t->boolean('harsh_cornering')->default(false);
            $t->timestamp('recorded_at');
            $t->timestamps();

            $t->index(['tenant_id', 'vehicle_id', 'recorded_at']);
        });

        // Perjalanan (trip)
        Schema::create('fleet_trips', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
            $t->foreignId('shipment_id')->nullable();
            $t->string('reference_no', 60)->nullable();
            $t->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $t->timestamp('started_at')->nullable();
            $t->timestamp('ended_at')->nullable();
            $t->decimal('distance_km', 12, 2)->default(0);
            $t->integer('duration_minutes')->nullable();
            $t->json('route_points')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'vehicle_id', 'started_at']);
        });

        // Amaran (breakdown, risiko,Leastfuel)
        Schema::create('fleet_alerts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vehicle_id')->nullable()->constrained('fleet_vehicles')->cascadeOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
            $t->enum('type', [
                'breakdown', 'low_fuel', 'harsh_braking', 'harsh_acceleration',
                'speeding', 'geofence_exit', 'idle_long', 'maintenance_due',
            ]);
            $t->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $t->string('title', 180);
            $t->text('message')->nullable();
            $t->json('context')->nullable();
            $t->timestamp('triggered_at')->nullable();
            $t->timestamp('acknowledged_at')->nullable();
            $t->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'severity', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_alerts');
        Schema::dropIfExists('fleet_trips');
        Schema::dropIfExists('fleet_location_histories');
        Schema::dropIfExists('fleet_vehicle_locations');
        Schema::dropIfExists('fleet_drivers');
        Schema::dropIfExists('fleet_vehicles');
    }
};
