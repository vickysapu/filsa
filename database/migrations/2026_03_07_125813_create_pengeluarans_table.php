<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('nomor_transaksi')->unique();
            $table->date('tanggal');
            $table->enum('kategori', [
                'operasional',
                'listrik_air',
                'gaji_karyawan',
                'kegiatan',
                'pemeliharaan',
                'amal_sosial',
                'konsumsi',
                'lainnya'
            ]);
            $table->string('keperluan');
            $table->string('penerima');
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('bukti_foto')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tanggal');
            $table->index('kategori');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluarans');
    }
};