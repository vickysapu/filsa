<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('financial_reports')) {
            Schema::create('financial_reports', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->string('report_type', 20);
                $table->integer('report_year');
                $table->integer('report_month')->nullable();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('total_income', 15, 2)->default(0);
                $table->decimal('total_expense', 15, 2)->default(0);
                $table->decimal('closing_balance', 15, 2)->default(0);
                $table->string('status', 20)->default('draft');
                $table->foreignUuid('submitted_to')->nullable()->constrained('branches')->nullOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('notes')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['branch_id', 'report_year', 'report_month']);
                $table->index(['status', 'submitted_to']);
            });
        } else {
            Schema::table('financial_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('financial_reports', 'submitted_by')) {
                    $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete()->after('submitted_at');
                }
                if (!Schema::hasColumn('financial_reports', 'created_by')) {
                    $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete()->after('submitted_by');
                }
                if (!Schema::hasColumn('financial_reports', 'updated_by')) {
                    $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
                }
            });
        }

        if (!Schema::hasTable('financial_report_forwards')) {
            Schema::create('financial_report_forwards', function (Blueprint $table) {
                $table->id();
                $table->uuid('financial_report_id');
                $table->foreign('financial_report_id')->references('id')->on('financial_reports')->cascadeOnDelete();
                $table->uuid('from_branch_id');
                $table->foreign('from_branch_id')->references('id')->on('branches')->cascadeOnDelete();
                $table->uuid('to_branch_id');
                $table->foreign('to_branch_id')->references('id')->on('branches')->cascadeOnDelete();
                $table->uuid('forwarded_by');
                $table->foreign('forwarded_by')->references('id')->on('users')->cascadeOnDelete();
                $table->timestamp('forwarded_at');
                $table->text('notes')->nullable();
                $table->string('action', 20)->default('submit');
                $table->timestamps();

                $table->index('financial_report_id');
                $table->index(['to_branch_id', 'action']);
            });
        }

        if (!Schema::hasTable('financial_report_details')) {
            Schema::create('financial_report_details', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('financial_report_id');
                $table->foreign('financial_report_id')->references('id')->on('financial_reports')->cascadeOnDelete();
                $table->string('category_code', 50);
                $table->string('category_name', 100);
                $table->string('category_group', 50);
                $table->string('type', 20);
                $table->decimal('amount', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['financial_report_id', 'category_code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_report_forwards');

        if (Schema::hasTable('financial_reports')) {
            Schema::table('financial_reports', function (Blueprint $table) {
                $table->dropForeign(['submitted_by']);
                $table->dropForeign(['created_by']);
                $table->dropForeign(['updated_by']);
                $table->dropColumn(['submitted_by', 'created_by', 'updated_by']);
            });
        }
    }
};
