<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchSessionController extends Controller
{
    public function switchBranch(Request $request)
    {
        $branchId = $request->get('branch_id', 'all');
        session(['active_branch_id' => $branchId]);

        return redirect()->back()->with('success', 'Active branch context switched successfully.');
    }
}
