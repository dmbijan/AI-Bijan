<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('type', 20);                        // products | customers | opening_stock
            $t->string('status', 12)->default('preview');  // preview | committed | cancelled
            $t->string('filename', 190)->nullable();
            $t->json('mapping');                           // medan => indeks lajur
            $t->json('options')->nullable();
            $t->unsignedInteger('total_rows')->default(0);
            $t->unsignedInteger('valid_rows')->default(0);
            $t->unsignedInteger('error_rows')->default(0);
            $t->json('result')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('committed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('committed_at')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'type', 'status']);
        });

        Schema::create('import_rows', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('import_job_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('row_no');
            $t->json('data');
            $t->json('errors')->nullable();
            $t->string('action', 10);                      // create | update | skip
            $t->unsignedBigInteger('target_id')->nullable();
            $t->index(['import_job_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
        Schema::dropIfExists('import_jobs');
    }
};
