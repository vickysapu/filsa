<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('data_access_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('requester_name');
            $table->string('requester_email')->index();

            $table->string('project_name')->nullable();
            $table->string('project_url')->nullable(); 

            $table->text('purpose');
            $table->json('requested_tables'); 

            $table->uuid('branch_id')->nullable()->index(); 
            $table->string('branch_name')->nullable(); 

            $table->string('status')->default('pending')->index();
            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('request_hash', 64)->nullable()->index();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('data_access_grants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('request_id')->constrained('data_access_requests')->onDelete('cascade');
            $table->string('table_name')->index();

            $table->json('allowed_columns');
            $table->json('restricted_columns')->nullable();

            $table->json('filters')->nullable(); 

            $table->boolean('allow_create')->default(false);
            $table->boolean('allow_update')->default(false);
            $table->boolean('allow_delete')->default(false);

            $table->integer('rate_limit_per_minute')->default(60);
            $table->timestamp('expires_at')->nullable()->index();

            $table->timestamps();

            $table->index(['request_id', 'table_name']);
        });

        Schema::create('api_access_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('grant_id')->constrained('data_access_grants')->onDelete('cascade');

            $table->string('token_hash', 128)->unique(); 
            $table->string('token_prefix', 20)->index(); 

            $table->string('encryption_key')->nullable(); 
            $table->json('allowed_ips')->nullable();

            $table->timestamp('last_used_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();
        });

        Schema::create('data_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('token_id')->constrained('api_access_tokens')->onDelete('cascade');

            $table->string('endpoint');
            $table->string('method');
            $table->string('table_accessed')->index();
            $table->json('columns_accessed')->nullable();
            $table->integer('rows_returned')->default(0);

            $table->string('ip_address', 45)->index();
            $table->text('user_agent')->nullable();

            $table->timestamp('accessed_at')->useCurrent()->index();

            $table->index(['token_id', 'accessed_at']);
            $table->index(['table_accessed', 'accessed_at']);
            $table->index(['ip_address', 'accessed_at']); 
        });

        Schema::create('request_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('request_id')->constrained('data_access_requests')->onDelete('cascade');

            $table->string('token', 64)->unique()->index();
            $table->timestamp('expires_at')->index();
            $table->boolean('used')->default(false)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_verifications');
        Schema::dropIfExists('data_access_logs');
        Schema::dropIfExists('api_access_tokens');
        Schema::dropIfExists('data_access_grants');
        Schema::dropIfExists('data_access_requests');
    }
};
