<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('subdomain')->unique()->nullable()->after('name');
            $table->index('subdomain');
            $table->json('settings')->default('{}')->after('subdomain');
        });

        DB::statement("ALTER TABLE organizations ADD CONSTRAINT check_subdomain_format CHECK (subdomain ~ '^[a-z0-9]([a-z0-9-]{1,61}[a-z0-9])?$')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE organizations DROP CONSTRAINT IF EXISTS check_subdomain_format");
        
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('subdomain');
            $table->dropColumn('settings');
        });
    }
};
