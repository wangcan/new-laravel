<?php

namespace Modules\ShowDocManager\Services;

use Modules\ShowDocManager\Models\ShowDocProject;
use Modules\ShowDocManager\Models\ShowDocDirectory;
use Modules\ShowDocManager\Models\ShowDocPage;
use Illuminate\Support\Facades\Log;

class ShowDocSyncService
{
    /**
     * 同步指定项目的目录和文档
     */
    public function syncProject(ShowDocProject $project): array
    {
        $result = ['directories' => 0, 'pages' => 0, 'errors' => []];

        try {
            $client = new ShowDocApiClient(
                $project->showdoc_url,
                $project->api_key,
                $project->api_token,
                $project->project_id
            );

            // 1. 同步目录树
            $catalogResult = $this->syncCatalogTree($project, $client);
            $result['directories'] = $catalogResult['count'];
            $result['errors'] = array_merge($result['errors'], $catalogResult['errors']);

            // 2. 同步页面内容
            $pageResult = $this->syncPages($project, $client);
            $result['pages'] = $pageResult['count'];
            $result['errors'] = array_merge($result['errors'], $pageResult['errors']);

            $project->touch();
        } catch (\Exception $e) {
            Log::error('ShowDoc sync failed: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'project_name' => $project->name
            ]);
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 同步目录树
     */
    private function syncCatalogTree(ShowDocProject $project, ShowDocApiClient $client): array
    {
        $result = ['count' => 0, 'errors' => []];

        try {
            $response = $client->getCatalogTree();

            if (isset($response['error_code']) && $response['error_code'] === 0) {
                $this->processCatalogNodes($project, $response['data'] ?? []);
                $result['count'] = ShowDocDirectory::where('project_id', $project->id)->count();
            } else {
                $result['errors'][] = $response['error_message'] ?? 'Unknown error';
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 递归处理目录节点
     */
    private function processCatalogNodes(ShowDocProject $project, array $nodes, ?string $parentCatId = null, int $level = 0): void
    {
        foreach ($nodes as $node) {
            $catId = (string)($node['id'] ?? $node['cat_id'] ?? '');

            ShowDocDirectory::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'cat_id' => $catId,
                ],
                [
                    'name' => $node['name'] ?? $node['cat_name'] ?? 'Unknown',
                    'parent_cat_id' => $parentCatId,
                    'level' => $level,
                    'sort' => $node['sort'] ?? 0,
                    'full_path' => $node['full_path'] ?? null,
                ]
            );

            if (!empty($node['children'])) {
                $this->processCatalogNodes($project, $node['children'], $catId, $level + 1);
            }
        }
    }

    /**
     * 同步页面内容
     */
    private function syncPages(ShowDocProject $project, ShowDocApiClient $client): array
    {
        $result = ['count' => 0, 'errors' => []];

        try {
            // 获取所有目录下的页面
            $directories = ShowDocDirectory::where('project_id', $project->id)->get();

            foreach ($directories as $directory) {
                // 根据实际 API 调整，可能需要遍历获取每个目录下的页面列表
                // 这里示例直接通过目录名获取页面
            }

            $result['count'] = ShowDocPage::where('project_id', $project->id)->count();
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 获取单个页面内容并更新本地
     */
    public function syncPageContent(ShowDocProject $project, string $pageId): ?ShowDocPage
    {
        try {
            $client = new ShowDocApiClient(
                $project->showdoc_url,
                $project->api_key,
                $project->api_token,
                $project->project_id
            );

            $response = $client->getPageContent($pageId);

            if (isset($response['error_code']) && $response['error_code'] === 0) {
                $data = $response['data'] ?? [];

                $page = ShowDocPage::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'page_id' => $pageId,
                    ],
                    [
                        'title' => $data['page_title'] ?? '',
                        'content' => $data['page_content'] ?? '',
                        'author' => $data['author'] ?? null,
                        'version' => $data['version'] ?? null,
                        'last_sync_at' => now(),
                    ]
                );

                return $page;
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync page content: ' . $e->getMessage());
        }

        return null;
    }
}
