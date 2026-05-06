<?php

namespace App\Services;

/**
 * MongoDB 管理和维护服务类
 *
 * 提供完整的 MongoDB 操作功能，包括：
 * - CRUD 基础操作
 * - 高级查询和聚合
 * - 索引管理
 * - 事务支持
 * - 批量操作
 * - 性能监控
 * - 备份恢复
 */
class MongoDBService
{
    use \App\Services\TraitMongoDB\BaseOperation;
    use \App\Services\TraitMongoDB\ExtOperation;
    use \App\Services\TraitMongoDB\MongoDBCRUD;
    use \App\Services\TraitMongoDB\PerformanceBackup;
    use \App\Services\TraitMongoDB\TransactionIndexed;

    /**
     * 数据库连接实例
     */
    protected $connection;

    /**
     * 数据库名称
     */
    protected $databaseName;

    /**
     * 当前集合名称
     */
    protected $collectionName;

    /**
     * 查询日志
     */
    protected $queryLog = [];

    /**
     * 是否启用查询日志
     */
    protected $enableQueryLog = false;

    /**
     * 构造函数
     *
     * @param string|null $connection 连接名称
     */
    public function __construct($connection = null)
    {
        $this->connection = $connection ?? config('mongodb.default');
        $this->databaseName = config("mongodb.connections.{$this->connection}.database");

        // 从配置文件读取日志设置
        $this->enableQueryLog = config('mongodb.system.log_queries', false);
    }
}
