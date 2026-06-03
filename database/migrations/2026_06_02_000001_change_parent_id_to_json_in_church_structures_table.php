<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Try to drop the foreign key constraint if it exists
        try {
            DB::statement('ALTER TABLE church_structures DROP FOREIGN KEY church_structures_parent_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might have already been dropped, continue
        }

        // Create temporary column for JSON data
        Schema::table('church_structures', function (Blueprint $table) {
            $table->json('parent_id_json')->nullable()->after('id');
        });

        // Copy and convert data from parent_id (string/uuid) to parent_id_json (array)
        // Each existing parent_id value becomes a single-element array
        DB::statement('UPDATE church_structures SET parent_id_json = JSON_ARRAY(parent_id) WHERE parent_id IS NOT NULL');

        // Drop the old parent_id column
        Schema::table('church_structures', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });

        // Rename parent_id_json to parent_id
        Schema::table('church_structures', function (Blueprint $table) {
            $table->renameColumn('parent_id_json', 'parent_id');
        });
    }

    public function down(): void
    {
        // Create temporary column for string data
        Schema::table('church_structures', function (Blueprint $table) {
            $table->string('parent_id_string')->nullable()->after('id');
        });

        // Convert JSON arrays back to single string (take first element)
        DB::statement("UPDATE church_structures SET parent_id_string = JSON_UNQUOTE(JSON_EXTRACT(parent_id, '$[0]')) WHERE parent_id IS NOT NULL");

        // Drop the JSON column
        Schema::table('church_structures', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });

        // Rename parent_id_string to parent_id
        Schema::table('church_structures', function (Blueprint $table) {
            $table->renameColumn('parent_id_string', 'parent_id');
        });

        // Re-create foreign key
        Schema::table('church_structures', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('church_structures')->nullOnDelete();
        });
    }
};
