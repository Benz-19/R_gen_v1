<?php

namespace App\Services;

use App\Models\ReconciliationRun;
use App\Models\UnclassifiedColumn;
use App\Services\Workspace\WorkspaceService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReconciliationService
{
    /**
     * Execute a reconciliation using already-stored source files.
     *
     * This service does not depend on:
     * - Laravel Jobs
     * - UploadedFile objects
     * - Queue workers
     * - HTTP requests
     *
     * It receives only serializable data and storage paths.
     */
    public function executeRunFromPaths(
        array $data,
        string $storedPathA,
        string $storedPathB,
        mixed $userId,
        ReconciliationRun $run,
        mixed $workspace_id
    ): ReconciliationRun {
        $runTimestamp = now()->format('Ymd_His') . '_' . Str::random(8);

        $relativeRunDir = "reconciliation_runs/{$runTimestamp}";
        $outputDirRelative = "{$relativeRunDir}/output";

        /*
        |--------------------------------------------------------------------------
        | Create reconciliation directories
        |--------------------------------------------------------------------------
        */

        Storage::makeDirectory($relativeRunDir);
        Storage::makeDirectory($outputDirRelative);

        /*
        |--------------------------------------------------------------------------
        | Resolve source files
        |--------------------------------------------------------------------------
        */

        $fullPathA = $this->resolveStoragePath($storedPathA);
        $fullPathB = $this->resolveStoragePath($storedPathB);

        /*
        |--------------------------------------------------------------------------
        | Verify source files
        |--------------------------------------------------------------------------
        */

        if (!File::exists($fullPathA)) {
            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                "Source A file does not exist: {$fullPathA}"
            );
        }

        if (!File::exists($fullPathB)) {
            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                "Source B file does not exist: {$fullPathB}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Copy source files into the actual reconciliation run directory
        |--------------------------------------------------------------------------
        */

        $extensionA = strtolower(
            pathinfo($fullPathA, PATHINFO_EXTENSION)
        ) ?: 'csv';

        $extensionB = strtolower(
            pathinfo($fullPathB, PATHINFO_EXTENSION)
        ) ?: 'csv';

        $relativePathA = "{$relativeRunDir}/source_a.{$extensionA}";
        $relativePathB = "{$relativeRunDir}/source_b.{$extensionB}";

        Storage::put(
            $relativePathA,
            File::get($fullPathA)
        );

        Storage::put(
            $relativePathB,
            File::get($fullPathB)
        );

        $fullPathA = Storage::path($relativePathA);
        $fullPathB = Storage::path($relativePathB);

        $outputDir = Storage::path($outputDirRelative);

        /*
        |--------------------------------------------------------------------------
        | Verify copied files and output directory
        |--------------------------------------------------------------------------
        */

        if (!File::exists($fullPathA)) {
            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                "Source A could not be copied into the reconciliation directory: {$fullPathA}"
            );
        }

        if (!File::exists($fullPathB)) {
            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                "Source B could not be copied into the reconciliation directory: {$fullPathB}"
            );
        }

        if (!File::isDirectory($outputDir)) {
            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                "Reconciliation output directory does not exist: {$outputDir}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Read reconciliation configuration
        |--------------------------------------------------------------------------
        */

        $sourceAType = $data['source_a_connection']
            ?? $data['source_type_a']
            ?? null;

        $sourceBType = $data['source_b_connection']
            ?? $data['source_type_b']
            ?? null;

        $startDate = $data['target_start_date']
            ?? $data['start_date']
            ?? null;

        $endDate = $data['target_end_date']
            ?? $data['end_date']
            ?? null;

        $amountTolerance = $data['amount_tolerance'] ?? 0.00;

        $dateWindowDays = $data['date_window_days'] ?? 2;

        $mlThreshold = $data['ml_threshold'] ?? 85;

        $modules = $data['ml_modules'] ?? [];

        if (!is_array($modules)) {
            $modules = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Determine original filenames
        |--------------------------------------------------------------------------
        */

        $sourceAFilename = $data['source_a_filename']
            ?? basename($storedPathA);

        $sourceBFilename = $data['source_b_filename']
            ?? basename($storedPathB);

        /*
        |--------------------------------------------------------------------------
        | Workspace
        |--------------------------------------------------------------------------
        */

        $workspaceId = $workspace_id;

        /*
        |--------------------------------------------------------------------------
        | Update run as PROCESSING
        |--------------------------------------------------------------------------
        */

        $run->update([
            'workspace_id' => $workspaceId,
            'executed_by' => $userId,
            'run_identifier' => $runTimestamp,

            'source_a_type' => $sourceAType,
            'source_b_type' => $sourceBType,

            'source_a_filename' => $sourceAFilename,
            'source_b_filename' => $sourceBFilename,

            'target_start_date' => $startDate,
            'target_end_date' => $endDate,

            'amount_tolerance' => $amountTolerance,
            'date_window_days' => $dateWindowDays,
            'ml_threshold' => $mlThreshold,

            'active_modules' => $modules,

            'output_directory' => $relativeRunDir,

            'status' => 'PROCESSING',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate required dates before Python starts
        |--------------------------------------------------------------------------
        */

        if (!$startDate || !$endDate) {
            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                'Reconciliation start date and end date are required.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Python engine
        |--------------------------------------------------------------------------
        */

        $pythonPath = config(
            'reconciliation.python_path',
            'python'
        );

        $scriptPath = base_path(
            'ml_engine/recon_service.py'
        );

        if (!File::exists($scriptPath)) {
            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                "Reconciliation Python script was not found: {$scriptPath}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build Python command
        |--------------------------------------------------------------------------
        */

        $command = [
            $pythonPath,
            $scriptPath,

            $fullPathA,
            $fullPathB,

            '--output',
            $outputDir,

            '--start-date',
            $startDate,

            '--end-date',
            $endDate,

            '--variance-tolerance',
            (string) $amountTolerance,

            '--settlement-buffer',
            (string) $dateWindowDays,

            '--fuzzy-threshold',
            (string) $mlThreshold,
        ];

        /*
        |--------------------------------------------------------------------------
        | ML modules
        |--------------------------------------------------------------------------
        */

        if (in_array(
            'levenshtein_string',
            $modules,
            true
        )) {
            $command[] = '--enable-fuzzy';
        }

        if (in_array(
            'fx_conversion',
            $modules,
            true
        )) {
            $command[] = '--enable-fx';
        }

        if (in_array(
            'n_to_one',
            $modules,
            true
        )) {
            $command[] = '--enable-split';
        }

        if (in_array(
            'fee_deduction_model',
            $modules,
            true
        )) {
            $command[] = '--enable-gateway';
        }

        /*
        |--------------------------------------------------------------------------
        | Run Python reconciliation engine
        |--------------------------------------------------------------------------
        */

        set_time_limit(0);

        Log::info(
            'Starting Reconciliation Engine',
            [
                'run_id' => $run->id,
                'run_identifier' => $runTimestamp,

                'source_a' => $fullPathA,
                'source_b' => $fullPathB,

                'output_directory' => $outputDir,

                'command' => $command,
            ]
        );

        $processResult = Process::timeout(0)->run(
            $command
        );

        /*
        |--------------------------------------------------------------------------
        | Python engine failed
        |--------------------------------------------------------------------------
        */

        if ($processResult->failed()) {
            $errorOutput = $processResult->errorOutput();
            $standardOutput = $processResult->output();

            /*
            |--------------------------------------------------------------------------
            | Capture DB_LOG_PAYLOAD
            |--------------------------------------------------------------------------
            */

            if (str_contains(
                $errorOutput,
                'DB_LOG_PAYLOAD:'
            )) {
                preg_match(
                    '/DB_LOG_PAYLOAD:(.*)/',
                    $errorOutput,
                    $matches
                );

                if (!empty($matches[1])) {
                    $payload = json_decode(
                        trim($matches[1]),
                        true
                    );

                    if (is_array($payload)) {
                        UnclassifiedColumn::create([
                            'file_name' =>
                                $payload['file_name']
                                ?? 'Unknown',

                            'missing_required_fields' =>
                                $payload['missing_required']
                                ?? [],

                            'unclassified_headers' =>
                                $payload['unclassified_headers']
                                ?? [],

                            'status' => 'PENDING',
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Log failure
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Reconciliation Engine Execution Failed',
                [
                    'run_id' => $run->id,

                    'run_identifier' =>
                        $runTimestamp,

                    'error_output' =>
                        $errorOutput,

                    'standard_output' =>
                        $standardOutput,

                    'source_a' =>
                        $fullPathA,

                    'source_b' =>
                        $fullPathB,

                    'output_directory' =>
                        $outputDir,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Mark same run FAILED
            |--------------------------------------------------------------------------
            */

            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                'Reconciliation processing engine failed: ' .
                $errorOutput
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify summary output
        |--------------------------------------------------------------------------
        */

        $summaryFilePath =
            "{$outputDir}/reconciliation_summary.json";

        if (!File::exists($summaryFilePath)) {
            Log::error(
                'Reconciliation Engine Missing Summary Output',
                [
                    'run_id' => $run->id,

                    'run_identifier' =>
                        $runTimestamp,

                    'summary_file' =>
                        $summaryFilePath,

                    'output_directory' =>
                        $outputDir,
                ]
            );

            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                'Engine finished without producing summary metrics output.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Read summary
        |--------------------------------------------------------------------------
        */

        $summaryContents = File::get(
            $summaryFilePath
        );

        $summaryData = json_decode(
            $summaryContents,
            true
        );

        if (!is_array($summaryData)) {
            Log::error(
                'Invalid Reconciliation Summary Output',
                [
                    'run_id' =>
                        $run->id,

                    'summary_file' =>
                        $summaryFilePath,
                ]
            );

            $run->update([
                'status' => 'FAILED',
            ]);

            throw new \RuntimeException(
                'Engine produced an invalid reconciliation summary output.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Extract metrics
        |--------------------------------------------------------------------------
        */

        $unmatchedA =
            $summaryData['unmatched_source_a']
            ?? 0;

        $unmatchedB =
            $summaryData['unmatched_source_b']
            ?? 0;

        $matchedCount =
            $summaryData['matched_transactions_or_groups']
            ?? 0;

        $matchRate =
            $summaryData['match_rate_source_a_percent']
            ?? 0.0;

        /*
        |--------------------------------------------------------------------------
        | Update SAME run as COMPLETED
        |--------------------------------------------------------------------------
        */

        $run->update([
            'workspace_id' => $workspaceId,

            'executed_by' => $userId,

            'run_identifier' => $runTimestamp,

            'source_a_type' => $sourceAType,

            'source_b_type' => $sourceBType,

            'source_a_filename' =>
                $sourceAFilename,

            'source_b_filename' =>
                $sourceBFilename,

            'target_start_date' =>
                $startDate,

            'target_end_date' =>
                $endDate,

            'amount_tolerance' =>
                $amountTolerance,

            'date_window_days' =>
                $dateWindowDays,

            'ml_threshold' =>
                $mlThreshold,

            'active_modules' =>
                $modules,

            'matched_count' =>
                $matchedCount,

            'unmatched_a_count' =>
                $unmatchedA,

            'unmatched_b_count' =>
                $unmatchedB,

            'match_rate' =>
                $matchRate,

            'summary_data' =>
                $summaryData,

            'output_directory' =>
                $relativeRunDir,

            'status' =>
                'COMPLETED',

            'total_matched' =>
                $matchedCount,

            'total_exceptions' =>
                $unmatchedA + $unmatchedB,

            'unmatched_a_rows' =>
                $summaryData['unmatched_a_rows']
                ?? [],

            'unmatched_b_rows' =>
                $summaryData['unmatched_b_rows']
                ?? [],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Success log
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Reconciliation Engine Completed Successfully',
            [
                'run_id' =>
                    $run->id,

                'run_identifier' =>
                    $runTimestamp,

                'matched_count' =>
                    $matchedCount,

                'unmatched_a' =>
                    $unmatchedA,

                'unmatched_b' =>
                    $unmatchedB,

                'match_rate' =>
                    $matchRate,

                'output_directory' =>
                    $outputDir,
            ]
        );

        return $run->fresh();
    }

    /**
     * Resolve a storage-relative path or absolute filesystem path.
     */
    private function resolveStoragePath(
        string $path
    ): string {
        /*
         * Already an absolute filesystem path.
         */
        if (File::exists($path)) {
            return $path;
        }

        /*
         * Storage-relative path.
         */
        return Storage::path($path);
    }

    /**
     * Return complete details for a reconciliation run.
     */
    public function getRunDetails(
        int|string $id
    ): ?array {
        $runRecord = ReconciliationRun::find($id);

        if (!$runRecord) {
            return null;
        }

        if (!$runRecord->output_directory) {
            return [
                'run' => $runRecord,
                'summary' => $runRecord->summary_data,
                'tables' => [
                    'matches' => [],
                    'unmatched_a' => [],
                    'unmatched_b' => [],
                ],
            ];
        }

        $outputDir = Storage::path(
            "{$runRecord->output_directory}/output"
        );

        $matchesCsv =
            "{$outputDir}/reconciliation_matches.csv";

        $unmatchedACsv =
            "{$outputDir}/reconciliation_unmatched_a.csv";

        $unmatchedBCsv =
            "{$outputDir}/reconciliation_unmatched_b.csv";

        return [
            'run' => $runRecord,

            'summary' =>
                $runRecord->summary_data,

            'tables' => [
                'matches' =>
                    File::exists($matchesCsv)
                        ? $this->parseCsvToAssoc($matchesCsv)
                        : [],

                'unmatched_a' =>
                    File::exists($unmatchedACsv)
                        ? $this->parseCsvToAssoc($unmatchedACsv)
                        : [],

                'unmatched_b' =>
                    File::exists($unmatchedBCsv)
                        ? $this->parseCsvToAssoc($unmatchedBCsv)
                        : [],
            ],
        ];
    }

    /**
     * Return an export file path.
     */
    public function getExportFilePath(
        int|string $id,
        string $type
    ): ?string {
        $runRecord = ReconciliationRun::find($id);

        if (!$runRecord) {
            return null;
        }

        if (!$runRecord->output_directory) {
            return null;
        }

        $allowedTypes = [
            'matches' =>
                'reconciliation_matches.csv',

            'unmatched_a' =>
                'reconciliation_unmatched_a.csv',

            'unmatched_b' =>
                'reconciliation_unmatched_b.csv',

            'summary' =>
                'reconciliation_summary.json',
        ];

        if (!isset($allowedTypes[$type])) {
            return null;
        }

        $filePath = Storage::path(
            "{$runRecord->output_directory}/output/" .
            $allowedTypes[$type]
        );

        return File::exists($filePath)
            ? $filePath
            : null;
    }

    /**
     * Convert a CSV file into an associative array.
     */
    private function parseCsvToAssoc(
        string $filepath
    ): array {
        if (!File::exists($filepath)) {
            return [];
        }

        $contents = file(
            $filepath,
            FILE_IGNORE_NEW_LINES
        );

        if (!$contents) {
            return [];
        }

        $rows = array_map(
            'str_getcsv',
            $contents
        );

        if (empty($rows)) {
            return [];
        }

        $header = array_shift($rows);

        if (!$header) {
            return [];
        }

        $data = [];

        foreach ($rows as $row) {
            if (
                count($header) ===
                count($row)
            ) {
                $data[] = array_combine(
                    $header,
                    $row
                );
            }
        }

        return $data;
    }
}
