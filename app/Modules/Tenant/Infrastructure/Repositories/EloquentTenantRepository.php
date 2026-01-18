<?php

namespace App\Modules\Tenant\Infrastructure\Repositories;

use App\Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use App\Modules\Tenant\Application\DTOs\TenantDashboardDTO;
use Illuminate\Support\Facades\DB;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function getDashboardData(): TenantDashboardDTO
    {
        $contractsCount = DB::connection('tenant')->table('vtiger_servicecontracts')
            ->join('vtiger_crmentity', 'vtiger_crmentity.crmid', '=', 'vtiger_servicecontracts.servicecontractsid')
            ->where('vtiger_crmentity.deleted', 0)
            ->count();

        return new TenantDashboardDTO(
            25,
            12,
            $contractsCount,
            'Active'
        );
    }
}
