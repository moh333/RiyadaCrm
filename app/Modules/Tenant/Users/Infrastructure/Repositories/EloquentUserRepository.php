<?php

namespace App\Modules\Tenant\Users\Infrastructure\Repositories;

use App\Modules\Tenant\Users\Domain\User;
use App\Modules\Tenant\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $data = DB::connection('tenant')->table('vtiger_users')
            ->leftJoin('vtiger_user2role', 'vtiger_users.id', '=', 'vtiger_user2role.userid')
            ->where('vtiger_users.id', $id)
            ->where('vtiger_users.deleted', 0)
            ->select('vtiger_users.*', 'vtiger_user2role.roleid')
            ->first();

        if (!$data)
            return null;

        return User::fromDatabase((array) $data);
    }

    public function findByEmail(string $email): ?User
    {
        $data = DB::connection('tenant')->table('vtiger_users')
            ->leftJoin('vtiger_user2role', 'vtiger_users.id', '=', 'vtiger_user2role.userid')
            ->where('vtiger_users.email1', $email)
            ->where('vtiger_users.deleted', 0)
            ->select('vtiger_users.*', 'vtiger_user2role.roleid')
            ->first();

        if (!$data)
            return null;

        return User::fromDatabase((array) $data);
    }

    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::connection('tenant')->table('vtiger_users')
            ->leftJoin('vtiger_user2role', 'vtiger_users.id', '=', 'vtiger_user2role.userid')
            ->where('vtiger_users.deleted', 0)
            ->select('vtiger_users.*', 'vtiger_user2role.roleid')
            ->orderBy('date_entered', 'desc');

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function ($row) {
            return User::fromDatabase((array) $row);
        });

        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        $dbQuery = DB::connection('tenant')->table('vtiger_users')
            ->leftJoin('vtiger_user2role', 'vtiger_users.id', '=', 'vtiger_user2role.userid')
            ->where('vtiger_users.deleted', 0)
            ->where(function ($q) use ($query) {
                $q->where('user_name', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email1', 'like', "%{$query}%");
            })
            ->select('vtiger_users.*', 'vtiger_user2role.roleid')
            ->orderBy('date_entered', 'desc');

        $paginator = $dbQuery->paginate($perPage);

        $items = collect($paginator->items())->map(function ($row) {
            return User::fromDatabase((array) $row);
        });

        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }

    public function nextIdentity(): int
    {
        // vtiger_users_seq
        $seqTable = 'vtiger_users_seq';
        if (!DB::connection('tenant')->getSchemaBuilder()->hasTable($seqTable)) {
            // Fallback if seq table missing, though it should exist
            return DB::connection('tenant')->table('vtiger_users')->max('id') + 1;
        }

        $id = DB::connection('tenant')->table($seqTable)->max('id');
        $nextId = $id + 1;

        // Update sequence
        DB::connection('tenant')->table($seqTable)->update(['id' => $nextId]);

        return $nextId;
    }

    public function save(User $user, string $password = null): void
    {
        DB::connection('tenant')->transaction(function () use ($user, $password) {
            $exists = DB::connection('tenant')->table('vtiger_users')
                ->where('id', $user->getId())
                ->exists();

            $data = [
                'user_name' => $user->getUserName(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email1' => $user->getEmail(),
                'status' => $user->getStatus(),
                'is_admin' => $user->isAdmin() ? 'on' : 'off',
                'title' => $user->getTitle(),
                'department' => $user->getDepartment(),
                'phone_mobile' => $user->getPhoneMobile(),
                'phone_work' => $user->getPhoneWork(),
                'signature' => $user->getSignature(),
                'reports_to_id' => $user->getReportsToId(),
                'address_street' => $user->getAddressStreet(),
                'address_city' => $user->getAddressCity(),
                'address_state' => $user->getAddressState(),
                'address_postalcode' => $user->getAddressPostalCode(),
                'address_country' => $user->getAddressCountry(),
                // Preferences (assuming columns exist in vtiger_users as per legacy standard, or stored in user_preferences json if modern. Spec implies fields.)
                'currency_id' => 1, // Defaulting if not in entity yet, but should be passed.
                'date_format' => 'yyyy-mm-dd', // Defaults
                'time_zone' => 'UTC',
                'date_modified' => now(),
            ];

            if ($password) {
                // Legacy vtiger often uses MD5 for user_password. 
                // We will use Bcrypt for Laravel compatibility if configured, 
                // BUT if we want to support existing vtiger, we might need MD5 or specific hash.
                // Assuming standard Laravel App for now as requested.
                $data['user_password'] = Hash::make($password);
            }

            if ($exists) {
                DB::connection('tenant')->table('vtiger_users')
                    ->where('id', $user->getId())
                    ->update($data);
            } else {
                $data['id'] = $user->getId();
                $data['date_entered'] = now();
                $data['deleted'] = 0;
                // Defaults for new user
                $data['crypt_type'] = 'bf'; // standard vtiger
                DB::connection('tenant')->table('vtiger_users')->insert($data);
            }

            // Role assignment
            if ($user->getRoleId()) {
                DB::connection('tenant')->table('vtiger_user2role')->updateOrInsert(
                    ['userid' => $user->getId()],
                    ['roleid' => $user->getRoleId()]
                );
            }
        });
    }

    public function delete(int $id): void
    {
        DB::connection('tenant')->table('vtiger_users')
            ->where('id', $id)
            ->update(['deleted' => 1, 'status' => 'Inactive']);
    }

    public function getDataTableQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::connection('tenant')
            ->table('vtiger_users')
            ->leftJoin('vtiger_user2role', 'vtiger_users.id', '=', 'vtiger_user2role.userid')
            ->leftJoin('vtiger_role', 'vtiger_user2role.roleid', '=', 'vtiger_role.roleid')
            ->where('vtiger_users.deleted', 0)
            ->select([
                'vtiger_users.id',
                'vtiger_users.user_name',
                'vtiger_users.first_name',
                'vtiger_users.last_name',
                'vtiger_users.email1',
                'vtiger_users.status',
                'vtiger_users.is_admin',
                'vtiger_users.title',
                'vtiger_role.rolename'
            ]);
    }
}
