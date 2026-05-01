<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('church_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable()->index();

            $table->string('church_name', 255);
            $table->string('tagline', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Indonesia');

            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();

            $table->text('history')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->json('values')->nullable();

            $table->string('logo_path', 500)->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('documents')->nullable();

            $table->string('facebook', 255)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('youtube', 255)->nullable();
            $table->string('tiktok', 255)->nullable();

            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description', 500)->nullable();
            $table->json('meta_keywords')->nullable();

            $table->string('map_embed_code', 1000)->nullable();
            $table->string('map_latitude', 50)->nullable();
            $table->string('map_longitude', 50)->nullable();

            $table->time('service_time_sunday')->nullable();
            $table->time('service_time_wednesday')->nullable();
            $table->time('service_time_friday')->nullable();
            $table->text('service_note')->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_profiles');
    }
};
