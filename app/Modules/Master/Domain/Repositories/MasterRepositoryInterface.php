<?php

namespace App\Modules\Master\Domain\Repositories;

use App\Modules\Master\Application\DTOs\DashboardStatsDTO;

interface MasterRepositoryInterface
{
    public function getStats(): DashboardStatsDTO;
}
