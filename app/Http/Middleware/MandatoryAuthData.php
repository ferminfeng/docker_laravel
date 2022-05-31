<?php

namespace App\Http\Middleware;

use App\Exceptions\BaseException;
use Closure;

class MandatoryAuthData
{
    /**
     * 登录校验
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     * @author fyf
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

}
