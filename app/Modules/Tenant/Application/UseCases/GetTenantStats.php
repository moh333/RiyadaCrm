<?php

namespace App\Modules\Tenant\Application\UseCases;

use App\Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use App\Modules\Tenant\Application\DTOs\TenantDashboardDTO;

class GetTenantStats
{
    private TenantRepositoryInterface $repository;

    public function __construct(TenantRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): TenantDashboardDTO
    {
        return $this->repository->getDashboardData();
    }
}
