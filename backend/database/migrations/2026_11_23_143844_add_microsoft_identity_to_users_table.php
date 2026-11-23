<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('microsoft_oid')->nullable();
            $table->uuid('microsoft_tid')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->unique(['microsoft_tid', 'microsoft_oid']);
        });
    }

    /**
     * Restore the scaffold schema. Empty hashes keep passwordless accounts
     * intact and are rejected by Laravel's password verifier.
     */
    public function down(): void
    {
        DB::table('users')->whereNull('password')->update(['password' => '']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['microsoft_tid', 'microsoft_oid']);
            $table->dropColumn(['microsoft_oid', 'microsoft_tid', 'is_admin']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->change();
        });
    }
};
