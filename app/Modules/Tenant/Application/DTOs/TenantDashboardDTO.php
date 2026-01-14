<?php

namespace App\Modules\Tenant\Application\DTOs;

final class TenantDashboardDTO
{
    public int $leadsCount;
    public int $opportunitiesCount;
    public string $status;

    public function __construct(int $leadsCount, int $opportunitiesCount, string $status)
    {
        $this->leadsCount = $leadsCount;
        $this->opportunitiesCount = $opportunitiesCount;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            'leads' => $this->leadsCount,
            'opportunities' => $this->opportunitiesCount,
            'status' => $this->status,
        ];
    }
}
