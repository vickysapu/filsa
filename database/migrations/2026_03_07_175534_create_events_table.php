<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nama_event');
            $table->text('deskripsi');
            $table->text('deskripsi_lengkap')->nullable(); // Untuk website
            $table->foreignId('kategori_id');
            $table->datetime('tanggal_mulai');
            $table->datetime('tanggal_selesai')->nullable();
            $table->string('lokasi');
            $table->string('alamat_lengkap')->nullable();
            $table->string('gambar_featured')->nullable();
            $table->json('gambar_galeri')->nullable(); // Multiple images
            $table->string('pembicara')->nullable();
            $table->string('kontak_person')->nullable();
            $table->string('email_kontak')->nullable();
            $table->string('whatsapp_kontak')->nullable();
            $table->decimal('harga_tiket', 12, 2)->default(0);
            $table->integer('kuota_peserta')->nullable();
            $table->integer('jumlah_terdaftar')->default(0);
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_website')->default(true);
            $table->json('meta_tags')->nullable(); // SEO
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('slug');
            $table->index('status');
            $table->index('tanggal_mulai');
            $table->index('is_featured');
            $table->index('show_in_website');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};