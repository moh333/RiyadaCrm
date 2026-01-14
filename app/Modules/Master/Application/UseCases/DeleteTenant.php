<?php

namespace App\Modules\Master\Application\UseCases;

use App\Modules\Master\Domain\Repositories\TenantManagerRepositoryInterface;

class DeleteTenant
{
    private TenantManagerRepositoryInterface $repository;

    public function __construct(TenantManagerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $id): void
    {
        $this->repository->delete($id);
    }
}
