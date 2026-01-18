<?php

namespace App\Modules\Tenant\Application\DTOs;

final class TenantDashboardDTO
{
    public int $leadsCount;
    public int $opportunitiesCount;
    public int $contractsCount;
    public string $status;

    public function __construct(int $leadsCount, int $opportunitiesCount, int $contractsCount, string $status)
    {
        $this->leadsCount = $leadsCount;
        $this->opportunitiesCount = $opportunitiesCount;
        $this->contractsCount = $contractsCount;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            'leads' => $this->leadsCount,
            'opportunities' => $this->opportunitiesCount,
            'contracts' => $this->contractsCount,
            'status' => $this->status,
        ];
    }
}
