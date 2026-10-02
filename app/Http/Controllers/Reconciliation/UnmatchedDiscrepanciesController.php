<?php
namespace App\Http\Controllers\Reconciliation;

use App\Http\Controllers\Admin\AdminUnmatchedDiscrepancies;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\EmployeeUnmatchedDiscrepancies;
use Illuminate\Http\Request;

class UnmatchedDiscrepanciesController extends Controller{

    public function index(Request $request)
    {
        
        if ($request->user()->userDetail->is_admin) {
            return app(AdminUnmatchedDiscrepancies::class)
                ->index($request);
        }

        return app(EmployeeUnmatchedDiscrepancies::class)
            ->index($request);
    }
}