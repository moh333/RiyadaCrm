<?php

namespace App\Modules\Master\Application\UseCases;

use App\Modules\Master\Application\DTOs\TenantDTO;
use App\Modules\Master\Domain\Repositories\TenantManagerRepositoryInterface;

class GetTenantById
{
    public function __construct(
        private TenantManagerRepositoryInterface $repository
    ) {
    }

    public function execute(string $id): ?TenantDTO
    {
        return $this->repository->findById($id);
    }
}
