<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('financial_report_details')) {
            DB::statement('ALTER TABLE financial_report_details MODIFY category_name VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
    }
};