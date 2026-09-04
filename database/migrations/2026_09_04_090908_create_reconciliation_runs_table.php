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
        Schema::create('reconciliation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('executed_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('source_dataset_id')->constrained('datasets')->onDelete('cascade');
            $table->foreignId('target_dataset_id')->constrained('datasets')->onDelete('cascade');
            $table->unsignedInteger('total_matched')->default(0);
            $table->unsignedInteger('total_exceptions')->default(0);
            $table->string('status', 50)->default('pending');
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_runs');
    }
};
