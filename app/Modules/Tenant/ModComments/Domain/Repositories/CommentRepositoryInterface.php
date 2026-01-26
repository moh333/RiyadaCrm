<?php

namespace App\Modules\Tenant\ModComments\Domain\Repositories;

use App\Modules\Tenant\ModComments\Domain\Comment;

interface CommentRepositoryInterface
{
    public function findById(int $id): ?Comment;
    public function getCommentsForRecord(int $recordId): array;
    public function save(Comment $comment): void;
    public function delete(int $id): void;
    public function nextIdentity(): int;
    public function addAttachment(int $commentId, array $fileData): void;
}
