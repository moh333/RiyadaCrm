<?php

namespace App\Modules\Tenant\Users\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::connection('tenant')->table('vtiger_loginhistory');

            return \Yajra\DataTables\Facades\DataTables::query($query)
                ->editColumn('status', function ($row) {
                    if ($row->status === 'Signed in') {
                        return __('tenant::users.signed_in');
                    } elseif ($row->status === 'Signed off') {
                        return __('tenant::users.signed_off');
                    }
                    return $row->status;
                })
                ->editColumn('login_time', function ($row) {
                    return $row->login_time;
                })
                ->make(true);
        }

        return view('tenant::login_history.index');
    }
}
