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
        Schema::create('dataset_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained()->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('counterparty')->nullable();
            $table->text('description')->nullable();
            $table->string('reference_no')->nullable();
            $table->json('raw_data');
            $table->enum('status', ['unmatched', 'matched', 'exception'])->default('unmatched');
            $table->timestamp('transaction_date');
            $table->timestamps();

            // Indexes for core matching queries
            $table->index(['workspace_id', 'status']);
            $table->index('reference_no');
            $table->index('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataset_records');
    }
};
