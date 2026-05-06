<?php

namespace App\Services\TraitMongoDB;

use Exception;
use Carbon\Carbon;

/**
 * CRUD 基础操作
 */
trait MongoDBCRUD
{
    /**
     * ========== 基础 CRUD 操作 ==========
     */

    /**
     * 查询多条记录
     *
     * @param array $filter 筛选条件
     * @param array $options 选项（sort, limit, skip, projection等）
     * @return array
     */
    public function find(array $filter = [], array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $collection = $this->getCollection();

            // 处理 ID 转换
            $filter = $this->convertIds($filter);

            // 设置默认值
            $options['limit'] = $options['limit'] ?? config('mongodb.system.default_per_page', 15);
            $options['skip'] = $options['skip'] ?? 0;

            // 构建查询
            $query = $collection->find($filter, $options);

            // 转换为数组
            $results = iterator_to_array($query);

            // 转换 ObjectId 为字符串
            $results = $this->convertObjectIdToString($results);

            $this->logQuery('find', ['filter' => $filter, 'options' => $options], $startTime);

            return $results;
        } catch (Exception $e) {
            $this->logError('find', $e);
            throw $e;
        }
    }

    /**
     * 查询单条记录
     *
     * @param array|string $filter 筛选条件或ID
     * @param array $options 选项
     * @return array|null
     */
    public function findOne($filter = [], array $options = []): ?array
    {
        $startTime = microtime(true);

        try {
            // 如果 $filter 是字符串，当作 ID 处理
            if (is_string($filter)) {
                $filter = ['_id' => $filter];
            }

            $filter = $this->convertIds($filter);
            $collection = $this->getCollection();
            $result = $collection->findOne($filter, $options);

            if ($result) {
                $result = $this->convertObjectIdToString([$result])[0];
            }

            $this->logQuery('findOne', ['filter' => $filter], $startTime);

            return $result;
        } catch (Exception $e) {
            $this->logError('findOne', $e);
            throw $e;
        }
    }

    /**
     * 插入单条记录
     *
     * @param array $data 要插入的数据
     * @return array 插入结果及ID
     */
    public function insertOne(array $data): array
    {
        $startTime = microtime(true);

        try {
            // 自动添加时间戳
            $data = $this->addTimestamps($data);

            // 处理日期字段
            $data = $this->convertDates($data);

            $collection = $this->getCollection();
            $result = $collection->insertOne($data);

            $insertedId = (string) $result->getInsertedId();

            $this->logQuery('insertOne', ['data' => $data], $startTime);

            return [
                'success' => true,
                'id' => $insertedId,
                'inserted_count' => 1,
            ];
        } catch (Exception $e) {
            $this->logError('insertOne', $e);
            throw $e;
        }
    }

    /**
     * 批量插入记录
     *
     * @param array $documents 要插入的文档数组
     * @return array 插入结果
     */
    public function insertMany(array $documents): array
    {
        $startTime = microtime(true);

        try {
            // 为每个文档添加时间戳并转换日期
            array_walk($documents, function (&$doc) {
                $doc = $this->addTimestamps($doc);
                $doc = $this->convertDates($doc);
            });

            $collection = $this->getCollection();
            $result = $collection->insertMany($documents);

            $insertedIds = array_map(function($id) {
                return (string) $id;
            }, $result->getInsertedIds());

            $this->logQuery('insertMany', ['count' => count($documents)], $startTime);

            return [
                'success' => true,
                'inserted_ids' => $insertedIds,
                'inserted_count' => $result->getInsertedCount(),
            ];
        } catch (Exception $e) {
            $this->logError('insertMany', $e);
            throw $e;
        }
    }

    /**
     * 更新单条记录
     *
     * @param array|string $filter 筛选条件或ID
     * @param array $data 要更新的数据
     * @param array $options 选项
     * @return array 更新结果
     */
    public function updateOne($filter, array $data, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            if (is_string($filter)) {
                $filter = ['_id' => $filter];
            }

            $filter = $this->convertIds($filter);

            // 自动更新时间戳
            $data['$set'] = $data['$set'] ?? [];

            // 转换日期
            if (isset($data['$set'])) {
                $data['$set'] = $this->convertDates($data['$set']);
            }

            $collection = $this->getCollection();
            $result = $collection->updateOne($filter, $data, $options);

            $this->logQuery('updateOne', ['filter' => $filter, 'data' => $data], $startTime);

            return [
                'success' => true,
                'matched_count' => $result->getMatchedCount(),
                'modified_count' => $result->getModifiedCount(),
                'upserted_id' => $result->getUpsertedId() ? (string) $result->getUpsertedId() : null,
            ];
        } catch (Exception $e) {
            $this->logError('updateOne', $e);
            throw $e;
        }
    }

    /**
     * 批量更新记录
     *
     * @param array $filter 筛选条件
     * @param array $data 要更新的数据
     * @param array $options 选项
     * @return array 更新结果
     */
    public function updateMany(array $filter, array $data, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $filter = $this->convertIds($filter);

            // 批量更新时间戳
            if (!isset($data['$set'])) {
                $data['$set'] = [];
            }
            $data['$set']['updated_at'] = Carbon::now();

            $data['$set'] = $this->convertDates($data['$set']);

            $collection = $this->getCollection();
            $result = $collection->updateMany($filter, $data, $options);

            $this->logQuery('updateMany', ['filter' => $filter], $startTime);

            return [
                'success' => true,
                'matched_count' => $result->getMatchedCount(),
                'modified_count' => $result->getModifiedCount(),
            ];
        } catch (Exception $e) {
            $this->logError('updateMany', $e);
            throw $e;
        }
    }

    /**
     * 删除单条记录
     *
     * @param array|string $filter 筛选条件或ID
     * @return array 删除结果
     */
    public function deleteOne($filter): array
    {
        $startTime = microtime(true);

        try {
            if (is_string($filter)) {
                $filter = ['_id' => $filter];
            }

            $filter = $this->convertIds($filter);
            $collection = $this->getCollection();
            $result = $collection->deleteOne($filter);

            $this->logQuery('deleteOne', ['filter' => $filter], $startTime);

            return [
                'success' => true,
                'deleted_count' => $result->getDeletedCount(),
            ];
        } catch (Exception $e) {
            $this->logError('deleteOne', $e);
            throw $e;
        }
    }

    /**
     * 批量删除记录
     *
     * @param array $filter 筛选条件
     * @return array 删除结果
     */
    public function deleteMany(array $filter): array
    {
        $startTime = microtime(true);

        try {
            $filter = $this->convertIds($filter);
            $collection = $this->getCollection();
            $result = $collection->deleteMany($filter);

            $this->logQuery('deleteMany', ['filter' => $filter], $startTime);

            return [
                'success' => true,
                'deleted_count' => $result->getDeletedCount(),
            ];
        } catch (Exception $e) {
            $this->logError('deleteMany', $e);
            throw $e;
        }
    }

    /**
     * 统计文档数量
     *
     * @param array $filter 筛选条件
     * @return int
     */
    public function count(array $filter = []): int
    {
        $startTime = microtime(true);

        try {
            $filter = $this->convertIds($filter);
            $collection = $this->getCollection();
            $count = $collection->countDocuments($filter);

            $this->logQuery('count', ['filter' => $filter], $startTime);

            return $count;
        } catch (Exception $e) {
            $this->logError('count', $e);
            throw $e;
        }
    }

    /**
     * 分页查询
     *
     * @param int $page 页码
     * @param int $perPage 每页数量
     * @param array $filter 筛选条件
     * @param array $sort 排序
     * @return array
     */
    public function paginate(int $page = 1, int $perPage = null, array $filter = [], array $sort = []): array
    {
        $perPage = $perPage ?? config('mongodb.system.default_per_page', 15);
        $skip = ($page - 1) * $perPage;

        $total = $this->count($filter);

        $options = [
            'limit' => $perPage,
            'skip' => $skip,
        ];

        if (!empty($sort)) {
            $options['sort'] = $sort;
        }

        $items = $this->find($filter, $options);

        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => (int) $perPage,
            'total' => (int) $total,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * ========== 批量操作 ==========
     */

    /**
     * 批量写入操作
     *
     * @param array $operations 操作数组
     * @return array
     */
    public function bulkWrite(array $operations): array
    {
        $startTime = microtime(true);

        try {
            $collection = $this->getCollection();
            $result = $collection->bulkWrite($operations);

            $this->logQuery('bulkWrite', ['operations' => array_keys($operations)], $startTime);

            return [
                'success' => true,
                'inserted_count' => $result->getInsertedCount(),
                'matched_count' => $result->getMatchedCount(),
                'modified_count' => $result->getModifiedCount(),
                'deleted_count' => $result->getDeletedCount(),
                'upserted_count' => $result->getUpsertedCount(),
            ];
        } catch (Exception $e) {
            $this->logError('bulkWrite', $e);
            throw $e;
        }
    }
}
