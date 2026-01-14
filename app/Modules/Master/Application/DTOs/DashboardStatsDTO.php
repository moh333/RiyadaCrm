<?php

namespace App\Modules\Master\Application\DTOs;

use App\Modules\Master\Domain\Model\Revenue;

final class DashboardStatsDTO
{
    public int $usersCount;
    public int $tenantsCount;
    public Revenue $totalRevenue;

    public function __construct(int $usersCount, int $tenantsCount, Revenue $totalRevenue)
    {
        $this->usersCount = $usersCount;
        $this->tenantsCount = $tenantsCount;
        $this->totalRevenue = $totalRevenue;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['users'] ?? 0,
            $data['tenants'] ?? 0,
            new Revenue($data['revenue'] ?? 0.0)
        );
    }

    public function toArray(): array
    {
        return [
            'users' => $this->usersCount,
            'tenants' => $this->tenantsCount,
            'revenue' => (string) $this->totalRevenue,
        ];
    }
}
