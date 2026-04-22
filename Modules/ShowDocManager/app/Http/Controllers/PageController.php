<?php

namespace Modules\ShowDocManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ShowDocManager\Models\ShowDocPage;
use Modules\ShowDocManager\Models\ShowDocProject;
use Modules\ShowDocManager\Services\ShowDocApiClient;
use Modules\ShowDocManager\Services\ShowDocSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    protected ShowDocSyncService $syncService;

    public function __construct(ShowDocSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * 获取项目下所有页面
     */
    public function index(int $projectId, Request $request): JsonResponse
    {
        $query = ShowDocPage::where('project_id', $projectId);

        if ($request->has('directory_id')) {
            $query->where('directory_id', $request->directory_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $pages = $query->orderBy('title')->paginate($request->get('per_page', 20));

        return response()->json($pages);
    }

    /**
     * 获取页面详情
     */
    public function show(int $projectId, int $pageId): JsonResponse
    {
        $page = ShowDocPage::where('project_id', $projectId)
            ->with('directory')
            ->findOrFail($pageId);

        return response()->json($page);
    }

    /**
     * 同步单个页面内容
     */
    public function sync(int $projectId, string $pageId): JsonResponse
    {
        $project = ShowDocProject::findOrFail($projectId);

        $page = $this->syncService->syncPageContent($project, $pageId);

        if ($page) {
            return response()->json(['message' => 'Page synced', 'page' => $page]);
        }

        return response()->json(['error' => 'Failed to sync page'], 500);
    }

    /**
     * 批量同步所有页面
     */
    public function syncAll(int $projectId): JsonResponse
    {
        $project = ShowDocProject::findOrFail($projectId);

        $pages = ShowDocPage::where('project_id', $projectId)->get();
        $synced = 0;

        foreach ($pages as $page) {
            $result = $this->syncService->syncPageContent($project, $page->page_id);
            if ($result) {
                $synced++;
            }
        }

        return response()->json([
            'message' => 'Batch sync completed',
            'total' => $pages->count(),
            'synced' => $synced,
        ]);
    }
}
