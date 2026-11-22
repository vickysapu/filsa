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
            $table->text('youtube_stream_key')->nullable()->after('room_password');
        });
    }
    public function down(): void
    {
        Schema::table('meetings', fn (Blueprint $t) => $t->dropColumn('youtube_stream_key'));
    }
};
