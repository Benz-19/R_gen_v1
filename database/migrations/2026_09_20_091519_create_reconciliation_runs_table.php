<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('executed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

                $table->string('run_identifier')->unique();

            $table->string('source_a_type')->nullable();
            $table->string('source_b_type')->nullable();

            $table->string('source_a_filename')->nullable();
            $table->string('source_b_filename')->nullable();

            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();

            $table->decimal('amount_tolerance', 15, 2)->default(0);
            $table->unsignedInteger('date_window_days')->default(2);
            $table->decimal('ml_threshold', 5, 2)->default(85);

            $table->json('active_modules')->nullable();

            $table->unsignedInteger('matched_count')->default(0);
            $table->unsignedInteger('unmatched_a_count')->default(0);
            $table->unsignedInteger('unmatched_b_count')->default(0);

            $table->json('unmatched_a_rows')->nullable();
            $table->json('unmatched_b_rows')->nullable();

            $table->decimal('match_rate', 5, 2)->default(0);

            $table->json('summary_data')->nullable();

            $table->string('output_directory')->nullable();

            $table->unsignedInteger('total_matched')->default(0);
            $table->unsignedInteger('total_exceptions')->default(0);

            $table->string('status', 50)->default('pending');

            $table->timestamps();

            $table->index(['workspace_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_runs');
    }
};