<?php

namespace App\Modules\Tenant\Users\Domain\Repositories;

use App\Modules\Tenant\Users\Domain\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function paginated(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
    public function search(string $query, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
    public function save(User $user, string $password = null): void; // Password optional on update
    public function delete(int $id): void;
    public function nextIdentity(): int;
    public function getDataTableQuery(): \Illuminate\Database\Query\Builder;
}
