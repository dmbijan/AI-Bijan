<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-ServiceOS — Service Business Operating System (BAB 87).
 *
 * P1 Service Core: projek perkhidmatan, sesi, sebut harga, kontrak,
 * penugasan kru, tugas, tempahan peralatan dan penyerahan.
 *
 * SEMUA jadual guna prefix `service_` untuk elak pertindihan dengan
 * `projects`, `bookings_appointments`, `invoices` dan `sign_documents`.
 *
 * NOTA: modul ini TIDAK menyimpan wang. Pembayaran kekal di Invoicing/Finance.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Pelanggan perkhidmatan (profil lanjutan, bukan pengganti CRM)
        Schema::create('service_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('display_name', 180);
            $table->string('company_name', 180)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('whatsapp', 40)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->string('country', 10)->default('MY');
            $table->string('source', 60)->nullable();
            $table->string('tier', 30)->default('regular');
            $table->decimal('lifetime_value', 14, 2)->default(0);
            $table->unsignedInteger('total_projects')->default(0);
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'email'], 'service_clients_tenant_email_unique');
            $table->index(['tenant_id', 'is_active']);
        });

        // 2. Pakej perkhidmatan (template harga)
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('code', 60);
            $table->string('name', 180);
            $table->string('category', 80)->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_price', 14, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('buffer_minutes')->default(0);
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->boolean('requires_deposit')->default(true);
            $table->decimal('deposit_percent', 5, 2)->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'service_packages_tenant_code_unique');
        });

        // 3. Projek perkhidmatan — rekod induk setiap kerja
        Schema::create('service_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('project_no', 40);
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('package_id')->nullable()->index();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('owner_user_id')->nullable()->index();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('industry', 80)->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('priority', 20)->default('normal');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('venue', 200)->nullable();
            $table->text('venue_address')->nullable();
            $table->unsignedInteger('guest_count')->nullable();
            $table->decimal('contract_value', 14, 2)->default(0);
            $table->decimal('estimated_cost', 14, 2)->default(0);
            $table->decimal('actual_cost', 14, 2)->default(0);
            $table->decimal('margin_amount', 14, 2)->default(0);
            $table->decimal('margin_percent', 6, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->json('custom_fields')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'project_no'], 'service_projects_tenant_no_unique');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'start_date']);
        });

        // 4. Sesi / jadual kerja dalam projek
        Schema::create('service_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('service_project_id')->index();
            $table->string('name', 150);
            $table->string('session_type', 50)->default('main');
            $table->string('status', 30)->default('planned');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('actual_start_at')->nullable();
            $table->dateTime('actual_end_at')->nullable();
            $table->string('venue', 200)->nullable();
            $table->unsignedInteger('sequence')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'start_at']);
            $table->index(['tenant_id', 'service_project_id']);
        });

        // 5. Sebut harga (versi)
        Schema::create('service_quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('service_project_id')->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('quote_no', 40);
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 30)->default('draft');
            $table->string('currency', 3)->default('MYR');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('deposit_amount', 14, 2)->default(0);
            $table->decimal('balance_amount', 14, 2)->default(0);
            $table->json('line_items')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'quote_no'], 'service_quotations_tenant_no_unique');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'service_project_id', 'is_current']);
        });

        // 6. Kontrak perkhidmatan
        Schema::create('service_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('service_project_id')->index();
            $table->unsignedBigInteger('service_quotation_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('sign_document_id')->nullable()->index();
            $table->string('contract_no', 40);
            $table->string('status', 30)->default('draft');
            $table->string('currency', 3)->default('MYR');
            $table->decimal('contract_value', 14, 2)->default(0);
            $table->decimal('deposit_amount', 14, 2)->default(0);
            $table->longText('body')->nullable();
            $table->json('clauses')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('accepted_by_name', 180)->nullable();
            $table->string('accepted_by_email', 180)->nullable();
            $table->string('accepted_ip', 45)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'contract_no'], 'service_contracts_tenant_no_unique');
            $table->index(['tenant_id', 'status']);
        });

        // 7. Kru / pekerja perkhidmatan
        Schema::create('service_crews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->string('name', 180);
            $table->string('role', 60)->default('crew');
            $table->string('phone', 40)->nullable();
            $table->string('email', 180)->nullable();
            $table->decimal('day_rate', 12, 2)->default(0);
            $table->decimal('hour_rate', 12, 2)->default(0);
            $table->json('skills')->nullable();
            $table->boolean('is_freelance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // 8. Penugasan kru ke projek / sesi
        Schema::create('service_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('service_project_id')->index();
            $table->unsignedBigInteger('service_session_id')->nullable()->index();
            $table->unsignedBigInteger('crew_id')->index();
            $table->string('role', 60)->default('crew');
            $table->string('status', 30)->default('assigned');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->decimal('agreed_rate', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'service_session_id', 'crew_id', 'role'],
                'service_assignments_unique'
            );
            $table->index(['tenant_id', 'crew_id', 'start_at']);
        });

        // 9. Tugas / checklist projek
        Schema::create('service_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('service_project_id')->index();
            $table->unsignedBigInteger('service_session_id')->nullable()->index();
            $table->unsignedBigInteger('assignee_user_id')->nullable()->index();
            $table->unsignedBigInteger('assignee_crew_id')->nullable()->index();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('phase', 50)->default('preparation');
            $table->string('status', 30)->default('pending');
            $table->string('priority', 20)->default('normal');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'service_project_id', 'status']);
            $table->index(['tenant_id', 'due_at']);
        });

        // 10. Peralatan + tempahan
        Schema::create('service_equipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('asset_id')->nullable()->index();
            $table->string('code', 60);
            $table->string('name', 180);
            $table->string('category', 80)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity_total')->default(1);
            $table->decimal('replacement_value', 12, 2)->default(0);
            $table->boolean('requires_maintenance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'service_equipments_tenant_code_unique');
        });

        Schema::create('service_equipment_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('service_equipment_id')->index();
            $table->unsignedBigInteger('service_project_id')->index();
            $table->unsignedBigInteger('service_session_id')->nullable()->index();
            $table->unsignedInteger('quantity')->default(1);
            $table->dateTime('reserved_from');
            $table->dateTime('reserved_to');
            $table->string('status', 30)->default('reserved');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(
                ['tenant_id', 'service_equipment_id', 'reserved_from'],
                'service_equip_res_tenant_equip_from_idx'
            );
        });

        // 11. Log peristiwa domain (outbox ringkas)
        Schema::create('service_domain_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('event_name', 80)->index();
            $table->string('aggregate_type', 60);
            $table->unsignedBigInteger('aggregate_id');
            $table->json('payload')->nullable();
            $table->string('status', 30)->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('dispatched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->string('idempotency_key', 120)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'event_name'], 'service_events_tenant_status_idx');
            $table->unique(['tenant_id', 'idempotency_key'], 'service_events_idem_unique');
        });

        // 12. Penyerahan (delivery / handover)
        Schema::create('service_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('service_project_id')->index();
            $table->string('delivery_type', 50)->default('handover');
            $table->string('status', 30)->default('pending');
            $table->json('items')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->string('received_by_name', 180)->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'service_project_id', 'status'], 'service_deliveries_proj_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_deliveries');
        Schema::dropIfExists('service_domain_events');
        Schema::dropIfExists('service_equipment_reservations');
        Schema::dropIfExists('service_equipments');
        Schema::dropIfExists('service_tasks');
        Schema::dropIfExists('service_assignments');
        Schema::dropIfExists('service_crews');
        Schema::dropIfExists('service_contracts');
        Schema::dropIfExists('service_quotations');
        Schema::dropIfExists('service_sessions');
        Schema::dropIfExists('service_projects');
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('service_clients');
    }
};
