<?php

namespace App\Http\Controllers\Traits;

use App\Services\MongoDBService;
use Illuminate\Http\Request;

trait TestMongoDB
{
    protected $mongoDB;

    public function initBase($mongoDB)
    {
        $this->mongoDB = $mongoDB;
        $this->mongoDB->collection('users');
    }

    /**
     * 获取用户列表
     */
    public function _testMongodb(Request $request)
    {
        $mongoDB = new MongoDBService();
        $this->initBase($mongoDB);

        //return $this->store($request);
        return $this->index($request);
    }

    public function index($request)
    {
        $users = $this->mongoDB->paginate(
            $request->get('page', 1),
            $request->get('per_page', 15),
            ['status' => 'active'],
            //['created_at' => -1]
        );

        return response()->json($users);
    }

    /**
     * 创建用户
     */
    public function store(Request $request)
    {
        $result = $this->mongoDB->insertOne([
            'name' => $request->name,
            'email' => $request->email,
            'status' => 'active',
            'roles' => ['user'],
        ]);

        return response()->json($result, 201);
    }

    /**
     * 获取用户详情
     */
    public function show($id)
    {
        $user = $this->mongoDB->findOne($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    /**
     * 更新用户
     */
    public function update(Request $request, $id)
    {
        $result = $this->mongoDB->updateOne($id, [
            '$set' => $request->only(['name', 'email', 'status'])
        ]);

        if ($result['matched_count'] === 0) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($result);
    }

    /**
     * 删除用户
     */
    public function destroy($id)
    {
        $result = $this->mongoDB->deleteOne($id);

        if ($result['deleted_count'] === 0) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User deleted']);
    }
    public function transferPoints(Request $request)
    {
        $session = $this->mongoDB->startTransaction();

        try {
            $this->mongoDB->collection('users')->updateOne(
                $request->from_user_id,
                ['$inc' => ['points' => -$request->amount]]
            );

            $this->mongoDB->collection('users')->updateOne(
                $request->to_user_id,
                ['$inc' => ['points' => $request->amount]]
            );

            // 记录转账日志
            $this->mongoDB->collection('transactions')->insertOne([
                'from' => $request->from_user_id,
                'to' => $request->to_user_id,
                'amount' => $request->amount,
                'created_at' => now(),
            ]);

            $this->mongoDB->commitTransaction($session);

            return response()->json(['message' => 'Transfer successful']);
        } catch (\Exception $e) {
            $this->mongoDB->abortTransaction($session);

            return response()->json(['message' => 'Transfer failed'], 500);
        }
    }

    // 在 Artisan 命令或数据库迁移中创建索引
    public function createIndexes()
    {
        $mongo = app(MongoDBService::class);

        // 创建唯一索引
        $mongo->collection('users')->createUniqueIndex('email');

        // 创建复合索引
        $mongo->collection('users')->createCompoundIndex([
            'status' => 1,
            'created_at' => -1
        ]);

        // 创建全文索引
        $mongo->collection('articles')->createTextIndex(['title', 'content']);
    }
}
