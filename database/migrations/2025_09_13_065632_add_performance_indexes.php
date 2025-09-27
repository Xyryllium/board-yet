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
        // Skip boards.organization_id - it already exists from foreign key constraint
        
        // Add index on users.current_organization_id for fast user org lookups
        Schema::table('users', function (Blueprint $table) {
            $table->index('current_organization_id');
        });

        // Add index on columns.board_id for fast column lookups
        Schema::table('columns', function (Blueprint $table) {
            $table->index('board_id');
        });

        // Add index on cards.column_id for fast card lookups
        Schema::table('cards', function (Blueprint $table) {
            $table->index('column_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip boards.organization_id - don't drop it as it's needed for foreign key
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['current_organization_id']);
        });

        Schema::table('columns', function (Blueprint $table) {
            $table->dropIndex(['board_id']);
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->dropIndex(['column_id']);
        });
    }
};