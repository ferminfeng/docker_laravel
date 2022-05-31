<?php

namespace App\Repositories;

use App\Models\City;
use App\Libs\Constant;

class CityRepository extends BaseRepository
{
    protected $model;

    public function __construct(City $city)
    {
        $this->model = $city;
    }

    public function findById(int $id, array $fields = ['*'])
    {
        $handle = $this->model->where('id', $id);

        $fields = empty($fields) ? ['*'] : $fields;
        $result = $handle->first($fields);

        return $result ? $result->toArray() : [];
    }

    public function getListByWhere(array $where, int $page = 1, int $pageSize = 10, array $fields = ['*'], $orderBy = [])
    {
        $fields = empty($fields) ? ['*'] : $fields;
        $handle = $this->model->select($fields);

        $this->search($handle, $where);

        if (isset($orderBy['default'])) {
            foreach ($orderBy['default'] as $key => $value) {
                $handle->orderBy($key, $value);
            }
        } elseif (isset($orderBy['raw'])) {
            foreach ($orderBy['raw'] as $key => $value) {
                $handle->orderByRaw($value);
            }
        } else {
            $handle->orderBy('id', 'asc');
        }

        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $handle->offset($offset)->limit($limit);

        $result = $handle->get();

        return $result ? $result->toArray() : [];
    }

    public function getTotalByWhere(array $where)
    {
        $handle = $this->model->select(['diy_id']);
        $this->search($handle, $where);
        return $handle->count() ?? 0;
    }

    public function getDataGroupBy($field)
    {
        return $this->model
            ->select($field)
            ->groupBy($field)
            ->get()
            ->toArray();
    }

    public function updateById(int $diyId, array $data)
    {
        $handle = $this->model->where('id', $diyId);
        return $handle->update($data);
    }

    public function createData(array $data)
    {
        $handle = $this->model;
        return $handle->create($data);
    }

    /**
     * 筛选
     *
     * @param $handle
     * @param $params
     * @author fyf
     */
    private function search($handle, $params)
    {
        // 根据ID搜索
        if (isset($params['id']) && !empty($params['id'])) {
            $handle->whereIn('id', $params['id']);
        }

        // 名称
        if (isset($params['search']) && !empty($params['search'])) {
            $handle->where(function ($query) use ($params) {
                $query->where('name', 'like', '%' . $params['search'] . '%');
            });
        }

    }

}
