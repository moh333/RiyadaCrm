<?php

namespace App\Modules\Master\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('vtiger_loginhistory');

            return DataTables::query($query)
                ->editColumn('status', function ($row) {
                    return $row->status;
                })
                ->editColumn('login_time', function ($row) {
                    return $row->login_time;
                })
                ->editColumn('logout_time', function ($row) {
                    return $row->logout_time ?: '-';
                })
                ->make(true);
        }

        return view('master::login_history.index');
    }
}
