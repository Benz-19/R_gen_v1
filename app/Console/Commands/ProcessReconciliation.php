<?php

namespace App\Console\Commands;

use App\Models\ReconciliationRun;
use App\Services\ReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessReconciliation extends Command
{
    protected $signature =
        'reconciliation:process {runId}';

    protected $description =
        'Process a reconciliation run outside the HTTP request';

    public function handle(
        ReconciliationService $reconciliationService
    ): int {

        $runId = $this->argument('runId');

        $this->info(
            "Starting reconciliation run {$runId}"
        );

        Log::info(
            'RECON CLI: command started',
            [
                'run_id' => $runId,
            ]
        );

        $run = ReconciliationRun::find(
            $runId
        );

        if (!$run) {

            $this->error(
                "Run {$runId} does not exist."
            );

            Log::error(
                'RECON CLI: run not found',
                [
                    'run_id' => $runId,
                ]
            );

            return self::FAILURE;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | Find process.json
            |--------------------------------------------------------------------------
            */

            $processJsonPath =
                storage_path(
                    "app/reconciliation_runs/{$run->id}/process.json"
                );

            Log::info(
                'RECON CLI: looking for process.json',
                [
                    'path' => $processJsonPath,
                ]
            );

            if (!File::exists(
                $processJsonPath
            )) {
                throw new \RuntimeException(
                    "process.json not found: {$processJsonPath}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Read configuration
            |--------------------------------------------------------------------------
            */

            $payload = json_decode(
                File::get(
                    $processJsonPath
                ),
                true
            );

            if (!is_array($payload)) {
                throw new \RuntimeException(
                    'process.json contains invalid JSON.'
                );
            }

            $data =
                $payload['data'] ?? [];

            $storedPathA =
                $payload['stored_path_a'] ?? null;

            $storedPathB =
                $payload['stored_path_b'] ?? null;

            $userId =
                $payload['user_id']
                ?? $run->user_id;

            $workspace_id =
                $payload['workspace_id']
                ?? $run->user_id;

            if (!$storedPathA) {
                throw new \RuntimeException(
                    'Source A path is missing.'
                );
            }

            if (!$storedPathB) {
                throw new \RuntimeException(
                    'Source B path is missing.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Mark processing
            |--------------------------------------------------------------------------
            */

            $run->update([
                'status' => 'processing',
            ]);

            $run->refresh();

            Log::info(
                'RECON CLI: run marked processing',
                [
                    'run_id' => $run->id,
                ]
            );

            $this->info(
                "Run {$run->id} is processing."
            );

            /*
            |--------------------------------------------------------------------------
            | Execute reconciliation
            |--------------------------------------------------------------------------
            */

            $result =
                $reconciliationService
                    ->executeRunFromPaths(
                        $data,
                        $storedPathA,
                        $storedPathB,
                        $userId,
                        $run,
                        $workspace_id
                    );

            /*
            |--------------------------------------------------------------------------
            | Remove process.json
            |--------------------------------------------------------------------------
            */

            if (File::exists(
                $processJsonPath
            )) {
                File::delete(
                    $processJsonPath
                );
            }

            Log::info(
                'RECON CLI: reconciliation completed',
                [
                    'run_id' => $run->id,
                    'status' => $result->status,
                ]
            );

            $this->info(
                "Run {$run->id} completed."
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {

            Log::error(
                'RECON CLI: reconciliation failed',
                [
                    'run_id' => $run->id,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            try {

                $run->update([
                    'status' => 'failed',
                    'error_message' =>
                        $e->getMessage(),
                ]);

            } catch (\Throwable $updateException) {

                Log::error(
                    'RECON CLI: failed to update run',
                    [
                        'run_id' => $run->id,
                        'message' =>
                            $updateException->getMessage(),
                    ]
                );
            }

            $this->error(
                "Run {$run->id} failed: " .
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}