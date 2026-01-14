<?php

namespace App\Modules\Master\Infrastructure\Repositories;

use App\Models\Central\Tenant;
use App\Modules\Master\Application\DTOs\TenantDTO;
use App\Modules\Master\Domain\Repositories\TenantManagerRepositoryInterface;

class EloquentTenantManagerRepository implements TenantManagerRepositoryInterface
{
    public function getAll(): array
    {
        return Tenant::with('domains')->get()
            ->map(fn($tenant) => TenantDTO::fromModel($tenant))
            ->toArray();
    }

    public function create(array $data): void
    {
        $tenant = Tenant::create(['id' => $data['id']]);
        $tenant->domains()->create(['domain' => $data['domain']]);
    }

    public function update(string $id, array $data): void
    {
        // For simplicity, mostly updating domain or extra ID logic
        // Implementation depends on exact tenant model needs
    }

    public function delete(string $id): void
    {
        $tenant = Tenant::find($id);
        if ($tenant) {
            $tenant->delete(); // This triggers deletion events
        }
    }
}
