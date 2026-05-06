<?php

namespace App\Services\TraitMongoDB;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\Collection;
use MongoDB\Database;
use Exception;
use Carbon\Carbon;

/**
 * CRUD 基础操作
 */
trait BaseOperation
{
    /**
     * 获取 MongoDB 数据库实例
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return DB::connection($this->connection)->getMongoDB();
    }

    /**
     * 获取集合实例
     *
     * @param string|null $collection
     * @return Collection
     */
    public function getCollection($collection = null): Collection
    {
        $collectionName = $collection ?? $this->collectionName;
        if (!$collectionName) {
            throw new Exception("Collection name is required");
        }
        return $this->getDatabase()->selectCollection($collectionName);
    }

    /**
     * 设置当前集合
     *
     * @param string $collection
     * @return $this
     */
    public function collection($collection)
    {
        $this->collectionName = $collection;
        return $this;
    }

    /**
     * 开始记录查询日志
     */
    public function enableQueryLog()
    {
        $this->enableQueryLog = true;
        $this->queryLog = [];
    }

    /**
     * 获取查询日志
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * 记录查询
     */
    protected function logQuery($operation, $query, $startTime, $endTime = null)
    {
        if (!$this->enableQueryLog) {
            return;
        }

        $duration = $endTime ? ($endTime - $startTime) * 1000 : null;

        $log = [
            'operation' => $operation,
            'collection' => $this->collectionName,
            'query' => $query,
            'duration_ms' => round($duration, 2),
            'timestamp' => Carbon::now(),
        ];

        $this->queryLog[] = $log;

        // 记录慢查询
        $slowThreshold = config('mongodb.system.slow_query_threshold', 100);
        if ($duration && $duration > $slowThreshold) {
            Log::channel('mongodb')->warning("MongoDB 慢查询", $log);
        }
    }

    /**
     * ========== 高级查询和聚合 ==========
     */

    /**
     * 聚合查询
     *
     * @param array $pipeline 聚合管道
     * @param array $options 选项
     * @return array
     */
    public function aggregate(array $pipeline, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $collection = $this->getCollection();
            $cursor = $collection->aggregate($pipeline, $options);
            $results = iterator_to_array($cursor);
            $results = $this->convertObjectIdToString($results);

            $this->logQuery('aggregate', ['pipeline' => $pipeline], $startTime);

            return $results;
        } catch (Exception $e) {
            $this->logError('aggregate', $e);
            throw $e;
        }
    }

    /**
     * 分组统计
     *
     * @param string $groupBy 分组字段
     * @param array $aggregations 聚合函数
     * @param array $match 匹配条件
     * @return array
     */
    public function groupBy(string $groupBy, array $aggregations = [], array $match = []): array
    {
        $pipeline = [];

        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }

        $group = ['_id' => '$' . $groupBy];
        foreach ($aggregations as $field => $operation) {
            list($op, $onField) = explode(':', $operation);
            $group[$field] = ['$' . $op => '$' . $onField];
        }

        $pipeline[] = ['$group' => $group];

        return $this->aggregate($pipeline);
    }
}
