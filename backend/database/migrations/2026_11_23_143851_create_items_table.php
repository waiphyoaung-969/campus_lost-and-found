<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->index()->constrained()->restrictOnDelete();
            $table->enum('type', ['lost', 'found']);
            $table->string('name', 100);
            $table->text('description');
            $table->date('date_seen');
            $table->string('holding_location', 160)->nullable();
            $table->enum('status', ['open', 'resolved', 'closed'])->default('open');
            $table->timestamps();

            $table->index(['status', 'date_seen']);
            $table->index(['type', 'status', 'date_seen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
