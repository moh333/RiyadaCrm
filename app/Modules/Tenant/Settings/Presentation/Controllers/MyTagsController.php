<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MyTagsController
{
    /**
     * Display user tags
     */
    public function index(Request $request): View
    {
        $userId = $request->get('user_id', auth()->id());

        $user = \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->first();

        return view('tenant::settings.tags.index', [
            'userId' => $userId,
            'showTagCloud' => (bool) ($user->tagcloudview ?? false)
        ]);
    }

    /**
     * Get tags data for DataTables (AJAX)
     */
    public function data(Request $request): JsonResponse
    {
        $userId = $request->get('user_id', auth()->id());

        $tags = \DB::connection('tenant')
            ->table('vtiger_freetags')
            ->where('owner', $userId)
            ->get();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $tags->count(),
            'recordsFiltered' => $tags->count(),
            'data' => $tags
        ]);
    }

    /**
     * Store new tag
     */
    public function store(Request $request): JsonResponse
    {
        $tagName = $request->get('tag');
        $userId = auth()->id();

        $exists = \DB::connection('tenant')
            ->table('vtiger_freetags')
            ->where('tag', $tagName)
            ->where('owner', $userId)
            ->first();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Tag already exists'], 400);
        }

        $tagId = \DB::connection('tenant')->transaction(function () use ($tagName, $userId, $request) {
            $currentId = \DB::connection('tenant')->table('vtiger_freetags_seq')->max('id');
            $nextId = $currentId + 1;

            \DB::connection('tenant')->table('vtiger_freetags')->insert([
                'id' => $nextId,
                'tag' => $tagName,
                'raw_tag' => $tagName,
                'visibility' => $request->get('visibility', 'PRIVATE'),
                'owner' => $userId
            ]);

            \DB::connection('tenant')->table('vtiger_freetags_seq')->update(['id' => $nextId]);

            return $nextId;
        });

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'tag_id' => $tagId
        ]);
    }

    /**
     * Update tag
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tagName = $request->get('tag');

        \DB::connection('tenant')
            ->table('vtiger_freetags')
            ->where('id', $id)
            ->update([
                'tag' => $tagName,
                'raw_tag' => $tagName,
                'visibility' => $request->get('visibility', 'PRIVATE')
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully'
        ]);
    }

    /**
     * Delete tag
     */
    public function destroy(int $id): JsonResponse
    {
        \DB::connection('tenant')->transaction(function () use ($id) {
            \DB::connection('tenant')
                ->table('vtiger_freetags')
                ->where('id', $id)
                ->delete();

            \DB::connection('tenant')
                ->table('vtiger_freetagged_objects')
                ->where('tag_id', $id)
                ->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully'
        ]);
    }

    /**
     * Update tag cloud preference
     */
    public function updateTagCloud(Request $request): JsonResponse
    {
        $enabled = $request->get('enabled', false);
        $userId = auth()->id();

        \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->update(['tagcloudview' => $enabled ? 1 : 0]);

        return response()->json([
            'success' => true,
            'message' => 'Tag cloud preference updated'
        ]);
    }
}
