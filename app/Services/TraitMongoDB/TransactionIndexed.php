<?php

namespace App\Services\TraitMongoDB;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * CRUD 基础操作
 */
trait TransactionIndexed
{
    /**
     * ========== 索引管理 ==========
     */

    /**
     * 创建索引
     *
     * @param array $keys 索引字段 ['field' => 1/-1]
     * @param array $options 索引选项
     * @return string 索引名称
     */
    public function createIndex(array $keys, array $options = []): string
    {
        try {
            $collection = $this->getCollection();
            $result = $collection->createIndex($keys, $options);

            Log::info("索引创建成功", [
                'collection' => $this->collectionName,
                'index' => $result,
                'keys' => $keys,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error("索引创建失败", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 创建唯一索引
     *
     * @param string|array $field 字段名或多个字段
     * @return string
     */
    public function createUniqueIndex($field): string
    {
        $keys = is_array($field) ? array_fill_keys($field, 1) : [$field => 1];

        return $this->createIndex($keys, ['unique' => true]);
    }

    /**
     * 创建复合索引
     *
     * @param array $fields 字段及排序 ['field1' => 1, 'field2' => -1]
     * @return string
     */
    public function createCompoundIndex(array $fields): string
    {
        return $this->createIndex($fields);
    }

    /**
     * 创建全文索引
     *
     * @param string|array $fields 要索引的字段
     * @return string
     */
    public function createTextIndex($fields): string
    {
        $keys = [];
        $fieldNames = is_array($fields) ? $fields : [$fields];

        foreach ($fieldNames as $field) {
            $keys[$field] = 'text';
        }

        return $this->createIndex($keys);
    }

    /**
     * 获取所有索引
     *
     * @return array
     */
    public function getIndexes(): array
    {
        try {
            $collection = $this->getCollection();
            $indexes = iterator_to_array($collection->listIndexes());

            return $this->convertObjectIdToString($indexes);
        } catch (Exception $e) {
            $this->logError('getIndexes', $e);
            throw $e;
        }
    }

    /**
     * 删除索引
     *
     * @param string $indexName 索引名称
     * @return array
     */
    public function dropIndex(string $indexName): array
    {
        try {
            $collection = $this->getCollection();
            $result = $collection->dropIndex($indexName);

            return ['success' => true, 'result' => $result];
        } catch (Exception $e) {
            $this->logError('dropIndex', $e);
            throw $e;
        }
    }

    /**
     * ========== 事务支持 ==========
     */

    /**
     * 开始事务
     *
     * @return \MongoDB\Driver\Session
     */
    public function startTransaction()
    {
        $client = DB::connection($this->connection)->getMongoClient();
        $session = $client->startSession();
        $session->startTransaction();

        return $session;
    }

    /**
     * 提交事务
     *
     * @param \MongoDB\Driver\Session $session
     * @return bool
     */
    public function commitTransaction($session): bool
    {
        try {
            $session->commitTransaction();
            return true;
        } catch (Exception $e) {
            $this->logError('commitTransaction', $e);
            throw $e;
        }
    }

    /**
     * 回滚事务
     *
     * @param \MongoDB\Driver\Session $session
     * @return bool
     */
    public function abortTransaction($session): bool
    {
        try {
            $session->abortTransaction();
            return true;
        } catch (Exception $e) {
            $this->logError('abortTransaction', $e);
            throw $e;
        }
    }
}
