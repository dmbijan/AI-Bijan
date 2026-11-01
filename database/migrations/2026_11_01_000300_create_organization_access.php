<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// OAM: struktur organisasi (kumpulan → syarikat → cawangan → jabatan → pasukan), skop keahlian,
// warisan peranan, kebenaran DITOLAK, had transaksi/kelulusan setiap peranan, status keahlian lanjutan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_units', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('org_units')->restrictOnDelete();
            $t->string('type', 12); // group, company, branch, department, team
            $t->string('name');
            $t->string('code', 30)->nullable();
            $t->string('legal_name')->nullable();
            $t->string('registration_number', 40)->nullable();
            $t->string('address')->nullable();
            $t->char('country', 2)->default('MY');
            $t->string('timezone', 40)->default('Asia/Kuala_Lumpur');
            $t->char('currency', 3)->default('MYR');
            $t->string('status', 10)->default('active'); // active, archived
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['tenant_id', 'type']);
            $t->unique(['tenant_id', 'code']);
        });

        Schema::table('tenant_users', function (Blueprint $t) {
            $t->timestamp('expires_at')->nullable();     // akses sementara (cth. kontraktor)
            $t->timestamp('suspended_at')->nullable();   // status disabled + suspended_at = digantung
            $t->timestamp('revoked_at')->nullable();     // status disabled + revoked_at = ditarik balik
            $t->string('status_reason')->nullable();
            $t->string('employee_no', 30)->nullable();
        });

        // Skop data setiap ahli. Tiada baris = lalai 'own_company' (semua dalam tenant, seperti sebelum OAM).
        Schema::create('membership_scopes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tenant_user_id')->constrained('tenant_users')->cascadeOnDelete();
            $t->foreignId('org_unit_id')->nullable()->constrained('org_units')->cascadeOnDelete(); // unit utama ahli
            $t->string('data_scope', 20)->default('own_unit'); // own, own_unit, unit_tree, selected, all
            $t->json('selected_unit_ids')->nullable();
            $t->timestamps();
            $t->unique('tenant_user_id');
        });

        Schema::table('roles', function (Blueprint $t) {
            $t->string('description')->nullable();
            $t->foreignId('parent_id')->nullable()->constrained('roles')->nullOnDelete(); // mewarisi kebenaran peranan induk
            $t->string('status', 10)->default('active');
        });

        Schema::create('role_permission_denies', function (Blueprint $t) {
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->primary(['role_id', 'permission_id']);
        });

        Schema::create('role_limits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->string('limit_key', 40); // approval.amount, pos.discount_amount, pos.refund_amount, purchase.amount
            $t->decimal('value', 14, 2);
            $t->unique(['role_id', 'limit_key']);
        });

        // Pentadbir perlu boleh melantik Juruwang tanpa melanggar peraturan "tak boleh beri kebenaran yang anda tiada".
        $perm = DB::table('permissions')->where('key', 'inventory.sell')->value('id');
        if ($perm) {
            foreach (DB::table('roles')->where('key', 'tenant_admin')->pluck('id') as $roleId) {
                DB::table('permission_role')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $perm]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_limits');
        Schema::dropIfExists('role_permission_denies');
        Schema::table('roles', function (Blueprint $t) {
            $t->dropConstrainedForeignId('parent_id');
            $t->dropColumn(['description', 'status']);
        });
        Schema::dropIfExists('membership_scopes');
        Schema::table('tenant_users', function (Blueprint $t) {
            $t->dropColumn(['expires_at', 'suspended_at', 'revoked_at', 'status_reason', 'employee_no']);
        });
        Schema::dropIfExists('org_units');
    }
};
