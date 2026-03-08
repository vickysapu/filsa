<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumentasi_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('dokumentasi_albums')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->default('image'); // image, video
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('caption')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->json('metadata')->nullable(); // EXIF data, dimensions, etc
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('album_id');
            $table->index('is_cover');
            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumentasi_photos');
    }
};