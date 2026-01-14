<?php

namespace App\Modules\Master\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Master\Application\UseCases\GetDashboardStats;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private GetDashboardStats $getDashboardStats;

    public function __construct(GetDashboardStats $getDashboardStats)
    {
        $this->getDashboardStats = $getDashboardStats;
    }

    public function index()
    {
        $statsDTO = $this->getDashboardStats->execute();

        return view('master::dashboard', ['stats' => $statsDTO->toArray()]);
    }
}
