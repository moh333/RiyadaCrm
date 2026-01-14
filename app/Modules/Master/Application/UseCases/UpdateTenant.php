<?php

namespace App\Modules\Master\Application\UseCases;

use App\Modules\Master\Domain\Repositories\TenantManagerRepositoryInterface;

class UpdateTenant
{
    public function __construct(
        private TenantManagerRepositoryInterface $repository
    ) {
    }

    public function execute(string $id, string $domain): void
    {
        $this->repository->update($id, [
            'domain' => $domain
        ]);
    }
}
