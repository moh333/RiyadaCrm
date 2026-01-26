<?php

namespace App\Modules\Tenant\Users\Application\UseCases;

use App\Modules\Tenant\Users\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Tenant\Users\Domain\User;

class CreateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {
    }

    public function execute(CreateUserDTO $dto): User
    {
        // Domain validation: check email uniqueness if needed (repo check)
        if ($this->repository->findByEmail($dto->email)) {
            throw new \DomainException("Email already exists.");
        }

        $id = $this->repository->nextIdentity();

        $user = User::fromDatabase([
            'id' => $id,
            'user_name' => $dto->userName,
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'email1' => $dto->email,
            'roleid' => $dto->roleId,
            'status' => $dto->status,
            'is_admin' => $dto->isAdmin,
            'title' => $dto->title,
            'department' => $dto->department,
            'phone_mobile' => $dto->phoneMobile,
            'phone_work' => $dto->phoneWork,
            'signature' => $dto->signature,
            'reports_to_id' => $dto->reportsToId,
            'address_street' => $dto->addressStreet,
            'address_city' => $dto->addressCity,
            'address_state' => $dto->addressState,
            'address_postalcode' => $dto->addressPostalCode,
            'address_country' => $dto->addressCountry,
            'date_entered' => now(),
            'date_modified' => now()
        ]);

        // Save handles password hashing logic
        $this->repository->save($user, $dto->password);

        return $user;
    }
}

class CreateUserDTO
{
    public function __construct(
        public string $userName,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public string $roleId,
        public string $status = 'Active',
        public bool $isAdmin = false,
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
