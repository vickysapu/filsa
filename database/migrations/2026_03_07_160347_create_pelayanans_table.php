<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelayanans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_jadwal')->unique();
            $table->foreignId('kategori_id')->constrained('kategori_pelayanans')->onDelete('cascade');
            $table->string('nama_pelayanan');
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('lokasi')->default('Gedung Utama');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tanggal');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelayanans');
    }
};