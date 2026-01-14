<?php

namespace App\Modules\Master\Domain\Repositories;

use App\Modules\Master\Application\DTOs\TenantDTO;

interface TenantManagerRepositoryInterface
{
    public function getAll(): array;
    public function findById(string $id): ?TenantDTO;
    public function create(array $data): void;
    public function update(string $id, array $data): void;
    public function delete(string $id): void;
}
