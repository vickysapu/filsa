<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email_attempt')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->boolean('success')->default(false);
            $table->string('failure_reason')->nullable(); 
            $table->timestamp('attempted_at')->useCurrent();
            
            $table->index(['ip_address', 'attempted_at']);
            $table->index(['email_attempt', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_activities');
    }
};