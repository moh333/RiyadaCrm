<?php

namespace App\Modules\Tenant\Domain\Repositories;

use App\Modules\Tenant\Application\DTOs\TenantDashboardDTO;

interface TenantRepositoryInterface
{
    public function getDashboardData(): TenantDashboardDTO;
}
