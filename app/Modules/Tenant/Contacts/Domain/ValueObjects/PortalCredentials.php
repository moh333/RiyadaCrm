<?php

namespace App\Modules\Tenant\Contacts\Domain\ValueObjects;

/**
 * PortalCredentials Value Object
 * 
 * Represents customer portal login credentials.
 * 
 * Business Rules:
 * - Portal username MUST equal contact's email address
 * - Password is encrypted using vtiger's encryption method
 * - isactive flag controls portal access
 * 
 * Database: Maps to vtiger_portalinfo (user_name, user_password, cryptmode, type, isactive)
 */
final class PortalCredentials
{
    private string $username;
    private string $encryptedPassword;
    private string $cryptMode;
    private bool $isActive;
    private ?\DateTimeImmutable $lastLoginTime;

    public const CRYPT_MODE = 'CRYPT';
    public const TYPE_CONTACT = 'C';

    private function __construct(
        string $username,
        string $encryptedPassword,
        bool $isActive = true,
        string $cryptMode = self::CRYPT_MODE
    ) {
        $this->username = $username;
        $this->encryptedPassword = $encryptedPassword;
        $this->isActive = $isActive;
        $this->cryptMode = $cryptMode;
        $this->lastLoginTime = null;
    }

    /**
     * Create new portal credentials
     * 
     * Business Rule: Username must match contact's email
     */
    public static function create(string $email, string $plainPassword): self
    {
        // Password encryption should use vtiger's Vtiger_Functions::generateEncryptedPassword()
        // This is a placeholder - actual encryption happens in infrastructure layer
        $encryptedPassword = self::encryptPassword($plainPassword);

        return new self($email, $encryptedPassword, true);
    }

    /**
     * Generate random password for portal user
     * Uses vtiger's makeRandomPassword() function
     */
    public static function generateRandomPassword(): string
    {
        // Placeholder - actual implementation uses vtiger's makeRandomPassword()
        return bin2hex(random_bytes(8));
    }

    /**
     * Encrypt password using vtiger's method
     * Actual implementation: Vtiger_Functions::generateEncryptedPassword()
     */
    private static function encryptPassword(string $plainPassword): string
    {
        // Placeholder - actual encryption in infrastructure layer
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEncryptedPassword(): string
    {
        return $this->encryptedPassword;
    }

    public function getCryptMode(): string
    {
        return $this->cryptMode;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Deactivate portal access
     * Business Rule: Sets isactive = 0 (record preserved, not deleted)
     */
    public function deactivate(): self
    {
        return new self($this->username, $this->encryptedPassword, false, $this->cryptMode);
    }

    /**
     * Activate portal access
     */
    public function activate(): self
    {
        return new self($this->username, $this->encryptedPassword, true, $this->cryptMode);
    }

    /**
     * Update password (e.g., when email changes)
     */
    public function updatePassword(string $plainPassword): self
    {
        $encryptedPassword = self::encryptPassword($plainPassword);
        return new self($this->username, $encryptedPassword, $this->isActive, $this->cryptMode);
    }

    /**
     * Update username (must match new email)
     */
    public function updateUsername(string $newEmail, string $newPlainPassword): self
    {
        $encryptedPassword = self::encryptPassword($newPlainPassword);
        return new self($newEmail, $encryptedPassword, $this->isActive, $this->cryptMode);
    }

    public function recordLogin(): void
    {
        $this->lastLoginTime = new \DateTimeImmutable();
    }

    public function getLastLoginTime(): ?\DateTimeImmutable
    {
        return $this->lastLoginTime;
    }
}
