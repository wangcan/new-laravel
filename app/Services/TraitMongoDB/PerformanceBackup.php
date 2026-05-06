<?php

namespace App\Services\TraitMongoDB;

use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

/**
 * CRUD 基础操作
 */
trait PerformanceBackup
{
    /**
     * ========== 性能监控 ==========
     */

    /**
     * 获取数据库状态
     *
     * @return array
     */
    public function getDatabaseStats(): array
    {
        try {
            $database = $this->getDatabase();
            $stats = $database->command(['dbStats' => 1]);
            $statsArray = $stats->toArray()[0] ?? [];

            // 转换大小为人类可读格式
            if (isset($statsArray['dataSize'])) {
                $statsArray['dataSize_readable'] = $this->formatBytes($statsArray['dataSize']);
            }
            if (isset($statsArray['storageSize'])) {
                $statsArray['storageSize_readable'] = $this->formatBytes($statsArray['storageSize']);
            }
            if (isset($statsArray['indexSize'])) {
                $statsArray['indexSize_readable'] = $this->formatBytes($statsArray['indexSize']);
            }

            return $statsArray;
        } catch (Exception $e) {
            $this->logError('getDatabaseStats', $e);
            throw $e;
        }
    }

    /**
     * 获取集合统计信息
     *
     * @return array
     */
    public function getCollectionStats(): array
    {
        try {
            $collection = $this->getCollection();
            $stats = $this->getDatabase()->command([
                'collStats' => $this->collectionName
            ]);
            $statsArray = $stats->toArray()[0] ?? [];

            if (isset($statsArray['size'])) {
                $statsArray['size_readable'] = $this->formatBytes($statsArray['size']);
            }
            if (isset($statsArray['avgObjSize'])) {
                $statsArray['avgObjSize_readable'] = $this->formatBytes($statsArray['avgObjSize']);
            }
            if (isset($statsArray['storageSize'])) {
                $statsArray['storageSize_readable'] = $this->formatBytes($statsArray['storageSize']);
            }

            return $statsArray;
        } catch (Exception $e) {
            $this->logError('getCollectionStats', $e);
            throw $e;
        }
    }

    /**
     * 获取查询执行计划
     *
     * @param array $filter 查询条件
     * @return array
     */
    public function explain(array $filter): array
    {
        try {
            $collection = $this->getCollection();
            $cursor = $collection->find($filter, ['explain' => true]);

            return $cursor->toArray()[0] ?? [];
        } catch (Exception $e) {
            $this->logError('explain', $e);
            throw $e;
        }
    }

    /**
     * ========== 备份和恢复 ==========
     */

    /**
     * 导出集合数据
     *
     * @param string $outputFile 输出文件路径
     * @param array $filter 筛选条件
     * @return bool
     */
    public function exportCollection(string $outputFile, array $filter = []): bool
    {
        try {
            $data = $this->find($filter, ['limit' => 0]); // 0 表示无限制

            $exportData = [
                'collection' => $this->collectionName,
                'exported_at' => Carbon::now()->toIso8601String(),
                'total' => count($data),
                'data' => $data,
            ];

            $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($outputFile, $json);

            Log::info("数据导出成功", [
                'collection' => $this->collectionName,
                'file' => $outputFile,
                'count' => count($data),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("数据导出失败", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 导入集合数据
     *
     * @param string $inputFile 输入文件路径
     * @param bool $clearExisting 是否清空现有数据
     * @return array
     */
    public function importCollection(string $inputFile, bool $clearExisting = false): array
    {
        try {
            if (!file_exists($inputFile)) {
                throw new Exception("文件不存在: {$inputFile}");
            }

            $content = file_get_contents($inputFile);
            $importData = json_decode($content, true);

            if (!$importData || !isset($importData['data'])) {
                throw new Exception("无效的导入文件格式");
            }

            if ($clearExisting) {
                $this->deleteMany([]);
            }

            $result = $this->insertMany($importData['data']);

            Log::info("数据导入成功", [
                'collection' => $this->collectionName,
                'file' => $inputFile,
                'count' => count($importData['data']),
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error("数据导入失败", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
