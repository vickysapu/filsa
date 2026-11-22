<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('room_password')->nullable();
            $table->boolean('viewer_mode')->default(false);
            $table->string('side_type', 10)->default('none');
            $table->text('side_content')->nullable();
        });

        Schema::create('meeting_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id', 36);
            $table->string('user_id', 36);
            $table->unique(['meeting_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void {}
};
