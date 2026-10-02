<?php
namespace App\Services\Admin;

use App\Models\ReconciliationRun;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class ReconciliationRunService{

    public function latest_run($user_id): LengthAwarePaginator|array
    {
        try {
            return ReconciliationRun::query()->where('executed_by', $user_id)->latest()->paginate(10);
        } catch (Throwable $th) {
            return ['message'=> $th];
        }
    }
}