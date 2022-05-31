<?php

namespace App\Extend;

use Throwable;

/**
 * redis操作类，主要是捕获异常
 *
 * @author fyf
 * Class RedisOperation
 * @package App\Extend
 */
class RedisOperation
{

    /**
     * select 切换数据库
     *
     * @param $redis
     * @param $key
     * @return string
     * @author fyf
     */
    public static function select($redis, $key)
    {
        $logArray[] = 'select';
        try {
            return $redis->select($key);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * get
     *
     * @param $redis
     * @param $key
     * @return string
     * @author fyf
     */
    public static function get($redis, $key)
    {
        $logArray[] = 'get';
        try {
            return $redis->get($key); //表示存储有效期为10秒
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * set
     *
     * @param $redis
     * @param $key
     * @param $val
     * @return mixed
     * @author fyf
     */
    public static function set($redis, $key, $val)
    {
        $logArray[] = 'set';
        try {
            return $redis->set($key, $val);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * set一个存储时效
     *
     * @param $redis
     * @param $key
     * @param $second
     * @param $val
     * @return mixed
     * @author fyf
     */
    public static function setex($redis, $key, $second, $val)
    {
        $logArray[] = 'setex';
        try {
            return $redis->setex($key, $second, $val); //表示存储有效期为10秒
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 取出表 $tableKey 中的key $field的值,返回'v1'
     *
     * @param $redis
     * @param $tableKey
     * @param $field
     * @return string
     * @author fyf
     */
    public static function hget($redis, $tableKey, $field)
    {
        $logArray[] = 'hget';
        try {
            return $redis->hget($tableKey, $field);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 取出表 $tableKey 中的所有数据
     *
     * @param $redis
     * @param $tableKey
     * @return string
     * @author fyf
     */
    public static function hgetall($redis, $tableKey)
    {
        $logArray[] = 'hget';
        try {
            return $redis->hgetall($tableKey);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 从hash表取多个元素
     * $redis->hmget('hash1',array('key3','key4')); //返回相应的值 array('v3','v4')
     *
     * @param $redis
     * @param $tableKey
     * @param $moreField
     * @return string
     * @author fyf
     */
    public static function hmget($redis, $tableKey, $moreField)
    {
        $logArray[] = 'hmget';
        try {
            return $redis->hmget($tableKey, $moreField);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 将key为$field value为$value的元素存入$tableKey表
     *
     * @param $redis
     * @param $tableKey
     * @param $field
     * @param $value
     * @return string
     * @author fyf
     */
    public static function hset($redis, $tableKey, $field, $value)
    {
        $logArray[] = 'hset';
        try {
            return $redis->hset($tableKey, $field, $value);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * hdel 删除hash表中指定key的元素
     *
     * @param $redis
     * @param $tableKey
     * @param $field
     * @return string
     * @author fyf
     */
    public static function hdel($redis, $tableKey, $field)
    {
        $logArray[] = 'hdel';
        try {
            return $redis->hdel($tableKey, $field);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * del 删除整张表
     *
     * @param $redis
     * @param $tableKey
     * @return string
     * @author fyf
     */
    public static function del($redis, $tableKey)
    {
        $logArray[] = 'del';
        try {
            return $redis->del($tableKey);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 存多个元素到hash表
     * $redis->hmset('hash1',array('key3'=>'v3','key4'=>'v4'));
     *
     * @param $redis
     * @param $tableKey
     * @param $moreField
     * @return string
     * @author fyf
     */
    public static function hmset($redis, $tableKey, $moreField)
    {
        $logArray[] = 'hmset';
        try {
            return $redis->hmset($tableKey, $moreField);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 对指定key进行累加, $increment值可为负数，表明对指定key进行减操作
     *
     * @param $redis
     * @param $tableKey
     * @param $field
     * @param $increment
     * @return string
     * @author fyf
     */
    public static function hincrby($redis, $tableKey, $field, $increment)
    {
        $logArray[] = 'hincrby';
        try {
            return $redis->hincrby($tableKey, $field, $increment);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 有序set表操作
     */

    /**
     * sadd 增加元素,并设置序号,返回true,重复返回false
     *
     * @param $redis
     * @param $tableKey
     * @param $value
     * @param $field
     * @return string
     * @author fyf
     */
    public static function zadd($redis, $tableKey, $value, $field)
    {
        $logArray[] = 'zadd';
        try {
            return $redis->zadd($tableKey, $value, $field);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * zincrby 对指定元素索引值的增减,改变元素排列次序
     *
     * @param $redis
     * @param $tableKey
     * @param $value
     * @param $field
     * @return string
     * @author fyf
     */
    public static function zincrby($redis, $tableKey, $value, $field)
    {
        $logArray[] = 'zincrby';
        try {
            return $redis->zincrby($tableKey, $value, $field);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * zrem 移除指定元素
     *
     * @param $redis
     * @param $tableKey
     * @param $field
     * @return string
     * @author fyf
     */
    public static function zrem($redis, $tableKey, $field)
    {
        $logArray[] = 'zrem';
        try {
            return $redis->zrem($tableKey, $field);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * zrange 按位置次序返回表中指定区间的元素
     *
     * @param $redis
     * @param $tableKey
     * @param $start
     * @param $end
     * @return string
     * @author fyf
     */
    public static function zrange($redis, $tableKey, $start, $end)
    {
        $logArray[] = 'zrange';
        try {
            return $redis->zrange($tableKey, $start, $end);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * zrevrange 同上,返回表中指定区间的元素,按次序倒排
     * 元素顺序和zrange相反
     *
     * @param $redis
     * @param $tableKey
     * @param $start
     * @param $end
     * @return string
     * @author fyf
     */
    public static function zrevrange($redis, $tableKey, $start, $end)
    {
        $logArray[] = 'zrevrange';
        try {
            return $redis->zrevrange($tableKey, $start, $end);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * zscore 查询元素的索引
     *
     * @param $redis
     * @param $tableKey
     * @param $key
     * @return string
     * @author fyf
     */
    public static function zscore($redis, $tableKey, $key)
    {
        $logArray[] = 'zscore';
        try {
            return $redis->zscore($tableKey, $key);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * zremrangebyrank 删除表中指定位置区间的元素
     *
     * @param $redis
     * @param $tableKey
     * @param $start
     * @param $end
     * @return string
     * @author fyf
     */
    public static function zremrangebyrank($redis, $tableKey, $start, $end)
    {
        $logArray[] = 'zremrangebyrank';
        try {
            return $redis->zremrangebyrank($tableKey, $start, $end);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 将一个值插入到已存在的列表头部
     *
     * @param $redis
     * @param $tableKey
     * @param $value
     * @return string
     * @author fyf
     */
    public static function lpush($redis, $tableKey, $value)
    {
        $logArray[] = 'lpush';
        try {
            return $redis->lpush($tableKey, $value);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 将一个值插入到已存在的列表尾部
     *
     * @param $redis
     * @param $tableKey
     * @param $value
     * @return string
     * @author fyf
     */
    public static function rpush($redis, $tableKey, $value)
    {
        $logArray[] = 'rpush';
        try {
            return $redis->rpush($tableKey, $value);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 获取列表指定范围内的元素
     *
     * @param $redis
     * @param $tableKey
     * @param $start
     * @param $end
     * @return string
     * @author fyf
     */
    public static function lrange($redis, $tableKey, $start, $end)
    {
        $logArray[] = 'lrange';
        try {
            return $redis->lrange($tableKey, $start, $end);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }

    /**
     * 获取列表长度
     *
     * @param $redis
     * @param $tableKey
     * @return string
     * @author fyf
     */
    public static function llen($redis, $tableKey)
    {
        $logArray[] = 'llen';
        try {
            return $redis->llen($tableKey);
        } catch (Throwable $e) {
            handleApiFailResult($e, 'redis', 'redis', $logArray, false);
            return '';
        }
    }


}
