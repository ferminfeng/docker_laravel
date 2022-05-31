<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($data = [], $msg = 'success', $other = [])
    {
        apiReturn(200, $msg, $data, $other);
    }

    public function fail(int $code = 500, string $msg = '', $data = '', $other = [])
    {
        apiReturn($code, $msg, $data, $other);
    }

}
