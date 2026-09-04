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
        Schema::create('reconciliation_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_run_id')->constrained('reconciliation_runs')->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('source_record_id')->constrained('dataset_records')->onDelete('cascade');
            $table->foreignId('target_record_id')->constrained('dataset_records')->onDelete('cascade');
            $table->string('match_type', 50)->nullable();
            $table->decimal('confidence_score', 5, 4)->default(1.0000);
            $table->string('status', 50)->default('proposed');
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index('reconciliation_run_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_matches');
    }
};
