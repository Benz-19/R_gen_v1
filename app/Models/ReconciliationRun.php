<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconciliationRun extends Model
{
    protected $fillable = [
        'executed_by',
        'workspace_id',
        'run_identifier',
        'source_a_type',
        'source_b_type',
        'source_a_filename',
        'source_b_filename',
        'target_start_date',
        'target_end_date',
        'amount_tolerance',
        'date_window_days',
        'ml_threshold',
        'active_modules',
        'matched_count',
        'unmatched_a_count',
        'unmatched_b_count',
        'match_rate',
        'summary_data',
        'output_directory',
        'status',
        'total_exceptions',
        'total_matched',
        'unmatched_a_rows',
        'unmatched_b_rows',
    ];

    protected $casts = [
        'target_start_date' => 'date',
        'target_end_date' => 'date',
        'amount_tolerance' => 'decimal:2',
        'date_window_days' => 'integer',
        'ml_threshold' => 'decimal:2',
        'active_modules' => 'array',
        'matched_count' => 'integer',
        'unmatched_a_count' => 'integer',
        'unmatched_b_count' => 'integer',
        'match_rate' => 'decimal:2',
        'summary_data' => 'array',
        'unmatched_a_rows' => 'array',
        'unmatched_b_rows' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->run_identifier)) {
                $model->run_identifier = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
