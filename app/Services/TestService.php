<?php

namespace App\Services;

use Exception;
use App\Extend\RedisOperation;
use App\Repositories\CityRepository;
use Illuminate\Support\Facades\DB;

class TestService
{
    protected $cityRepository;
    protected $redisObject;
    protected $redisOperation;

    public function __construct(CityRepository $cityRepository, RedisOperation $redisOperation)
    {
        $this->cityRepository = $cityRepository;
        $this->redisObject = redisObject();
        $this->redisOperation = $redisOperation;
    }

    /**
     * 测试mysql
     *
     * @return array
     * @author fyf
     */
    public function testMysql() : array
    {
        return $this->cityRepository->getListByWhere([]);
    }

    /**
     * 测试redis
     *
     * @return array
     * @throws Exception
     * @author fyf
     */
    public function testRedis() : array
    {
//        $cityList = $this->testMysql();
        $cityList = [
            [
                'id' => 1,
                'name' => '武汉市',
            ],
            [
                'id' => 2,
                'name' => '上海市',
            ],
        ];

        $tableKey = 'city';

        $this->redisOperation->del($this->redisObject, $tableKey);

        foreach ($cityList as $cityInfo) {
            $this->redisOperation->hset($this->redisObject, $tableKey, $cityInfo['id'], $cityInfo['name']);
        }

        $redisCityList = $this->redisOperation->hgetall($this->redisObject, $tableKey);
        if (!is_array($redisCityList)) {
            throw new Exception('从redis没有拿到数据');
        } else {
            return $redisCityList;
        }
    }

    public function testMongo() : array
    {

        DB::connection('mongodb')       //选择使用mongodb
        ->collection('users')           //选择使用users集合
        ->insert([                          //插入数据
            'name' => 'tom',
            'age' => 18
        ]);

        $res = DB::connection('mongodb')->collection('phone')->get()->toArray();   //查询所有数据

        return $res;

    }
}
