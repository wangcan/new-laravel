<?php

namespace Modules\ShowDocManager\Services;

use Illuminate\Support\Facades\Http;

class ShowDocApiClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiToken;
    private string $projectId;

    public function __construct(string $baseUrl, string $apiKey, string $apiToken, string $projectId)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->apiToken = $apiToken;
        $this->projectId = $projectId;
    }

    /**
     * 获取目录树
     */
    public function getCatalogTree(): array
    {
        $response = Http::asForm()->post(
            $this->baseUrl . '/server/index.php?s=/api/open/getCatalogTree',
            [
                'api_key' => $this->apiKey,
                'api_token' => $this->apiToken,
            ]
        );

        return $response->json();
    }

    /**
     * 获取页面内容
     */
    public function getPageContent(string $pageId): array
    {
        $response = Http::asForm()->post(
            $this->baseUrl . '/server/index.php?s=/api/open/getPageContent',
            [
                'api_key' => $this->apiKey,
                'api_token' => $this->apiToken,
                'page_id' => $pageId,
            ]
        );

        return $response->json();
    }

    /**
     * 更新/创建页面
     */
    public function updatePage(string $title, string $content, ?string $catalog = null): array
    {
        $data = [
            'api_key' => $this->apiKey,
            'api_token' => $this->apiToken,
            'page_title' => $title,
            'page_content' => $content,
        ];

        if ($catalog) {
            $data['cat_name'] = $catalog;
        }

        $response = Http::asForm()->post(
            $this->baseUrl . '/server/index.php?s=/api/open/updatePage',
            $data
        );

        return $response->json();
    }

    /**
     * 创建目录
     */
    public function createCatalog(string $catName, ?int $parentCatId = null): array
    {
        $data = [
            'api_key' => $this->apiKey,
            'api_token' => $this->apiToken,
            'cat_name' => $catName,
        ];

        if ($parentCatId) {
            $data['parent_cat_id'] = $parentCatId;
        }
        \Log::debug($this->baseUrl . '/server/index.php?s=/api/open/createCatalog');
        \Log::debug($data);

        $response = Http::asForm()->post(
            $this->baseUrl . '/server/index.php?s=/api/open/createCatalog',
            $data
        );

        return $response->json();
    }
}
