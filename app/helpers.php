<?php


/**
 * 接口返回信息 json格式
 *
 * @param int $code
 * @param string $msg
 * @param array|null $data
 * @param array $other ['img_url'] 个性化图片域名
 * @author fyf
 */
function apiReturn(int $code = 0, string $msg = '', $data = null, array $other = [])
{
    switch ($code) {
        case 0:
            $msg = $msg != '' ? $msg : "请求成功";
            break;
        case 1:
            $msg = $msg != '' ? $msg : "请求失败";
            break;
        case 2:
            $msg = $msg != '' ? $msg : "参数错误";
            break;
    }

    // 将data数组值中的null换为空字符串
    if (!empty($data) && is_array($data)) {
        array_walk_recursive(
            $data,
            function (&$v) {
                is_null($v) && $v = '';
            }
        );
    }

    $result = ['code' => intval($code), 'msg' => $msg, 'data' => $data];
    $img_url = isset($other['img_url']) ? $other['img_url'] : config('app.img_url');
    unset($other['img_url']);
    if (!empty($other)) {
        $result = array_merge($result, $other);
    }
    if (!empty($result['data']) || count($result) > 3) {
        $result['img_url'] = $img_url;
    }

    $result['server_at'] = (string)time();

    cors();

    header('Content-Type:application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}

/**
 * 处理header 跨域问题
 *
 * @author fyf
 */
function cors()
{
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding");
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: *");
        }
    }
}

/**
 * 记录日志
 *
 * @param string $dirName
 * @param string $fileName
 * @param $log
 * @author fyf
 */
function saveLog(string $dirName, string $fileName, $log)
{
    $filePath = storage_path() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . DIRECTORY_SEPARATOR . $dirName;
    checkDir($filePath);

    $message = date('Y-m-d H:i:s') . "\r\n";
    $message .= json_encode($log, JSON_UNESCAPED_UNICODE) . "\r\n\r\n";

    $fileName .= '.log';
    error_log($message, 3, $filePath . DIRECTORY_SEPARATOR . $fileName);
}

/**
 * 判断是否是目录，不是则创建
 *
 * @param $path
 * @return bool
 * @author fyf
 */
function checkDir($path)
{
    // 如果目录已经存在，直接返回
    if (is_dir($path)) {
        return true;
    }

    // 如果目录不存在,创建
    //想要创建$path目录，要么$path的父目录dirname($path)存在，要么你就帮我创建父目录
    //终止条件：找到了父目录，不去创建父目录了
    return is_dir(dirname($path)) || checkDir(dirname($path)) ? mkdir($path, 0777, true) : false;
}

/**
 * 处理接口失败结果
 *
 * @param $e
 * @param $dirName
 * @param $fileName
 * @param $logArray
 * @param $isReturn
 * @author fyf
 */
function handleApiFailResult($e, $dirName, $fileName, &$logArray = [], $isReturn = true)
{
    $errCode = $e->getCode();
    $msg = $e->getMessage();
    $logArray[] = [
        'err_code' => $errCode,
        'err_msg' => $msg,
        'err_file' => $e->getFile(),
        'err_line' => $e->getLine(),
        'url' => \Illuminate\Support\Facades\URL::full(),
    ];
    if ($errCode <= 0) {
        saveLog($dirName, $fileName . '_warning', $logArray);
    } else {
        saveLog($dirName, $fileName . '_error', $logArray);
    }

    if ($errCode > 0 && !config('app.debug')) {
        $errCode = 1;
        $msg = '网络繁忙，请稍后重试';
    }
    $isReturn && apiReturn(intval($errCode), $msg);
}


/**
 * 实例化redis
 *
 * @author fyf
 */
function redisObject()
{
    //配置连接的IP、端口、以及相应的数据库
    $config = config('database.redis.default');
    $server = [
        'host' => $config['host'],
        'port' => $config['port'],
        'database' => $config['database'],
        'password' => $config['password']
    ];
    return new \Predis\Client($server);
}

