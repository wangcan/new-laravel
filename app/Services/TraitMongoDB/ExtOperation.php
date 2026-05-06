<?php

namespace App\Services\TraitMongoDB;

use Illuminate\Support\Facades\Log;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Exception;
use Carbon\Carbon;

/**
 * CRUD 基础操作
 */
trait ExtOperation
{

    /**
     * ========== 辅助方法 ==========
     */

    /**
     * 转换 _id 字符串为 ObjectId
     *
     * @param array $filter
     * @return array
     */
    protected function convertIds(array $filter): array
    {
        if (isset($filter['_id'])) {
            if (is_string($filter['_id']) && strlen($filter['_id']) === 24) {
                $filter['_id'] = new ObjectId($filter['_id']);
            } elseif (is_array($filter['_id']) && isset($filter['_id']['$in'])) {
                foreach ($filter['_id']['$in'] as &$id) {
                    if (is_string($id) && strlen($id) === 24) {
                        $id = new ObjectId($id);
                    }
                }
            }
        }

        return $filter;
    }

    /**
     * 转换 ObjectId 为字符串
     *
     * @param array $data
     * @return array
     */
    protected function convertObjectIdToString(array $data): array
    {
        array_walk_recursive($data, function (&$item) {
            if ($item instanceof ObjectId) {
                $item = (string) $item;
            } elseif ($item instanceof UTCDateTime) {
                $item = Carbon::createFromTimestampMs($item->toDateTime()->getTimestamp() * 1000);
            }
        });

        return $data;
    }

    /**
     * 转换日期字符串为 UTCDateTime
     *
     * @param array $data
     * @return array
     */
    protected function convertDates(array $data): array
    {
        array_walk_recursive($data, function (&$item) {
            if (is_string($item) && strtotime($item)) {
                $item = new UTCDateTime(strtotime($item) * 1000);
            }
        });

        return $data;
    }

    /**
     * 添加时间戳
     *
     * @param array $data
     * @return array
     */
    protected function addTimestamps(array $data): array
    {
        $now = Carbon::now();

        if (!isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        $data['updated_at'] = $now;

        return $data;
    }

    /**
     * 格式化字节大小
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * 记录错误日志
     *
     * @param string $operation
     * @param Exception $e
     */
    protected function logError(string $operation, Exception $e)
    {
        Log::error("MongoDB 操作失败: {$operation}", [
            'collection' => $this->collectionName,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * 获取 MongoDB 连接名称
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connection;
    }
}
