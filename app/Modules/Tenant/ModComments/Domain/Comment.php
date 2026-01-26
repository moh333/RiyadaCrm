<?php

namespace App\Modules\Tenant\ModComments\Domain;

use DateTimeImmutable;

class Comment
{
    private function __construct(
        private int $id,
        private string $content,
        private int $relatedTo,
        private ?int $parentId,
        private int $userId,
        private ?string $userName,
        private ?string $reasonToEdit,
        private int $isPrivate,
        private DateTimeImmutable $createdTime,
        private DateTimeImmutable $modifiedTime,
        private ?array $attachments = []
    ) {
    }

    public static function create(
        int $id,
        string $content,
        int $relatedTo,
        int $userId,
        ?int $parentId = null,
        int $isPrivate = 0
    ): self {
        return new self(
            $id,
            $content,
            $relatedTo,
            $parentId,
            $userId,
            null, // userName
            null, // reasonToEdit
            $isPrivate,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            []
        );
    }

    public static function fromDatabase(array $data, array $attachments = []): self
    {
        return new self(
            (int) $data['modcommentsid'],
            (string) $data['commentcontent'],
            (int) $data['related_to'],
            isset($data['parent_comments']) ? (int) $data['parent_comments'] : null,
            (int) $data['userid'],
            $data['user_name'] ?? null,
            $data['reasontoedit'] ?? null,
            (int) ($data['is_private'] ?? 0),
            new DateTimeImmutable($data['createdtime']),
            new DateTimeImmutable($data['modifiedtime']),
            $attachments
        );
    }

    public function updateContent(string $content, string $reason): void
    {
        $this->content = $content;
        $this->reasonToEdit = $reason;
        $this->modifiedTime = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function getRelatedTo(): int
    {
        return $this->relatedTo;
    }
    public function getParentId(): ?int
    {
        return $this->parentId;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getReasonToEdit(): ?string
    {
        return $this->reasonToEdit;
    }
    public function getUserName(): ?string
    {
        return $this->userName;
    }
    public function isPrivate(): bool
    {
        return $this->isPrivate === 1;
    }
    public function getCreatedTime(): DateTimeImmutable
    {
        return $this->createdTime;
    }
    public function getModifiedTime(): DateTimeImmutable
    {
        return $this->modifiedTime;
    }
    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
