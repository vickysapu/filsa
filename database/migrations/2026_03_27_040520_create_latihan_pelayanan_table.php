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

            // Foreign ke tabel pelayanans (UUID)
            $table->foreignUuid('pelayanan_id')
                  ->nullable()
                  ->constrained('pelayanans')
                  ->onDelete('cascade');

            // Foreign ke kategori
            $table->foreignUuid('kategori_id')
                  ->constrained('kategori_pelayanans')
                  ->onDelete('cascade');

            $table->string('nama_latihan');
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['active', 'cancelled'])->default('active');

            $table->foreignUuid('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            $table->index('tanggal');
            $table->index('status');
            $table->index('pelayanan_id');
        });

        // =============================================
        // TABEL PIVOT PESERTA LATIHAN
        // =============================================
        Schema::create('latihan_pelayanan_detail', function (Blueprint $table) {
            $table->id();

            // Foreign ke latihan_pelayanan (id biasa / auto increment)
            $table->foreignId('latihan_pelayanan_id')
                  ->constrained('latihan_pelayanan')
                  ->onDelete('cascade');

            // ← INI YANG DIPERBAIKI
            // Foreign ke jemaats (harus foreignUuid karena jemaats.id = UUID)
            $table->foreignUuid('jemaat_id')
                  ->constrained('jemaats')
                  ->onDelete('cascade');

            $table->string('role')->nullable();

            $table->timestamps();

            // Agar tidak ada duplikat jemaat di satu latihan
            $table->unique(['latihan_pelayanan_id', 'jemaat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('latihan_pelayanan_detail');
        Schema::dropIfExists('latihan_pelayanan');
    }
};