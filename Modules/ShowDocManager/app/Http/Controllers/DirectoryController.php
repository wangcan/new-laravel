<?php

namespace Modules\ShowDocManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ShowDocManager\Models\ShowDocDirectory;
use Modules\ShowDocManager\Models\ShowDocProject;
use Modules\ShowDocManager\Services\ShowDocApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DirectoryController extends Controller
{
    /**
     * 获取项目的目录树
     */
    public function tree(int $projectId): JsonResponse
    {
        $project = ShowDocProject::findOrFail($projectId);

        $directories = ShowDocDirectory::where('project_id', $projectId)
            ->whereNull('parent_cat_id')
            ->with('children')
            ->orderBy('sort')
            ->get();

        return response()->json($directories);
    }

    /**
     * 获取目录下的页面列表
     */
    public function pages(int $projectId, string $catId): JsonResponse
    {
        $directory = ShowDocDirectory::where('project_id', $projectId)
            ->where('cat_id', $catId)
            ->firstOrFail();

        $pages = $directory->pages()->orderBy('title')->get();

        return response()->json($pages);
    }

    /**
     * 在远程 ShowDoc 创建目录
     */
    public function store(Request $request, int $projectId): JsonResponse
    {
        $project = ShowDocProject::findOrFail($projectId);

        $validated = $request->validate([
            'cat_name' => 'required|string|max:255',
            'parent_cat_id' => 'nullable|integer',
        ]);

        $client = new ShowDocApiClient(
            $project->showdoc_url,
            $project->api_key,
            $project->api_token,
            $project->project_id
        );

        $response = $client->createCatalog($validated['cat_name'], $validated['parent_cat_id'] ?? null);

        if (isset($response['error_code']) && $response['error_code'] === 0) {
            // 重新同步目录
            $syncService = app(ShowDocSyncService::class);
            $syncService->syncProject($project);

            return response()->json(['message' => 'Directory created', 'data' => $response]);
        }

        return response()->json(['error' => $response['error_message'] ?? 'Creation failed'], 500);
    }
}
