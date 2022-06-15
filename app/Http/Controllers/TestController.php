<?php

namespace App\Http\Controllers;

use App\Jobs\TestQueueJob;
use App\Services\TestService;
use Throwable;

class TestController extends Controller
{
    protected $testService;

    public function __construct(TestService $testService)
    {
        $this->testService = $testService;
    }

    /**
     * index
     *
     * @author fyf
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * mysql
     *
     * @author fyf
     */
    public function testMysql()
    {
        try {
            $data = $this->testService->testMysql();
            $this->success($data, 'mysql-success');
        } catch (Throwable $e) {
            $this->fail(500, 'mysql ' . $e->getMessage());
        }
    }

    /**
     * redis
     *
     * @author fyf
     */
    public function testRedis()
    {
        try {
            $data = $this->testService->testRedis();
            $this->success($data, 'redis-success');
        } catch (Throwable $e) {
            $this->fail(500, 'redis ' . $e->getMessage());
        }
    }

    /**
     * mongo
     *
     * @author fyf
     */
    public function testMongo()
    {
        try {
            $data = $this->testService->testMongo();
            $this->success($data, 'mongo-success');
        } catch (Throwable $e) {
            $this->fail(500, 'mongo' . $e->getMessage());
        }
    }

    /**
     * 发送队列
     *
     * @author fyf
     */
    public function sendQueue()
    {
        try {
            $params = [
                'test_key' => 111
            ];

            TestQueueJob::dispatch($params);
            $this->success();
        } catch (Throwable $e) {
            $this->fail(500, 'sendQueue' . $e->getMessage());
        }
    }
}

