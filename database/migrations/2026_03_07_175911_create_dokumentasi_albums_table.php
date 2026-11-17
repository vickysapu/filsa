<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumentasi_albums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('judul_album');
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('kategori_id')->constrained('dokumentasi_kategoris')->onDelete('cascade');
            $table->foreignUuid('event_id')->nullable()->constrained('events')->onDelete('set null');
            $table->string('cover_image')->nullable();
            $table->string('fotografer')->nullable();
            $table->date('tanggal_album');
            $table->string('lokasi')->nullable();
            $table->integer('jumlah_foto')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_download')->default(false);
            $table->boolean('show_in_website')->default(true);
            $table->json('meta_tags')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('slug');
            $table->index('status');
            $table->index('tanggal_album');
            $table->index('show_in_website');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumentasi_albums');
    }
};