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

    public function findById(string $id): ?TenantDTO
    {
        $tenant = Tenant::with('domains')->find($id);
        return $tenant ? TenantDTO::fromModel($tenant) : null;
    }

    public function create(array $data): void
    {
        $tenant = Tenant::create(['id' => $data['id']]);
        $tenant->domains()->create(['domain' => $data['domain']]);
    }

    public function update(string $id, array $data): void
    {
        $tenant = Tenant::findOrFail($id);

        if (isset($data['domain'])) {
            // Update the first domain or all domains depending on logic
            // For now, let's assume we update the first one or manage it
            $tenant->domains()->delete();
            $tenant->domains()->create(['domain' => $data['domain']]);
        }

        if (isset($data['id']) && $data['id'] !== $id) {
            // Changing ID in stancl/tenancy is tricky, usually not recommended
            // but if needed: $tenant->update(['id' => $data['id']]);
        }
    }

    public function delete(string $id): void
    {
        $tenant = Tenant::find($id);
        if ($tenant) {
            $tenant->delete(); // This triggers deletion events
        }
    }
}
