<?php

namespace App\Modules\Tenant\ModComments\Application\UseCases;

use App\Modules\Tenant\ModComments\Domain\Repositories\CommentRepositoryInterface;

class DeleteModCommentUseCase
{
    public function __construct(
        private CommentRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): void
    {
        $comment = $this->repository->findById($id);
        if (!$comment) {
            throw new \Exception("Comment not found");
        }

        // Logic check: only owner can delete?
        if ($comment->getUserId() !== auth('tenant')->id()) {
            throw new \Exception("Unauthorized");
        }

        $this->repository->delete($id);
    }
}
