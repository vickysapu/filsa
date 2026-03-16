<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kategori_pemasukans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('nama_kategori');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_pemasukans');
    }
};