<?php

namespace App\Modules\Tenant\Users\Domain;

use DateTimeImmutable;

class User
{
    private function __construct(
        private int $id,
        private string $userName,
        private ?string $firstName,
        private string $lastName,
        private string $email,
        private string $status,
        private bool $isAdmin,
        private ?string $title,
        private ?string $department,
        private ?string $phoneMobile,
        private ?string $phoneWork,
        private ?string $signature,
        private ?string $reportsToId,
        private ?string $addressStreet,
        private ?string $addressCity,
        private ?string $addressState,
        private ?string $addressPostalCode,
        private ?string $addressCountry,
        private ?string $currencyId,
        private ?string $dateFormat,
        private ?string $timeZone,
        private ?string $currencyGroupingSeparator,
        private ?string $roleId,
        private DateTimeImmutable $dateEntered,
        private DateTimeImmutable $dateModified
    ) {
    }

    public static function create(
        int $id,
        string $userName,
        string $firstName,
        string $lastName,
        string $email,
        string $roleId,
        string $status = 'Active',
        bool $isAdmin = false
    ): self {
        return new self(
            $id,
            $userName,
            $firstName,
            $lastName,
            $email,
            $status,
            $isAdmin,
            null,
            null,
            null,
            null,
            null,
            null, // title, dept, mobile, work, sig, reports
            null,
            null,
            null,
            null,
            null, // address
            null,
            null,
            null,
            null, // prefs
            $roleId,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );
    }

    public static function fromDatabase(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['user_name'],
            $data['first_name'] ?? null,
            $data['last_name'],
            $data['email1'],
            $data['status'],
            $data['is_admin'] === 'on' || $data['is_admin'] === 1 || $data['is_admin'] === true,
            $data['title'] ?? null,
            $data['department'] ?? null,
            $data['phone_mobile'] ?? null,
            $data['phone_work'] ?? null,
            $data['signature'] ?? null,
            $data['reports_to_id'] ?? null,
            $data['address_street'] ?? null,
            $data['address_city'] ?? null,
            $data['address_state'] ?? null,
            $data['address_postalcode'] ?? null,
            $data['address_country'] ?? null,
            $data['currency_id'] ?? null,
            $data['date_format'] ?? null,
            $data['time_zone'] ?? null,
            $data['currency_grouping_separator'] ?? null,
            $data['roleid'] ?? null,
            new DateTimeImmutable($data['date_entered'] ?? 'now'),
            new DateTimeImmutable($data['date_modified'] ?? 'now')
        );
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getUserName(): string
    {
        return $this->userName;
    }
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    public function getLastName(): string
    {
        return $this->lastName;
    }
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function getDepartment(): ?string
    {
        return $this->department;
    }
    public function getPhoneMobile(): ?string
    {
        return $this->phoneMobile;
    }
    public function getPhoneWork(): ?string
    {
        return $this->phoneWork;
    }
    public function getSignature(): ?string
    {
        return $this->signature;
    }
    public function getReportsToId(): ?string
    {
        return $this->reportsToId;
    }
    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }
    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }
    public function getAddressState(): ?string
    {
        return $this->addressState;
    }
    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }
    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }
    public function getRoleId(): ?string
    {
        return $this->roleId;
    }
    public function getDateEntered(): DateTimeImmutable
    {
        return $this->dateEntered;
    }
    public function getDateModified(): DateTimeImmutable
    {
        return $this->dateModified;
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }
}
