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
        Schema::create('exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('reconciliation_run_id')->constrained('reconciliation_runs')->onDelete('cascade');
            $table->foreignId('source_record_id')->constrained('dataset_records')->onDelete('cascade');
            $table->foreignId('target_record_id')->nullable()->constrained('dataset_records')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('exception_type', 50)->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('status', 50)->default('open');
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exceptions');
    }
};
