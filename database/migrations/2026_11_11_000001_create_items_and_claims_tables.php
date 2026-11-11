<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('type'); $table->string('name'); $table->string('category'); $table->text('description'); $table->date('date_seen'); $table->string('location'); $table->string('holding_location')->nullable(); $table->json('photos')->nullable(); $table->string('status')->default('open'); $table->timestamps(); $table->index(['type', 'status', 'category']);
        });
        Schema::create('claims', function (Blueprint $table) {
            $table->id(); $table->foreignId('item_id')->constrained()->cascadeOnDelete(); $table->foreignId('claimant_id')->constrained('users')->cascadeOnDelete(); $table->text('proof'); $table->string('status')->default('pending'); $table->text('response_note')->nullable(); $table->timestamps(); $table->unique(['item_id', 'claimant_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('claims'); Schema::dropIfExists('items'); }
};
