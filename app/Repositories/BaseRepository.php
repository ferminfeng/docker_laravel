<?php

namespace App\Repositories;

use App\Libs\Constant;
use Illuminate\Support\Arr;

class BaseRepository
{
    protected $limitMax = Constant::SELECT_LIMIT_MAX;

    public function byWhere(&$handle, $key, $value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (strtolower($k) == 'between') {
                    $handle->whereBetween($key, $v);
                } elseif (strtolower($k) == 'in') {
                    $handle->whereIn($key, $v);
                } else {
                    $handle->where($key, $k, $v);
                }
            }
        } else {
            $handle->where($key, $value);
        }
    }

    public function getArray($object, array $fields = [])
    {
        if (!$object || !is_object($object)) {
            return $object;
        }
        $result = $object->toArray() ?? [];
        if (!empty($fields)) {
            array_walk($result, function (&$val) use ($fields) {
                $val = Arr::only($val, $fields);
            });
        }
        return $result;
    }

    public function getLimit(int $limit)
    {
        return $limit > $this->limitMax ? $this->limitMax : $limit;
    }
}
