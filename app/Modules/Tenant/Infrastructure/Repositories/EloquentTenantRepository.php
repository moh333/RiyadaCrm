<?php

namespace App\Modules\Tenant\Infrastructure\Repositories;

use App\Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use App\Modules\Tenant\Application\DTOs\TenantDashboardDTO;
use Illuminate\Support\Facades\DB;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function getDashboardData(): TenantDashboardDTO
    {
        // Isolated logic. interacting with tenantdb or vtiger tables
        // Example: DB::connection('tenantdb')->table('leads')->count();

        return new TenantDashboardDTO(
            25,
            12,
            'Active'
        );
    }
}
