<?php

namespace App\Modules\Tenant\ModComments\Application\UseCases;

use App\Modules\Tenant\ModComments\Domain\Comment;
use App\Modules\Tenant\ModComments\Domain\Repositories\CommentRepositoryInterface;
use Illuminate\Http\UploadedFile;

class AddModCommentUseCase
{
    public function __construct(
        private CommentRepositoryInterface $repository
    ) {
    }

    public function execute(AddModCommentDTO $dto): Comment
    {
        $id = $this->repository->nextIdentity();

        $comment = Comment::create(
            $id,
            $dto->content,
            $dto->relatedTo,
            $dto->userId,
            $dto->parentId,
            $dto->isPrivate
        );

        $this->repository->save($comment);

        if ($dto->files) {
            foreach ($dto->files as $file) {
                $path = $file->store('attachments', 'public');
                $this->repository->addAttachment($id, [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType()
                ]);
            }
        }

        return $comment;
    }
}

class AddModCommentDTO
{
    public string $content;
    public int $relatedTo;
    public int $userId;
    public ?int $parentId;
    public int $isPrivate;
    /** @var UploadedFile[]|null */
    public ?array $files;

    public function __construct(array $data)
    {
        $this->content = $data['content'];
        $this->relatedTo = $data['related_to'];
        $this->userId = $data['user_id'];
        $this->parentId = $data['parent_id'] ?? null;
        $this->isPrivate = $data['is_private'] ?? 0;
        $this->files = $data['files'] ?? null;
    }
}
