<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unclassified_columns', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->json('missing_required_fields');
            $table->json('unclassified_headers');
            $table->enum('status', ['PENDING', 'RESOLVED', 'IGNORED'])->default('PENDING');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unclassified_columns');
    }
};