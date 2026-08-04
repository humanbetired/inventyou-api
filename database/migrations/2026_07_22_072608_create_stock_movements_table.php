<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('source_branch_id')->nullable()->constrained('branches')->restrictOnDelete();
            $table->foreignId('destination_branch_id')->nullable()->constrained('branches')->restrictOnDelete();
            $table->integer('quantity');
            $table->string('type');
            $table->foreignId('stock_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
