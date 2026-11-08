<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lengkapkan jadual yang hanya wujud dalam migrasi modul (bukan dalam
 * `database/migrations/`).
 *
 * Konteks: projek mempunyai migrasi DUPLIKAT antara `database/migrations/`
 * dan `app/Modules/<Mod>/database/migrations/`. Untuk menyatukan sumber
 * kanonik ke `database/migrations/`, migrasi ini menambah jadual yang
 * tertinggal sebelum salinan modul dibuang.
 *
 *  - `cash_shifts`           (POS)
 *  - `bookings_appointments` (Booking) — versi modul lebih lengkap
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cash_shifts')) {
            Schema::create('cash_shifts', function (Blueprint $t) {
                $t->id();
                $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

                // FK ke pos_registers hanya jika jadual wujud (fikiran berhati-hati).
                if (Schema::hasTable('pos_registers')) {
                    $t->foreignId('register_id')->constrained('pos_registers')->cascadeOnDelete();
                } else {
                    $t->unsignedBigInteger('register_id')->nullable();
                }

                $t->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->string('status', 20)->default('open');
                $t->decimal('opening_cash', 15, 2)->default(0);
                $t->decimal('closing_cash', 15, 2)->nullable();
                $t->decimal('expected_cash', 15, 2)->nullable();
                $t->decimal('cash_discrepancy', 15, 2)->nullable();
                $t->date('shift_date')->nullable();
                $t->timestamp('opened_at')->nullable();
                $t->timestamp('closed_at')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();

                $t->index(['tenant_id', 'status']);
                $t->index('register_id');
            });
        }

        if (! Schema::hasTable('bookings_appointments')) {
            Schema::create('bookings_appointments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $t->string('appointment_no', 60)->unique();

                if (Schema::hasTable('booking_services')) {
                    $t->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
                } else {
                    $t->unsignedBigInteger('service_id')->nullable();
                }

                if (Schema::hasTable('booking_service_providers')) {
                    $t->foreignId('provider_id')->nullable()->constrained('booking_service_providers')->nullOnDelete();
                } else {
                    $t->unsignedBigInteger('provider_id')->nullable();
                }

                if (Schema::hasTable('booking_slots')) {
                    $t->foreignId('slot_id')->nullable()->constrained('booking_slots')->nullOnDelete();
                } else {
                    $t->unsignedBigInteger('slot_id')->nullable();
                }

                $t->unsignedBigInteger('customer_id')->nullable();
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->string('status', 30)->default('draft');
                $t->dateTime('start_time');
                $t->dateTime('end_time');
                $t->dateTime('actual_start_time')->nullable();
                $t->dateTime('actual_end_time')->nullable();
                $t->text('notes')->nullable();
                $t->text('internal_notes')->nullable();
                $t->boolean('reminder_sent')->default(false);
                $t->dateTime('reminder_sent_at')->nullable();
                $t->dateTime('cancelled_at')->nullable();
                $t->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                $t->text('cancellation_reason')->nullable();
                $t->integer('rescheduled_from')->nullable();
                $t->timestamps();

                $t->index(['tenant_id', 'status']);
                $t->index(['provider_id', 'start_time']);
                $t->index(['customer_id', 'start_time']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings_appointments');
        Schema::dropIfExists('cash_shifts');
    }
};
