<?php

namespace App\Http\Controllers\Reconciliation;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Workspace\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReconciliationController extends Controller
{

    private function getReconciliationTotalExceptions(){
        $runs = ReconciliationRun::query()->latest()->paginate(20);
        $exceptions = 0;
        foreach($runs as $run){
            if(!empty($run->total_exceptions) && $run->total_exceptions >0){
                $exceptions+=1;
            }
        }

        return $exceptions;
    }

    public function index()
    {
        $runs = ReconciliationRun::query()
            ->latest()
            ->paginate(20);

        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions();    

        return view('admin.reconciliation-runs',compact('runs', 'total_unmatched_discrepancies'));
    }

    public function create()
    {
        return view('reconciliation.trigger_run');
    }

    public function execute(Request $request)
    {
        Log::info('RECON EXECUTE: request reached controller');

        try {
            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */

            $executedBy = $request->session()->get('user_id');

            Log::info(
                'RECON EXECUTE: authenticated user',
                [
                    'executed_by' => $executedBy,
                ]
            );

            if (!$executedBy) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You must be authenticated.',
                ], 401);
            }

            /*
            |--------------------------------------------------------------------------
            | Validation
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([
                'file_source_a' => [
                    'required',
                    'file',
                    'max:25600',
                ],

                'file_source_b' => [
                    'required',
                    'file',
                    'max:25600',
                ],

                'source_a_connection' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'source_b_connection' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'target_start_date' => [
                    'required',
                    'date',
                ],

                'target_end_date' => [
                    'required',
                    'date',
                ],

                'amount_tolerance' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'date_window_days' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'ml_threshold' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'ml_modules' => [
                    'nullable',
                    'array',
                ],

                'ml_modules.*' => [
                    'string',
                ],
            ]);

            Log::info(
                'RECON EXECUTE: validation passed'
            );

            /*
            |--------------------------------------------------------------------------
            | Uploaded files
            |--------------------------------------------------------------------------
            */

            $fileA = $request->file(
                'file_source_a'
            );

            $fileB = $request->file(
                'file_source_b'
            );

            if (!$fileA || !$fileB) {
                throw new \RuntimeException(
                    'One or both uploaded files are missing.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create database run
            |--------------------------------------------------------------------------
            */

            $runIdentifier =
                now()->format('Ymd_His') .
                '_' .
                Str::random(8);

            $run = new ReconciliationRun();

            $run->executed_by = $executedBy;
            $run->status = 'queued';
            $run->run_identifier = $runIdentifier;

            $run->save();

            Log::info(
                'RECON EXECUTE: DATABASE RUN CREATED',
                [
                    'run_id' => $run->id,
                    'executed_by' => $executedBy,
                    'status' => $run->status,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Create storage directory
            |--------------------------------------------------------------------------
            */

            $runDirectory =
                "reconciliation_runs/{$run->id}";

            Storage::makeDirectory(
                $runDirectory
            );

            /*
            |--------------------------------------------------------------------------
            | Store Source A
            |--------------------------------------------------------------------------
            */

            $storedPathA = $fileA->storeAs(
                $runDirectory,
                'source_a.csv'
            );

            if (!$storedPathA) {
                throw new \RuntimeException(
                    'Failed to store Source A.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Store Source B
            |--------------------------------------------------------------------------
            */

            $storedPathB = $fileB->storeAs(
                $runDirectory,
                'source_b.csv'
            );

            if (!$storedPathB) {
                throw new \RuntimeException(
                    'Failed to store Source B.'
                );
            }

            Log::info(
                'RECON EXECUTE: source files stored',
                [
                    'source_a' => $storedPathA,
                    'source_b' => $storedPathB,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Capture original filenames
            |--------------------------------------------------------------------------
            */

            $validated['source_a_filename'] =
                $fileA->getClientOriginalName();

            $validated['source_b_filename'] =
                $fileB->getClientOriginalName();

            /*
            |--------------------------------------------------------------------------
            | Remove UploadedFile objects
            |--------------------------------------------------------------------------
            */

            unset(
                $validated['file_source_a'],
                $validated['file_source_b']
            );

            /*
            |--------------------------------------------------------------------------
            | Save process configuration
            |--------------------------------------------------------------------------
            */
            $user_id = $request->session()->get('user_id');
             $workspace_id = (new WorkspaceService)
            ->get_workspace_id($user_id);
            
            $processData = [
                'data' => $validated,
                'stored_path_a' => $storedPathA,
                'stored_path_b' => $storedPathB,
                'executed_by' => $executedBy,
                'run_id' => $run->id,
                'user_id' => $user_id,
                'workspace_id'=> $workspace_id,
            ];

            $processJsonPath =
                storage_path(
                    "app/{$runDirectory}/process.json"
                );

            File::ensureDirectoryExists(
                dirname($processJsonPath)
            );

            File::put(
                $processJsonPath,
                json_encode(
                    $processData,
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES |
                    JSON_UNESCAPED_UNICODE
                )
            );

            if (!File::exists($processJsonPath)) {
                throw new \RuntimeException(
                    'process.json was not created.'
                );
            }

            Log::info(
                'RECON EXECUTE: process.json created',
                [
                    'path' => $processJsonPath,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Locate CLI PHP
            |--------------------------------------------------------------------------
            */

            $phpBinary = 'php';

            $artisanPath = base_path(
                'artisan'
            );

            /*
            |--------------------------------------------------------------------------
            | Paths
            |--------------------------------------------------------------------------
            */

            $logPath =
                storage_path(
                    "app/{$runDirectory}/process.log"
                );

            $batPath =
                storage_path(
                    "app/{$runDirectory}/run_process.bat"
                );

            /*
            |--------------------------------------------------------------------------
            | Create BAT file
            |--------------------------------------------------------------------------
            */

            $batContents = "@echo off\r\n";

            $batContents .=
                "cd /d \"" .
                base_path() .
                "\"\r\n";

            $batContents .=
                "echo RECON PROCESS STARTED > \"" .
                $logPath .
                "\"\r\n";

            $batContents .=
                "echo PHP: " .
                $phpBinary .
                " >> \"" .
                $logPath .
                "\"\r\n";

            $batContents .=
                "echo RUN ID: " .
                $run->id .
                " >> \"" .
                $logPath .
                "\"\r\n";

            $batContents .=
                "\"" .
                $phpBinary .
                "\" \"" .
                $artisanPath .
                "\" reconciliation:process " .
                $run->id .
                " >> \"" .
                $logPath .
                "\" 2>&1\r\n";

            $batContents .=
                "echo RECON PROCESS FINISHED WITH ERRORLEVEL %ERRORLEVEL% >> \"" .
                $logPath .
                "\"\r\n";

            $batContents .=
                "exit /b %ERRORLEVEL%\r\n";

            File::put(
                $batPath,
                $batContents
            );

            if (!File::exists($batPath)) {
                throw new \RuntimeException(
                    'run_process.bat was not created.'
                );
            }

            Log::info(
                'RECON EXECUTE: BAT created',
                [
                    'bat_path' => $batPath,
                    'log_path' => $logPath,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Launch BAT through CMD
            |--------------------------------------------------------------------------
            */

            $cmdExe =
                getenv('COMSPEC')
                ?: 'C:\\Windows\\System32\\cmd.exe';

            $launchCommand =
                '"' .
                $cmdExe .
                '" /c start "" /B "' .
                $batPath .
                '"';

            Log::info(
                'RECON EXECUTE: launching detached process',
                [
                    'command' => $launchCommand,
                    'php_binary' => $phpBinary,
                    'bat_path' => $batPath,
                ]
            );

            $handle = popen(
                $launchCommand,
                'r'
            );

            if ($handle === false) {
                throw new \RuntimeException(
                    'Windows could not launch the reconciliation process.'
                );
            }

            pclose($handle);

            Log::info(
                'RECON EXECUTE: detached process launched',
                [
                    'run_id' => $run->id,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Return immediately
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => 'queued',
                'run_id' => $run->id,
                'run_identifier' =>
                    $run->run_identifier,
                'message' =>
                    'Reconciliation started successfully.',
            ], 202);

        } catch (\Throwable $e) {

            Log::error(
                'RECON EXECUTE: FAILED',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            if (
                isset($run) &&
                $run instanceof ReconciliationRun
            ) {
                try {
                    $run->update([
                        'status' => 'failed',
                        'error_message' =>
                            $e->getMessage(),
                    ]);
                } catch (\Throwable $updateException) {
                    Log::error(
                        'RECON EXECUTE: could not mark run failed',
                        [
                            'run_id' => $run->id,
                            'message' =>
                                $updateException->getMessage(),
                        ]
                    );
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkStatus(
        ReconciliationRun $run
    ) {
        return response()->json([
            'run_id' => $run->id,
            'status' =>
                strtolower((string) $run->status),
            'run_identifier' =>
                $run->run_identifier,
            'error_message' =>
                $run->error_message,
        ]);
    }

   public function getResults(
    ReconciliationRun $run
) {
    $status = strtolower(
        (string) $run->status
    );

    if ($status !== 'completed') {
        return response()->json([
            'status' => $status,
            'message' =>
                'Reconciliation has not completed yet.',
        ], 409);
    }

    $summary = $run->summary_data;

    if (is_string($summary)) {
        $summary = json_decode(
            $summary,
            true
        );
    }

    $summary = is_array($summary)
        ? $summary
        : [];

    /*
    |--------------------------------------------------------------------------
    | ML Match Breakdown
    |--------------------------------------------------------------------------
    */

    $fuzzyMatches =
        $summary['fuzzy_matches']
        ?? $summary['match_types']['FUZZY']
        ?? $summary['fuzzy_matches_count']
        ?? $summary['levenshtein_matches']
        ?? $summary['fuzzy_count']
        ?? 0;

    $gatewayFeeDeductions =
        $summary['gateway_fee_deductions']
        ?? $summary['match_types']['GATEWAY_FEE']
        ?? $summary['fee_deductions']
        ?? $summary['fee_matches']
        ?? $summary['fee_match_count']
        ?? $summary['gateway_matches']
        ?? $summary['gateway_match_count']
        ?? 0;

    $splitPayments =
        $summary['split_payments']
        ?? $summary['match_types']['SPLIT_PAYMENT']
        ?? $summary['split_matches']
        ?? $summary['n_to_one_matches']
        ?? $summary['split_count']
        ?? 0;

    return response()->json([
        'status' => 'completed',
        'run_id' => $run->id,
        'run_identifier' => $run->run_identifier,

        'match_rate' => $run->match_rate,
        'matched_count' => $run->matched_count,

        'unmatched_a_count' => $run->unmatched_a_count,
        'unmatched_b_count' => $run->unmatched_b_count,

        'summary' => $summary,

        /*
        |--------------------------------------------------------------------------
        | Explicit ML breakdown values
        |--------------------------------------------------------------------------
        */

        'fuzzy_matches' => $fuzzyMatches,
        'gateway_fee_deductions' => $gatewayFeeDeductions,
        'split_payments' => $splitPayments,

        'unmatched_a' =>
            $run->unmatched_a_rows ?? [],

        'unmatched_b' =>
            $run->unmatched_b_rows ?? [],
    ]);
}

    public function exportFile(
        Request $request,
        ReconciliationRun $run
    ) {
        $type = $request->query(
            'type',
            'matches'
        );

        $service = app(
            \App\Services\ReconciliationService::class
        );

        $filePath =
            $service->getExportFilePath(
                $run->id,
                $type
            );

        if (
            !$filePath ||
            !File::exists($filePath)
        ) {
            abort(
                404,
                'Export file not found.'
            );
        }

        return response()->download(
            $filePath
        );
    }
}
