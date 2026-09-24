<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReconciliationRunService;
use Illuminate\Http\Request;

class AdminReconciliationController extends Controller{

    public function index(Request $request){

        $reconciliation_run_service = new ReconciliationRunService();
        $runs = $reconciliation_run_service->latest_run();
        return view('admin.reconciliation_runs', compact('runs'));
    }

    public function trigger_run(Request $request){
        return view('admin.trigger_run');
    }
}