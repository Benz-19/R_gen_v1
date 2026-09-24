<?php
namespace App\Services\Admin;

use App\Models\ReconciliationRun;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class ReconciliationRunService{

    public function latest_run(): LengthAwarePaginator|array
    {
        try {
            return ReconciliationRun::latest()->paginate(10);
        } catch (Throwable $th) {
            return ['message'=> $th];
        }
    }
}