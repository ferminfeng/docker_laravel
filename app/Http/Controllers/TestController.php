<?php

namespace App\Http\Controllers;

use App\Services\TestService;
use Throwable;
use Illuminate\Http\Request;

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
            $this->fail(0, 'mysql ' . $e->getMessage());
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
            $this->fail(0, 'redis ' . $e->getMessage());
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
            $this->fail(0, 'mongo' . $e->getMessage());
        }
    }
}

