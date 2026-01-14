<?php

namespace App\Modules\Master\Application\UseCases;

use App\Modules\Master\Domain\Repositories\TenantManagerRepositoryInterface;

class CreateTenant
{
    private TenantManagerRepositoryInterface $repository;

    public function __construct(TenantManagerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $id, string $domain): void
    {
        $this->repository->create(['id' => $id, 'domain' => $domain]);
    }
}
