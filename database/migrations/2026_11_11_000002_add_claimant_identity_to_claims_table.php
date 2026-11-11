<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('claimant_name')->nullable()->after('claimant_id');
            $table->string('student_id', 40)->nullable()->after('claimant_name');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['claimant_name', 'student_id']);
        });
    }
};
