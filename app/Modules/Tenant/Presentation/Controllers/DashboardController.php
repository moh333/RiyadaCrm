<?php

namespace App\Modules\Tenant\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Application\UseCases\GetTenantStats;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private GetTenantStats $getTenantStats;

    public function __construct(GetTenantStats $getTenantStats)
    {
        $this->getTenantStats = $getTenantStats;
    }

    public function index()
    {
        $dto = $this->getTenantStats->execute();

        return view('tenant::dashboard', ['data' => $dto->toArray()]);
    }
}
