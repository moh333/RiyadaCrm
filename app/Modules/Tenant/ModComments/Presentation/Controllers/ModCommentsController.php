<?php

namespace App\Modules\Tenant\ModComments\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\ModComments\Application\UseCases\AddModCommentDTO;
use App\Modules\Tenant\ModComments\Application\UseCases\AddModCommentUseCase;
use App\Modules\Tenant\ModComments\Application\UseCases\UpdateModCommentUseCase;
use App\Modules\Tenant\ModComments\Domain\Repositories\CommentRepositoryInterface;
use Illuminate\Http\Request;

class ModCommentsController extends Controller
{
    public function __construct(
        private CommentRepositoryInterface $repository,
        private AddModCommentUseCase $addUseCase,
        private UpdateModCommentUseCase $updateUseCase,
        private \App\Modules\Tenant\ModComments\Application\UseCases\DeleteModCommentUseCase $deleteUseCase
    ) {
    }

    public function store(Request $request)
    {
        $request->validate([
            'commentcontent' => 'required|string',
            'related_to' => 'required|integer',
            'files.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        $dto = new AddModCommentDTO([
            'content' => $request->commentcontent,
            'related_to' => $request->related_to,
            'user_id' => auth('tenant')->id(),
            'parent_id' => $request->parent_id,
            'is_private' => $request->is_private ?? 0,
            'files' => $request->file('files')
        ]);

        try {
            $this->addUseCase->execute($dto);
            return back()->with('success', 'Comment added successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'commentcontent' => 'required|string',
            'reasontoedit' => 'required|string',
        ]);

        try {
            $this->updateUseCase->execute($id, $request->commentcontent, $request->reasontoedit);
            return back()->with('success', 'Comment updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->deleteUseCase->execute($id);
            return back()->with('success', 'Comment deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
