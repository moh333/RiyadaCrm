<?php

namespace App\Modules\Master\Application\UseCases;

use App\Modules\Master\Application\DTOs\DashboardStatsDTO;
use App\Modules\Master\Domain\Repositories\MasterRepositoryInterface;

class GetDashboardStats
{
    private MasterRepositoryInterface $repository;

    public function __construct(MasterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): DashboardStatsDTO
    {
        return $this->repository->getStats();
    }
}
