<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('latihan_pelayanan', function (Blueprint $table) {
            $table->id();

            // Foreign ke tabel pelayanans (UUID) - WAJIB
            $table->foreignUuid('pelayanan_id')
                  ->constrained('pelayanans')
                  ->onDelete('cascade');

            $table->string('nama_latihan');
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('lokasi')->nullable()->default('Gedung Utama');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['active', 'cancelled'])->default('active');

            $table->foreignUuid('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa
            $table->index('tanggal');
            $table->index('status');
            $table->index('pelayanan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('latihan_pelayanan');
    }
};