<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('church_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->uuid('jemaat_id')->nullable();
            $table->string('position_name');
            $table->string('position_level')->default('staff');
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('parent_id')->references('id')->on('church_structures')->nullOnDelete();
            $table->foreign('jemaat_id')->references('id')->on('jemaats')->nullOnDelete();
            $table->index(['branch_id', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_structures');
    }
};
