<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('data_access_grants')) {
            // Ubah seluruh grant akses data yang sebelumnya memiliki tanggal kedaluwarsa menjadi permanen (null)
            DB::table('data_access_grants')
                ->whereNotNull('expires_at')
                ->update(['expires_at' => null]);
        }
    }

    public function down(): void
    {
        // Tidak perlu mengembalikan tanggal kedaluwarsa lama
    }
};
