<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-1c — 36 model tidak sefasa dengan jadualnya.
 *
 * Dikesan automatik oleh `php skrip/audit-tenant.php` (semakan
 * `$fillable` vs `pragma_table_info`). Pola: model dibangunkan/ditulis
 * semula untuk skema yang tidak pernah dimigrasikan, atau kolum dibuang
 * semasa penyatuan modul tanpa mengemas kini model.
 *
 * KESAN: `Model::create([...])` meletup dengan
 *   - `MassAssignmentException` (kolum tiada dalam `$fillable` DB), atau
 *   - `SQLSTATE[HY000]: no such column: X` (kolum tiada dalam jadual)
 *
 * Dalam erti kata lain: **ciri itu tidak boleh digunakan** walaupun route
 * wujud dan UI memaparkannya.
 *
 * STRATEGI
 * ────────
 * Tambah kolum yang HILANG sebagai nullable / default selamat. Ini
 * membolehkan `create()` berfungsi serta-merta tanpa migrasi data.
 * Tiada kolum dibuang — data sedia ada (jika ada) kekal.
 *
 * ⚠️ Kolum wang menggunakan `decimal(15,2)` (bukan float) dan kolum
 * peratusan `decimal(5,2)`, mengikut konvensyen projek.
 *
 * ⚠️ Idempoten: setiap kolum disemak `hasColumn` dahulu.
 *
 * SENARAI PENUH (36 model) — lihat `docs/AUDIT-SKEMA-MODEL.md` untuk
 * perincian setiap satu. Nota penting:
 *   • `DepreciationSchedule`, `DealActivity`, `SequenceExecution`,
 *     `WorkflowExecution` kehilangan `tenant_id` → ditambah + diisi
 *     daripada `tenant_id` entiti induk jika ada.
 *   • `documents` kehilangan 31 kolum (modul DocumentAI) — jadual ini
 *     dikongsi dengan SignDocs/Invoicing, jadi kolum baru semua nullable.
 */
return new class extends Migration
{
    /**
     * jadual → [kolum => definisi]
     *
     * Definisi: 'string' | 'text' | 'int' | 'bigint' | 'decimal' | 'bool'
     *           | 'date' | 'datetime' | 'json'
     */
    private const KOLUM = [

        // ── Affiliate ────────────────────────────────────────────────
        'affiliates' => [
            'user_id' => 'bigint',
            'crm_contact_id' => 'bigint',
            'company_name' => 'string',
            'profile_image' => 'string',
            'average_order_value' => 'decimal',
            'top_referring_domain' => 'string',
            'payment_details' => 'json',
            'notes' => 'text',
            'approved_by' => 'bigint',
        ],
        'affiliate_commissions' => [
            'type' => 'string',
            'base_amount' => 'decimal',
            'notes' => 'text',
            'paid_at' => 'datetime',
        ],
        'affiliate_referrals' => [
            'commission_earned' => 'decimal',
            'notes' => 'text',
        ],
        'affiliate_referral_codes' => [
            'properties' => 'json',
        ],

        // ── Analytics ────────────────────────────────────────────────
        'dashboard_widgets' => [
            'data_source' => 'string',
        ],
        'analytics_reports' => [
            'schedule_cron' => 'string',
            'data_source' => 'string',
            'columns' => 'json',
            'group_by' => 'json',
            'sort_by' => 'json',
        ],

        // ── Assets ───────────────────────────────────────────────────
        'assets' => [
            'asset_no' => 'string',
            'serial_number' => 'string',
            'barcode' => 'string',
            'description' => 'text',
            'purchase_price' => 'decimal',
            'vendor_id' => 'bigint',
            'assigned_to' => 'bigint',
            'warranty_provider' => 'string',
            'warranty_start' => 'date',
            'warranty_end' => 'date',
            'salvage_value' => 'decimal',
            'useful_life_years' => 'int',
            'depreciation_method' => 'string',
            'current_book_value' => 'decimal',
            'total_depreciation' => 'decimal',
            'disposed_date' => 'date',
            'disposal_value' => 'decimal',
            'created_by' => 'bigint',
        ],
        'asset_categories' => [
            'code' => 'string',
            'description' => 'text',
            'default_purchase_price' => 'decimal',
            'default_useful_life_years' => 'int',
            'depreciation_method' => 'string',
            'is_active' => 'bool',
        ],
        'depreciation_schedule' => [
            // ⚠️ tenant_id hilang — model guna BelongsToTenant tetapi kolum tiada
            'tenant_id' => 'bigint',
            'period' => 'string',
            'opening_value' => 'decimal',
            'closing_value' => 'decimal',
            'calculated_at' => 'datetime',
        ],

        // ── BrainHub ─────────────────────────────────────────────────
        'brainhub_forecasts' => [
            'source_module' => 'string',
            'title' => 'string',
            'description' => 'text',
            'model_used' => 'string',
            'forecast_horizon_days' => 'int',
            'predicted_value' => 'decimal',
            'confidence_lower' => 'decimal',
            'confidence_upper' => 'decimal',
            'confidence_level' => 'decimal',
            'accuracy_score' => 'decimal',
            'actual_value' => 'decimal',
            'actual_confirmed_at' => 'datetime',
            'metadata' => 'json',
        ],
        'brainhub_insights' => [
            'source_metric' => 'string',
            'metadata' => 'json',
        ],
        'brainhub_recommendations' => [
            'source_insight_id' => 'bigint',
            'related_metric' => 'string',
            'estimated_effort' => 'string',
            'metadata' => 'json',
        ],

        // ── COGS ─────────────────────────────────────────────────────
        'cogs_cost_components' => [
            'total_amount' => 'decimal',
        ],

        // ── DocumentAI (jadual dikongsi) ─────────────────────────────
        'documents' => [
            'document_number' => 'string',
            'description' => 'text',
            'file_path' => 'string',
            'file_mime' => 'string',
            'file_size' => 'bigint',
            'file_hash' => 'string',
            'document_type' => 'string',
            'ocr_processed' => 'bool',
            'ocr_processed_at' => 'datetime',
            'ocr_text' => 'text',
            'ocr_confidence' => 'decimal',
            'ml_extracted' => 'bool',
            'ml_extracted_at' => 'datetime',
            'extracted_fields' => 'json',
            'ml_confidence' => 'decimal',
            'classification_status' => 'string',
            'classification_label' => 'string',
            'classification_confidence' => 'decimal',
            'error_message' => 'text',
            'document_date' => 'date',
            'sender_name' => 'string',
            'sender_email' => 'string',
            'recipient_name' => 'string',
            'recipient_email' => 'string',
            'total_amount' => 'decimal',
            'currency' => 'string',
            'reference_number' => 'string',
            'tags' => 'json',
            'assigned_to' => 'bigint',
            'retention_until' => 'date',
            'is_confidential' => 'bool',
        ],
        'document_extraction_history' => [
            'extraction_type' => 'string',
            'model_version' => 'string',
            'input_data' => 'json',
            'confidence_score' => 'decimal',
            'processing_time_ms' => 'int',
        ],
        'document_shares' => [
            'share_token' => 'string',
            'shared_with_user' => 'bigint',
            'shared_with_email' => 'string',
            'is_active' => 'bool',
        ],
        'document_templates' => [
            'template_name' => 'string',
            'template_type' => 'string',
            'field_definitions' => 'json',
            'sample_image_path' => 'string',
            'usage_count' => 'int',
            'average_confidence' => 'decimal',
        ],
        'document_versions' => [
            'version_number' => 'int',
            'file_mime' => 'string',
            'file_size' => 'bigint',
            'file_hash' => 'string',
            'change_summary' => 'text',
            'uploaded_by' => 'bigint',
        ],

        // ── Loyalty ──────────────────────────────────────────────────
        'loyalty_members' => [
            'date_of_birth' => 'date',
            'gender' => 'string',
            'avatar_url' => 'string',
            'purchase_count' => 'int',
            'last_purchase_date' => 'date',
            'properties' => 'json',
        ],
        'loyalty_points_ledger' => [
            'metadata' => 'json',
        ],
        'loyalty_redemptions' => [
            'notes' => 'text',
            'properties' => 'json',
        ],
        'loyalty_rewards' => [
            'value' => 'decimal',
            'currency' => 'string',
            'code' => 'string',
            'redemption_limit_per_member' => 'int',
            'start_date' => 'date',
            'end_date' => 'date',
            'properties' => 'json',
        ],
        'loyalty_tiers' => [
            'color' => 'string',
            'max_points' => 'int',
            'demotion_grace_period' => 'int',
        ],

        // ── Marketing ────────────────────────────────────────────────
        'marketing_campaigns' => [
            'slug' => 'string',
            'goal' => 'string',
            'currency' => 'string',
            'segments' => 'json',
            'templates' => 'json',
            'settings' => 'json',
            'performance_data' => 'json',
        ],
        'marketing_contacts' => [
            'segment_id' => 'bigint',
            'properties' => 'json',
            'lead_score' => 'int',
            'status' => 'string',
            'consent_data' => 'json',
            'last_activity_at' => 'datetime',
        ],
        'marketing_deal_activities' => [
            'tenant_id' => 'bigint',
            'value' => 'decimal',
            'currency' => 'string',
        ],
        'marketing_lead_scores' => [
            'calculated_at' => 'datetime',
        ],
        'marketing_segments' => [
            'slug' => 'string',
            'count' => 'int',
        ],
        'marketing_sequence_executions' => [
            'tenant_id' => 'bigint',
            'stopped_at' => 'datetime',
            'stop_reason' => 'string',
            'context_data' => 'json',
        ],
        'marketing_vouchers' => [
            'type' => 'string',
            'value' => 'decimal',
            'max_discount' => 'decimal',
            'usage_limit' => 'int',
            'usage_per_customer' => 'int',
            'start_date' => 'date',
            'end_date' => 'date',
            'applicable_categories' => 'json',
        ],
        'marketing_workflow_executions' => [
            'tenant_id' => 'bigint',
            'context_data' => 'json',
        ],

        // ── Omnichannel ──────────────────────────────────────────────
        'omnichannel_messages' => [
            'content_type' => 'string',
            'media_url' => 'string',
            'media_path' => 'string',
            'external_reply_to_id' => 'string',
            'failed_at' => 'datetime',
        ],

        // ── Projects ─────────────────────────────────────────────────
        'projects' => [
            'priority' => 'string',
            'spent' => 'decimal',
            'tags' => 'json',
            'manager_id' => 'bigint',
            'client_id' => 'bigint',
            'completed_at' => 'datetime',
        ],
        'project_members' => [
            'role' => 'string',
        ],
        'tasks' => [
            'assigned_to' => 'bigint',
            'parent_task_id' => 'bigint',
            'order' => 'int',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
            'estimated_hours' => 'decimal',
            'actual_hours' => 'decimal',
            'story_points' => 'int',
            'labels' => 'json',
        ],
        'timesheets' => [
            'approved_at' => 'datetime',
            'rejection_reason' => 'text',
        ],
    ];

    public function up(): void
    {
        foreach (self::KOLUM as $jadual => $kolum) {
            if (! Schema::hasTable($jadual)) {
                continue;
            }

            $tambah = array_filter(
                $kolum,
                fn ($jenis, $nama) => ! Schema::hasColumn($jadual, $nama),
                ARRAY_FILTER_USE_BOTH
            );

            if ($tambah === []) {
                continue;
            }

            Schema::table($jadual, function (Blueprint $t) use ($tambah) {
                foreach ($tambah as $nama => $jenis) {
                    match ($jenis) {
                        'text' => $t->text($nama)->nullable(),
                        'int' => $t->integer($nama)->nullable(),
                        'bigint' => $t->unsignedBigInteger($nama)->nullable(),
                        'decimal' => $t->decimal($nama, 15, 2)->nullable(),
                        'bool' => $t->boolean($nama)->nullable(),
                        'date' => $t->date($nama)->nullable(),
                        'datetime' => $t->dateTime($nama)->nullable(),
                        'json' => $t->json($nama)->nullable(),
                        default => $t->string($nama)->nullable(),
                    };
                }
            });
        }
    }

    public function down(): void
    {
        // Tidak dibalikkan — membuang kolum akan memusnahkan data.
    }
};
