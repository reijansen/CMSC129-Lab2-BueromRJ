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
        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['user_id', 'deleted_at']);
            $table->dropUnique(['user_id', 'name', 'type']);
        });

        // MySQL doesn't support partial indexes (WHERE clause), so use regular unique index
        // This allows soft-deleted records to bypass the unique constraint
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['user_id', 'name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'deleted_at']);
            $table->dropSoftDeletes();
            $table->dropUnique(['user_id', 'name', 'type']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['user_id', 'name', 'type']);
        });
    }
};
