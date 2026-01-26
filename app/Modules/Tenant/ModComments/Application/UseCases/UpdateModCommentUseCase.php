<?php

namespace App\Modules\Tenant\ModComments\Application\UseCases;

use App\Modules\Tenant\ModComments\Domain\Comment;
use App\Modules\Tenant\ModComments\Domain\Repositories\CommentRepositoryInterface;

class UpdateModCommentUseCase
{
    public function __construct(
        private CommentRepositoryInterface $repository
    ) {
    }

    public function execute(int $id, string $content, string $reason): Comment
    {
        $comment = $this->repository->findById($id);
        if (!$comment) {
            throw new \Exception("Comment not found");
        }

        $comment->updateContent($content, $reason);
        $this->repository->save($comment);

        return $comment;
    }
}
