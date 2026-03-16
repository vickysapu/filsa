<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jemaats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->bigInteger('NIK');
            $table->string('nama_lengkap');
            $table->boolean('jenis_kelamin');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('alamat');
            $table->string('no_hp', 20);
            $table->string('email');
            $table->enum('status_pernikahan', ['belum menikah', 'menikah', 'janda', 'duda']);
            $table->string('pekerjaan');
            $table->string('pendidikan');
            $table->date('tanggal_bergabung');
            $table->boolean('status_jemaat');
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jemaats');
    }
};