<?php

namespace App\Modules\Master\Infrastructure\Repositories;

use App\Modules\Master\Domain\Repositories\MasterRepositoryInterface;
use App\Modules\Master\Application\DTOs\DashboardStatsDTO;
use App\Modules\Master\Domain\Model\Revenue;

class EloquentMasterRepository implements MasterRepositoryInterface
{
    public function getStats(): DashboardStatsDTO
    {
        // For DDD, we map the persistence result to our DTO or Domain Model.
        return new DashboardStatsDTO(
            100,
            5,
            new Revenue(50000.00)
        );
    }
}
