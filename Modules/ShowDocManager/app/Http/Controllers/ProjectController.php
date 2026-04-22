<?php

namespace Modules\ShowDocManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ShowDocManager\Models\ShowDocProject;
use Modules\ShowDocManager\Services\ShowDocSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    protected ShowDocSyncService $syncService;

    public function __construct(ShowDocSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * 获取项目列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShowDocProject::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $projects = $query->paginate($request->get('per_page', 15));

        return response()->json($projects);
    }

    /**
     * 创建项目
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'showdoc_url' => 'required|url',
            'api_key' => 'required|string',
            'api_token' => 'required|string',
            'project_id' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $project = ShowDocProject::create($validated);

        return response()->json($project, 201);
    }

    /**
     * 获取项目详情
     */
    public function show(int $id): JsonResponse
    {
        $project = ShowDocProject::with(['directories', 'pages'])->findOrFail($id);

        return response()->json($project);
    }

    /**
     * 更新项目
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $project = ShowDocProject::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'showdoc_url' => 'sometimes|url',
            'api_key' => 'sometimes|string',
            'api_token' => 'sometimes|string',
            'project_id' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    /**
     * 删除项目
     */
    public function destroy(int $id): JsonResponse
    {
        $project = ShowDocProject::findOrFail($id);
        $project->delete();

        return response()->json(null, 204);
    }

    /**
     * 同步项目目录和文档
     */
    public function sync(int $id): JsonResponse
    {
        $project = ShowDocProject::findOrFail($id);

        if ($project->status !== 'active') {
            return response()->json(['error' => 'Project is not active'], 422);
        }

        $result = $this->syncService->syncProject($project);

        return response()->json([
            'message' => 'Sync completed',
            'project' => $project->name,
            'directories_synced' => $result['directories'],
            'pages_synced' => $result['pages'],
            'errors' => $result['errors'],
        ]);
    }
}
