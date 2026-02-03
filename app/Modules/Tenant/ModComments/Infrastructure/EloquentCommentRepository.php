<?php

namespace App\Modules\Tenant\ModComments\Infrastructure;

use App\Modules\Tenant\ModComments\Domain\Comment;
use App\Modules\Tenant\ModComments\Domain\Repositories\CommentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use DateTimeImmutable;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function nextIdentity(): int
    {
        $query = DB::connection('tenant')->table('vtiger_crmentity_seq')->lockForUpdate();
        $result = $query->first();

        if (!$result) {
            $maxId = DB::connection('tenant')->table('vtiger_crmentity')->max('crmid') ?? 1000;
            $nextId = $maxId + 1;
            DB::connection('tenant')->table('vtiger_crmentity_seq')->insert(['id' => $nextId]);
            return $nextId;
        }

        $nextId = $result->id + 1;
        DB::connection('tenant')->table('vtiger_crmentity_seq')->update(['id' => $nextId]);

        return $nextId;
    }

    public function findById(int $id): ?Comment
    {
        $data = DB::connection('tenant')->table('vtiger_modcomments')
            ->join('vtiger_crmentity', 'vtiger_modcomments.modcommentsid', '=', 'vtiger_crmentity.crmid')
            ->leftJoin('vtiger_users', 'vtiger_modcomments.userid', '=', 'vtiger_users.id')
            ->where('vtiger_crmentity.crmid', $id)
            ->where('vtiger_crmentity.deleted', 0)
            ->select([
                'vtiger_modcomments.*',
                'vtiger_crmentity.*',
                DB::raw("CONCAT(vtiger_users.first_name, ' ', vtiger_users.last_name) as user_name")
            ])
            ->first();

        if (!$data)
            return null;

        $attachments = $this->getAttachments($id);

        return Comment::fromDatabase((array) $data, $attachments);
    }

    public function getCommentsForRecord(?int $recordId): array
    {
        if (!$recordId) {
            return [];
        }

        $results = DB::connection('tenant')->table('vtiger_modcomments')
            ->join('vtiger_crmentity', 'vtiger_modcomments.modcommentsid', '=', 'vtiger_crmentity.crmid')
            ->leftJoin('vtiger_users', 'vtiger_modcomments.userid', '=', 'vtiger_users.id')
            ->where('vtiger_modcomments.related_to', $recordId)
            ->where('vtiger_crmentity.deleted', 0)
            ->select([
                'vtiger_modcomments.*',
                'vtiger_crmentity.*',
                DB::raw("CONCAT(vtiger_users.first_name, ' ', vtiger_users.last_name) as user_name")
            ])
            ->orderBy('vtiger_crmentity.createdtime', 'desc')
            ->get();

        $comments = [];
        foreach ($results as $row) {
            $attachments = $this->getAttachments($row->modcommentsid);
            $comments[] = Comment::fromDatabase((array) $row, $attachments);
        }

        return $comments;
    }

    public function save(Comment $comment): void
    {
        DB::connection('tenant')->transaction(function () use ($comment) {
            $isNew = !DB::connection('tenant')->table('vtiger_modcomments')
                ->where('modcommentsid', $comment->getId())
                ->exists();

            if ($isNew) {
                DB::connection('tenant')->table('vtiger_crmentity')->insert([
                    'crmid' => $comment->getId(),
                    'smcreatorid' => $comment->getUserId(),
                    'smownerid' => $comment->getUserId(),
                    'modifiedby' => $comment->getUserId(),
                    'setype' => 'ModComments',
                    'createdtime' => $comment->getCreatedTime()->format('Y-m-d H:i:s'),
                    'modifiedtime' => $comment->getModifiedTime()->format('Y-m-d H:i:s'),
                    'deleted' => 0,
                    'label' => substr($comment->getContent(), 0, 100)
                ]);

                DB::connection('tenant')->table('vtiger_modcomments')->insert([
                    'modcommentsid' => $comment->getId(),
                    'commentcontent' => $comment->getContent(),
                    'related_to' => $comment->getRelatedTo(),
                    'parent_comments' => $comment->getParentId(),
                    'userid' => $comment->getUserId(),
                    'is_private' => $comment->isPrivate() ? 1 : 0
                ]);
            } else {
                DB::connection('tenant')->table('vtiger_crmentity')
                    ->where('crmid', $comment->getId())
                    ->update([
                        'modifiedby' => $comment->getUserId(),
                        'modifiedtime' => $comment->getModifiedTime()->format('Y-m-d H:i:s'),
                        'label' => substr($comment->getContent(), 0, 100)
                    ]);

                DB::connection('tenant')->table('vtiger_modcomments')
                    ->where('modcommentsid', $comment->getId())
                    ->update([
                        'commentcontent' => $comment->getContent(),
                        'reasontoedit' => $comment->getReasonToEdit(),
                        'is_private' => $comment->isPrivate() ? 1 : 0
                    ]);
            }
        });
    }

    public function delete(int $id): void
    {
        DB::connection('tenant')->table('vtiger_crmentity')
            ->where('crmid', $id)
            ->update(['deleted' => 1]);
    }

    public function addAttachment(int $commentId, array $fileData): void
    {
        DB::connection('tenant')->transaction(function () use ($commentId, $fileData) {
            $attachmentId = $this->nextIdentity();

            DB::connection('tenant')->table('vtiger_crmentity')->insert([
                'crmid' => $attachmentId,
                'smcreatorid' => auth('tenant')->id() ?? 0,
                'smownerid' => auth('tenant')->id() ?? 0,
                'setype' => 'ModComments Attachment',
                'createdtime' => now(),
                'modifiedtime' => now(),
                'deleted' => 0,
                'label' => $fileData['name']
            ]);

            DB::connection('tenant')->table('vtiger_attachments')->insert([
                'attachmentsid' => $attachmentId,
                'name' => $fileData['name'],
                'path' => $fileData['path'],
                'type' => $fileData['type']
            ]);

            DB::connection('tenant')->table('vtiger_seattachmentsrel')->insert([
                'crmid' => $commentId,
                'attachmentsid' => $attachmentId
            ]);
        });
    }

    private function getAttachments(int $commentId): array
    {
        return DB::connection('tenant')->table('vtiger_seattachmentsrel')
            ->join('vtiger_attachments', 'vtiger_seattachmentsrel.attachmentsid', '=', 'vtiger_attachments.attachmentsid')
            ->where('vtiger_seattachmentsrel.crmid', $commentId)
            ->get()
            ->toArray();
    }
}
