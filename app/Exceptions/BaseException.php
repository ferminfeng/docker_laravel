<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class BaseException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    protected static $codeMsgList = [
        // Global
        0 => '请求成功',
        1 => '请求失败',
        2 => '参数错误',
        3 => '操作频繁，请稍后重试',
    ];

    public static function getCodeMsg($code)
    {
        return (array_key_exists($code, self::$codeMsgList)) ? self::$codeMsgList[$code] : "未定义的错误:{$code}";
    }
}
