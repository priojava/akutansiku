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
        // 1. Tabel Departemen (Divisi / Unit Organisasi)
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 50)->nullable();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        // 2. Tabel Proyek (Pekerjaan / Kontrak di bawah Departemen)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('code', 50)->nullable();
            $table->string('name', 150);
            $table->decimal('contract_amount', 18, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'on_hold'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'department_id', 'status']);
        });

        // 3. Tambahkan relasi department_id dan project_id ke transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('tag_id')->constrained('departments')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->after('department_id')->constrained('projects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['project_id']);
            $table->dropColumn(['department_id', 'project_id']);
        });

        Schema::dropIfExists('projects');
        Schema::dropIfExists('departments');
    }
};
