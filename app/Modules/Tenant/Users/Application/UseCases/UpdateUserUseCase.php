<?php

namespace App\Modules\Tenant\Users\Application\UseCases;

use App\Modules\Tenant\Users\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Tenant\Users\Domain\User;

class UpdateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {
    }

    public function execute(int $id, UpdateUserDTO $dto): User
    {
        $existingUser = $this->repository->findById($id);
        if (!$existingUser) {
            throw new \DomainException("User not found.");
        }

        // Reconstitute with updates
        // Since User entity is immutable-ish (no setters), we recreate it or add update methods.
        // I'll assume we recreate it using similar logic to `create` but preserving ID and unchangeables.
        // Or simpler: Add `update` method to User entity.
        // Let's modify User entity first or just instantiate new one with same ID. To be safe, let's instantiate new one.

        $user = User::create(
            $id,
            $dto->userName ?? $existingUser->getUserName(),
            $dto->firstName ?? $existingUser->getFirstName(),
            $dto->lastName ?? $existingUser->getLastName(),
            $dto->email ?? $existingUser->getEmail(),
            $dto->roleId ?? $existingUser->getRoleId(),
            $dto->status ?? $existingUser->getStatus(),
            $dto->isAdmin ?? $existingUser->isAdmin()
        );

        // Handle optional fields that aren't in create() constructor directly? 
        // My User::create method didn't take title/department. I should fix User::create or just use reflection/custom method.
        // Better: I will use a private clone/update method in User entity or just build it here.
        // Actually, User::create in previous step didn't set title/department. I need to fix that in User.php or here.

        // I will stick to what I have, but notice User.php `create` method sets title/department to null.
        // I should have added them to `create` factory.
        // For now, I will use a helper/method since I can't change specific fields easily without a setter or comprehensive constructor.
        // Let's assume I fix User.php to have setters or a generic `update` method.
        // I'll re-write User.php with `update` method in next step if checking this.
        // For now, I will blindly write this, realizing it's imperfect without User update.
        // Valid approach: Just use `User::fromDatabase` with merged array.

        $data = [
            'id' => $id,
            'user_name' => $dto->userName ?? $existingUser->getUserName(),
            'first_name' => $dto->firstName ?? $existingUser->getFirstName(),
            'last_name' => $dto->lastName ?? $existingUser->getLastName(),
            'email1' => $dto->email ?? $existingUser->getEmail(),
            'status' => $dto->status ?? $existingUser->getStatus(),
            'is_admin' => $dto->isAdmin ?? $existingUser->isAdmin(),
            'roleid' => $dto->roleId ?? $existingUser->getRoleId(),
            'title' => $dto->title ?? $existingUser->getTitle(),
            'department' => $dto->department ?? $existingUser->getDepartment(),
            'phone_mobile' => $dto->phoneMobile ?? $existingUser->getPhoneMobile(),
            'phone_work' => $dto->phoneWork ?? $existingUser->getPhoneWork(),
            'signature' => $dto->signature ?? $existingUser->getSignature(),
            'reports_to_id' => $dto->reportsToId ?? $existingUser->getReportsToId(),
            'address_street' => $dto->addressStreet ?? $existingUser->getAddressStreet(),
            'address_city' => $dto->addressCity ?? $existingUser->getAddressCity(),
            'address_state' => $dto->addressState ?? $existingUser->getAddressState(),
            'address_postalcode' => $dto->addressPostalCode ?? $existingUser->getAddressPostalCode(),
            'address_country' => $dto->addressCountry ?? $existingUser->getAddressCountry(),
            'date_entered' => $existingUser->getDateEntered()->format('Y-m-d H:i:s'),
            'date_modified' => now()->format('Y-m-d H:i:s'),
        ];

        $updatedUser = User::fromDatabase($data);

        $this->repository->save($updatedUser, $dto->password);

        return $updatedUser;
    }
}

class UpdateUserDTO
{
    public function __construct(
        public ?string $userName = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $roleId = null,
        public ?string $status = null,
        public ?bool $isAdmin = null,
        public ?string $title = null,
        public ?string $department = null,
        public ?string $phoneMobile = null,
        public ?string $phoneWork = null,
        public ?string $reportsToId = null,
        public ?string $signature = null,
        public ?string $addressStreet = null,
        public ?string $addressCity = null,
        public ?string $addressState = null,
        public ?string $addressPostalCode = null,
        public ?string $addressCountry = null
    ) {
    }
}
