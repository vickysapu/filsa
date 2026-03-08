<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelayanan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelayanan_id')->constrained('pelayanans')->onDelete('cascade');
            $table->foreignId('jemaat_id')->constrained('jemaats')->onDelete('cascade');
            $table->string('role')->nullable(); 
            $table->enum('status', ['active', 'off', 'cancelled'])->default('active');
            $table->date('tanggal_off')->nullable();
            $table->text('alasan_off')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['pelayanan_id', 'jemaat_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelayanan_details');
    }
};