<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Buat tabel churches untuk mendata gereja/pendeta di bawah MD
        if (!Schema::hasTable('churches')) {
            Schema::create('churches', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->string('name');
                $table->string('pic_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('financial_report_details') && !Schema::hasColumn('financial_report_details', 'church_id')) {
            Schema::table('financial_report_details', function (Blueprint $table) {
                $table->foreignUuid('church_id')->nullable()->after('financial_report_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('financial_report_details') && Schema::hasColumn('financial_report_details', 'church_id')) {
            Schema::table('financial_report_details', function (Blueprint $table) {
                $table->dropColumn('church_id');
            });
        }
        Schema::dropIfExists('churches');
    }
};
