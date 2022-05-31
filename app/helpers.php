<?php

use \App\Extend\RedisOperation;
use \App\Exceptions\BaseException;
use App\Libs\Constant;
use App\Models\Users;
use Overtrue\Pinyin\Pinyin;

/**
 * +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 * +----------------------------------------------------------
 * @static
 * @access public
 * +----------------------------------------------------------
 *
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param boolean $title “...”处鼠标指向时是否显示详细内容
 * @param string $charset 编码格式
 * @param boolean $suffix 截断显示字符
 * +----------------------------------------------------------
 *
 * @return string
 * +----------------------------------------------------------
 */
function m_substr($str, $start, $length, $title = true, $charset = "utf-8", $suffix = true)
{
    $strlen = strlen($str) / 3;
    if ($strlen <= $length) {
        return $str;
    }
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    $suffix_str = ($title == true) ? " <a href='javascript:;' title='{$str}'>...</a>" : " ...";

    return $suffix ? $slice . $suffix_str : $slice;
}

/**
 * 发送钉钉消息
 *
 * @param string $title 标题
 * @param array|string $message 消息
 * @param bool $isAtAll 是否@所有人
 * @param array $atMobiles 被@人手机号
 * @param array $ddConfig
 * @param bool $silent 是否开启一分钟沉默
 * @return bool|array
 * @author fyf
 */
function sendDingDing(string $title = '', $message = '', bool $isAtAll = false, array $atMobiles = [], array $ddConfig = [], bool $silent = true)
{
    try {
        if (!$title) {
            throw new \App\Exceptions\BaseException('title不能为空');
        }

        // 相同$title一分钟沉默
        if ($silent) {
            $redisObject = redisObject();
            $redis = new \App\Extend\RedisOperation();
            $redisKey = md5($title);
            $isSilent = $redis->get($redisObject, 'sendDingDing', $redisKey);
            if ($isSilent) {
                return true;
            }
        }

        if (empty($ddConfig)) {
            $ddConfig = config('app.dd_error');
        }
        $accessToken = $ddConfig['access_token'];
        $secret = $ddConfig['secret'];

        if (empty($accessToken) || empty($secret)) {
            throw new \App\Exceptions\BaseException('access_token或secret未设置');
        }

        $time = time() * 1000;
        $strToSign = $time . "\n" . $secret;
        $sign = urlencode(base64_encode(hash_hmac('sha256', $strToSign, $secret, true)));
        $url = "https://oapi.dingtalk.com/robot/send?access_token=" . $accessToken . '&timestamp=' . $time . '&sign=' . $sign;

        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        $fromIp = getIp();

        $content = $title;
        $content .= "\n\n" . $message;
        $content .= "\n\n运行环境:" . env('APP_ENV');
        $content .= "\n时间:" . date('Y-m-d H:i:s');
        $content .= "\nIP:" . $fromIp;

        $postData = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ],
            'at' => [
                'isAtAll' => $isAtAll,
            ],
        ];
        if (is_array($atMobiles) && count($atMobiles) > 0) {
            $postData['at']['atMobiles'] = $atMobiles;
        }
        $postString = json_encode($postData);

        $curlResult = curlPost($url, $postString, true);

        $result = json_decode($curlResult, true);

        $redis->setex($redisObject, 'sendDingDing', 60, $redisKey);
        return $result;
    } catch (\Exception $e) {
        $logArray[] = [
            'err_code' => $e->getCode(),
            'err_msg' => $e->getMessage(),
            'err_file' => $e->getFile(),
            'err_line' => $e->getLine(),
        ];
        return false;
    }
}

/**
 * curlPost
 *
 * @param string $url
 * @param string $dataString
 * @param bool $isCatch
 * @return bool|string
 * @throws \App\Exceptions\BaseException
 * @author fyf
 */
function curlPost(string $url, string $dataString, bool $isCatch = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-AjaxPro-Method:ShowList',
        'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36',
        'Content-Type: application/json;charset=utf-8'
    ]);
//    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json;charset=utf-8']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    // 在尝试连接时等待的秒数
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    // 最大执行时间
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $data = curl_exec($ch);
    if (false === $data && true == $isCatch) {
        $msg = 'Curl error: ' . curl_error($ch) . PHP_EOL . 'Url: ' . $url;
        throw new \App\Exceptions\BaseException($msg);
    }
    curl_close($ch);

    return $data;
}

function curlGet(string $url, array $params)
{
    $urlParams = "";
    foreach ($params as $key => $val) {
        $urlParams .= $key . '=' . $val . '&';
    }
    $fullUrl = $url . '?' . rtrim($urlParams, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    if (curl_error($ch)) {
        $msg = 'Curl error: ' . curl_error($ch) . PHP_EOL . 'Url: ' . $url;
        throw new BaseException($msg);
    }
    curl_close($ch);

    return $data;
}

function curlPut(string $url, string $params)
{
    $ch = curl_init($url);
    $header = "Content-Type: multipart/form-data; boundary='123456f'";
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $data = curl_exec($ch);
    if (curl_error($ch)) {
        $msg = 'Curl error: ' . curl_error($ch) . PHP_EOL . 'Url: ' . $url;
        throw new BaseException($msg);
    }
    curl_close($ch);

    return $data;
}

/**
 * 记录、发送错误信息
 *
 * @param string $key
 * @param int $level 1:紧急(发送消息) 2:一般(只记录错误信息)
 * @param array $info
 * @return bool
 * @author fyf
 */
function handleErrorInfo(string $key, int $level = 2, array $info = []) : bool
{
    if ($key == '' || empty($info)) {
        return false;
    }

    $insertRedisStatus = true;

    //发送消息
    $sendDingDingStatus = ['errmsg' => 'ok'];
    if ($level == '1') {
        $sendDingDingStatus = sendDingDing($key, $info);
    }

    return $insertRedisStatus && $sendDingDingStatus['errmsg'] == 'ok';
}

/**
 * 把数字1-1亿换成汉字表述，如：123->一百二十三
 *
 * @param string $num
 * @return string
 * @author fyf
 */
function numToZh(string $num) : string
{
    $chiNum = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
    $chiUni = ['', '十', '百', '千', '万', '亿', '十', '百', '千'];

    $num_str = (string)$num;
    if ((!$num_str && $num_str != '0') || $num_str < '0') {
        return '';
    }

    if ($num_str == '2') {
        return '两';
    }

    $count = strlen($num_str);
    $last_flag = true; //上一个 是否为0
    $zero_flag = true; //是否第一个
    $temp_num = null; //临时数字

    $chiStr = ''; //拼接结果
    if ($count == 2) {
        //两位数
        $temp_num = $num_str[0];
        $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
        $temp_num = $num_str[1];
        $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
    } else if ($count > 2) {
        $index = 0;
        for ($i = $count - 1; $i >= 0; $i--) {
            $temp_num = $num_str[$i];
            if ($temp_num == 0) {
                if (!$zero_flag && !$last_flag) {
                    $chiStr = $chiNum[$temp_num] . $chiStr;
                    $last_flag = true;
                }
            } else {
                $chiStr = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;

                $zero_flag = false;
                $last_flag = false;
            }
            $index++;
        }
    } else {
        $chiStr = $chiNum[$num_str[0]];
    }

    return $chiStr;
}

/**
 * 将日期转换成周数
 *
 * @param string $date
 * @param bool $isTimestamp
 * @return string
 * @author fyf
 */
function getDateWeek(string $date, bool $isTimestamp = true) : string
{
    if (!$isTimestamp) {
        $date = strtotime($date);
    }
    $week = '周' . config('week_str')[date('N', $date)];

    return $week;
}

/**
 * 屏蔽emoji表情
 *
 * @param string $content
 * @return string
 * @author fyf
 */
function yzEmojiExpression(string $content = '') : string
{
    $mbLen = mb_strlen($content);

    $strArr = [];
    for ($i = 0; $i < $mbLen; $i++) {
        $mbSubstr = mb_substr($content, $i, 1, 'utf-8');
        if (strlen($mbSubstr) >= 4) {
            continue;
        }
        $strArr[] = $mbSubstr;
    }

    return implode('', $strArr);
}

/**
 * 生成用户名
 *
 * @return string
 * @author fyf
 */
function createUserName() : string
{
    usleep(1);
    list($usec, $sec) = explode(".", microtime(true));
    mt_srand((double)microtime() * 1000000);
    $member_name = date('YmdHis', $usec) . str_pad($sec, 4, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    $member_name = 'nook' . $member_name;

    return $member_name;
}

/**
 * 取汉字的第一个字的首字母
 *
 * @param string $str
 * @return string
 * @author fyf
 */
function getFirstCharter(string $str) : string
{
    if (empty($str)) {
        return '';
    }
    $fchar = ord($str[0]);
    if ($fchar >= ord('A') && $fchar <= ord('z')) {
        return strtoupper($str[0]);
    }
//    $s1 = iconv('UTF-8', 'GBK', $str);
    $s1 = mb_convert_encoding($str, 'GBK', 'UTF-8');

//    $s2 = iconv('GBK', 'UTF-8', $s1);

    $s2 = mb_convert_encoding($s1, 'UTF-8', 'GBK');

    $s = $s2 == $str ? $s1 : $str;
    $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if (($asc >= -19775 && $asc <= -19219) || $asc == '-8003') return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if (($asc >= -18526 && $asc <= -18240) || $asc == '-3931') return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if (($asc >= -17922 && $asc <= -17418) || $asc == '-6150') return 'H';
    if ($asc >= -17417 && $asc <= -16475 || $asc == '-6752' || $asc == '-6981') return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if (($asc >= -15640 && $asc <= -15166) || $asc == '-8968') return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if (($asc >= -14630 && $asc <= -14150) || $asc == '-3409' || $asc == '-6747') return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319 || $asc == '-6984') return 'S';
    if (($asc >= -13318 && $asc <= -12839) || $asc == '-6756') return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if (($asc >= -12556 && $asc <= -11848) || $asc == '-4869' || $asc == '-5441' || $asc == '-6757' || $asc == '-6446') return 'X';
    if (($asc >= -11847 && $asc <= -11056) || $asc == '-8526') return 'Y';
    if (($asc >= -11055 && $asc <= -10247) || $asc == '-5717' || $asc == '-8977') return 'Z';

    return '';
}

/**
 * 获取IP
 *
 * @return array|false|string
 * @author fyf
 */
function getIp()
{
    global $ip;
    if (getenv("HTTP_CLIENT_IP")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR")) {
        $ip = getenv("REMOTE_ADDR");
    } else {
        $ip = "Unknow IP";
    }

    return $ip;
}

/**
 * 去除换行符、空格符
 *
 * @param string $char
 * @return mixed|string
 * @author fyf
 */
function replace_char(string $char)
{
    if (is_scalar($char)) {
        $char = str_replace(["\r\n", "\r", "\n"], "", $char);
        $char = $char ? addslashes($char) : $char;
    }

    return $char;
}

/**
 * 标准返回函数
 *
 * @param int $code
 * @param string $msg
 * @param null $data
 * @return array
 * @author fyf
 */
function getResponse(int $code = 0, string $msg = "", $data = null)
{
    return $data === null ? ['code' => $code, 'msg' => $msg, 'data' => ''] : ['code' => $code, 'msg' => $msg, 'data' => $data];
}

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
 * 接口直接返回json格式
 *
 * @param array $data
 * @author fyf
 */
function apiDirectReturn(array $data)
{
    cors();

    header('Content-Type:application/json; charset=utf-8');
    echo json_encode($data);
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
 * 敏感词过滤
 *
 * @param string $string
 * @return string
 * @author fyf
 */
function filterWord(string $string) : string
{
    $file_name = "./sensitive_words.txt";
    if (!($words = file_get_contents($file_name))) {
        die('file read error!');
    }
    $word = preg_replace("/[1,2,3]\r\n|\r\n/i", '', $words);
    $matched = preg_replace('/' . $word . '/i', '***', $string);
    $matched = preg_replace("/(href=\\\")([^\\\"]+)/s", "href=\"#", $matched);
    $matched = preg_replace("/(href=\\\')([^\\\']+)/s", "href=\'#", $matched);
    return $matched;
}

/**
 * 内容解码显示（用于解决Emoji表情问题）
 *
 * @param string $content
 * @return string
 * @author fyf
 */
function decodeContent(string $content) : string
{
    //return stripslashes(json_decode($content));
    return json_decode($content);
}

/**
 * 内容转码存储（用于解决Emoji表情问题）
 *
 * @param string $content
 * @return string
 * @author fyf
 */
function encodeContent(string $content) : string
{
//    return addslashes(json_encode($content));
    return json_encode($content);
}

/**
 * MySQL模糊搜索转码处理
 *
 * @param string $content
 * @return string
 * @author fyf
 */
function likeSearchContent(string $content) : string
{
    return trim(str_replace("\\", "\\\\\\\\", json_encode($content)), '"');
}

/**
 * AES加密
 *
 * @param string $data
 * @param string $key 加密key
 * @param string $iv 偏移量
 *
 * @return string
 */
function aesEncode(string $data, string $key, string $iv) : string
{
    return base64_encode(openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv));
}

/**
 * AES解密
 *
 * @param string $data
 * @param string $key 加密key
 * @param string $iv 偏移量
 *
 * @return string
 */
function aesDecode(string $data, string $key, string $iv) : string
{
    return openssl_decrypt(base64_decode($data), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

/**
 * 对密码加盐
 *
 * @param string $password
 * @param string $safeCode
 * @return string
 * @author fyf
 */
function getPassword(string $password, string $safeCode) : string
{
    return md5(md5($password) . $safeCode);
}

/**
 * 1天内的时间显示分钟、小时
 * 2天内显示 昨天、分钟、小时
 * 其他显示年月日时分
 *
 * @param int $showTime
 * @return string
 * @author fyf
 */
function timeTran(int $showTime) : string
{
    if ($showTime <= 0) {
        return '';
    }

    $nowTime = time();
    $dur = $nowTime - $showTime;
    if ($dur < 0) {
        return date('Y-m-d H:i', $showTime);
    } else {
        //今天开始时间
        $nowDateStartTime = strtotime(date('Y-m-d'));
        if ($showTime >= $nowDateStartTime) {
            return date('H:i', $showTime);
        } else {
            //昨天开始时间
            $lastDateStartTime = strtotime(date('Y-m-d')) - 86400;
            if ($showTime >= $lastDateStartTime) {
                return '昨天 ' . date('H:i', $showTime);
            } else {
                if (date('Y', $showTime) == date('Y')) {
                    return date('m-d H:i', $showTime);
                } else {
                    return date('Y-m-d H:i', $showTime);
                }
            }
        }
    }
}


/**
 * 计算间隔时间
 * 一分钟内显示多少秒
 * 一小时内显示多少分钟多少秒
 * 一小时以上显示小时
 *
 * @param int $startTime
 * @param int $endTime
 * @return string
 * @author fyf
 */
function getIntervalTime(int $startTime, int $endTime) : string
{

    if ($startTime <= 0 || $endTime <= 0) {
        return '';
    }

    $dur = $endTime - $startTime;
    if ($dur < 0) {
        $text = '';
    } else {
        if ($dur < 60) {
            $text = $dur . '秒';
        } elseif ($dur < 3600) {
            $text = floor($dur / 60) . '分钟';
        } else {
            $text = floor($dur / 3600) . '小时';
        }
    }

    return $text;
}

/**
 * 返回一个按照首字母分组的数组
 *
 * @param array $list
 * @param string $key
 * @return array
 * @author fyf
 */
function handleGetFirstCharter(array $list, string $key = '') : array
{

    $data = [];
    foreach ($list as $val) {
        $firstChar = getFirstCharter($val[$key]);
        $firstChar = $firstChar ? $firstChar : '#';
        $data[$firstChar][] = $val;
    }

    ksort($data);

    $other = [];
    if (isset($data['#'])) {
        $other = $data['#'];
        unset($data['#']);
    }

    $newData = [];
    foreach ($data as $k => $v) {
        $newData[] = [
            'name' => $k,
            'content' => $v,
        ];
    }

    if ($other) {
        $newData[] = [
            'name' => '#',
            'content' => $other
        ];
    }
    return $newData;
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

    // todo 前期在同一个文件里再记一份
    saveLog('', 'all_error', $logArray);

    if ($errCode > 0 && !config('app.debug')) {
        $errCode = 1;
        $msg = '网络繁忙，请稍后重试';
    }
    $isReturn && apiReturn(intval($errCode), $msg);
}

/**
 * 生成用户头像名称
 *
 * @param string $username
 * @param int $uId
 * @return string
 * @author fyf
 */
function createUserAvatarFileName(string $username, int $uId) : string
{
    return md5($username . $uId);
}

/**
 * 用户token-加密
 *
 * @param array $userInfo
 * @return string
 * @author fyf
 */
function encryptUserToken(array $userInfo) : string
{
    return encrypt(json_encode([
        'username' => $userInfo['username'],
        'safe_code' => $userInfo['safe_code'],
    ]));
}

/**
 * 用户token-解密
 *
 * @param string $token
 * @return array
 * @author fyf
 */
function decryptUserToken(string $token) : array
{
    return json_decode(decrypt($token), true);
}

/**
 * 用户 jwt token-加密
 *
 * @param array $userInfo
 * @return string
 * @author fyf
 */
function encodeJwtUserToken(array $userInfo)
{
    $key = str_replace('base64:', '', env('APP_KEY'));
    $nowTime = time();
    $payload = [
        'iss' => 'nook', // jwt的签发者/发行人
        'aud' => 'wx_app', // 接收方
        'iat' => $nowTime, // 签发时间
        'nbf' => $nowTime, // jwt生效时间
        'exp' => $nowTime + config('app.login_timeout'), // jwt过期时间
        'sub' => '', // 主题
        'jti' => '', //jwt唯一身份标识，可以避免重放攻击
        'uid' => $userInfo['uid'],
        'username' => $userInfo['username'],
        'safe_code' => $userInfo['safe_code'],
    ];

    // 生成 token
    $jwt = new \Firebase\JWT\JWT();
    $token = $jwt::encode($payload, $key);
    return $token;
}

/**
 * 用户 jwt token-解析
 *
 * @param string $token
 * @return array
 * @author fyf
 */
function decodeJwtUserToken(string $token)
{
    $key = str_replace('base64:', '', env('APP_KEY'));

    $jwt = new \Firebase\JWT\JWT();
    // 设置 有效时长 单位秒
//    $jwt::$leeway = 60;
    $token = $jwt::decode($token, $key, ['HS256']);
    return (array)$token;
}


/**
 * 服务端交互 jwt token-加密
 *
 * @return string
 * @author fyf
 */
function encodeJwtTokenForServer()
{
    $key = env('APP_SERVICE_JWT_TOKEN_KEY');
    $nowTime = time();
    $payload = [
        'iss' => 'nook_test', // jwt的签发者/发行人
        'aud' => 'php_server', // 接收方
        'iat' => $nowTime, // 签发时间
        'nbf' => $nowTime, // jwt生效时间
        'exp' => $nowTime + 60, // jwt过期时间
        'sub' => '', // 主题
        'jti' => '', //jwt唯一身份标识，可以避免重放攻击
    ];

    // 生成 token
    $jwt = new \Firebase\JWT\JWT();
    $token = $jwt::encode($payload, $key);
    return $token;
}

/**
 * 服务端交互 jwt token-解析
 *
 * @param string $token
 * @return array
 * @author fyf
 */
function decodeJwtTokenForServer(string $token)
{
    $key = env('APP_SERVICE_JWT_TOKEN_KEY');

    $jwt = new \Firebase\JWT\JWT();
    $token = $jwt::decode($token, $key, ['HS256']);
    return (array)$token;
}

/**
 * 验证远程文件是否存在
 *
 * @param $url
 * @return bool
 * @author fyf
 */
function checkFileExists($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 200) {
        return true;
    }
    return false;
}

/**
 * 设置布隆过滤器，额，目前只是个假的名字
 *
 * @param string $key
 * @param string $data
 * @param int $expire
 * @return mixed
 * @author fyf
 */
function setBloomFilter(string $key, string $data, int $expire = 1)
{
    try {
        $redisObj = redisObject();
        $res = $redisObj->setex($key, $expire, $data);
        return $res;
    } catch (\Exception $e) {
//        $logArray[] = [
//            'err_code' => $e->getCode(),
//            'err_msg' => $e->getMessage(),
//            'err_file' => $e->getFile(),
//            'err_line' => $e->getLine(),
//        ];
//        print_r($logArray);
//        die;
        return false;
    }
}

/**
 * 添加到集合中
 */
function setBloomFilterNew($string)
{
    try {
        $bucket = 'rptc';
        $hashFunction = [
            'BKDRHash',
            'SDBMHash',
            'JSHash'
        ];
        $redisObj = redisObject(); //假设这里你已经连接好了
        $hash = new \App\Extend\bloomFilter\BloomFilterHash();
        $pipe = $redisObj::multi();
        foreach ($hashFunction as $function) {
            $hash = $hash->$function($string);
            $pipe->setBit($bucket, $hash, 1);
        }
        return $pipe->exec();
    } catch (\Exception $e) {
        $logArray[] = [
            'err_code' => $e->getCode(),
            'err_msg' => $e->getMessage(),
            'err_file' => $e->getFile(),
            'err_line' => $e->getLine(),
        ];
        print_r($logArray);
        die;
        return false;
    }
}

/**
 * 布隆过滤器 校验
 *
 * @param string $key
 * @return mixed
 * @author fyf
 */
function getBloomFilter(string $key)
{
    try {
        $redisObj = redisObject();
        $res = $redisObj->get($key);
        return $res;
    } catch (\Exception $exception) {
        return false;
    }
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
//    return new \Illuminate\Support\Facades\Redis();
}


/**
 * 获取缓存中的access_token
 *
 * @param string $accessTokenKey
 * @param bool $useRedis
 * @return bool|false|string
 * @author fyf
 */
function getCacheAccessToken(string $accessTokenKey = 'access_token', bool &$useRedis = true)
{
    // 获取缓存内的access_token
    try {
        if (!$useRedis) {
            throw new \App\Exceptions\BaseException('从文件获取access_token');
        }
        $redisObj = redisObject();
        $redisAccessToken = $redisObj->get($accessTokenKey);
        if ($redisAccessToken) {
            $redisAccessTokenArray = json_decode($redisAccessToken, true);
            if (time() <= $redisAccessTokenArray['failure_time']) {
                return (string)$redisAccessTokenArray['access_token'];
            }
        }
        return false;
    } catch (\Exception $e) {
        $logArray[] = [
            'err_code' => $e->getCode(),
            'err_msg' => $e->getMessage(),
            'err_file' => $e->getFile(),
            'err_line' => $e->getLine(),
        ];

        $useRedis = false;
        // 异常时从文件中获取access_token
        return getFileAccessToken();
    }
}

/**
 * 从文件中获取access_token
 *
 * @return false|string
 * @author fyf
 */
function getFileAccessToken()
{
    $fileName = 'access_token.log';
    $filePath = storage_path() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $fileName;

    if (!file_exists($filePath)) {
        return false;
    }
    $fileData = file_get_contents($filePath);
    if ($fileData) {
        $accessTokenArray = json_decode($fileData, true);
        if (time() <= $accessTokenArray['failure_time']) {
            return (string)$accessTokenArray['access_token'];
        }
    }
    return false;
}

/**
 * 设置access_token
 *
 * @param string $accessTokenKey
 * @param string $accessToken
 * @param bool $useRedis
 * @param int $failureTime
 * @return bool|int
 * @author fyf
 */
function setCacheAccessToken(string $accessTokenKey = 'access_token', string $accessToken, bool $useRedis = true, int $failureTime = -1)
{

    $failureTime = $failureTime == -1 ? 3600 * 2 - 180 : $failureTime;
    $accessTokenArray = [
        'access_token' => $accessToken,
        'failure_time' => time() + $failureTime
    ];

    // 获取缓存内的access_token
    try {
        if (!$useRedis) {
            throw new \App\Exceptions\BaseException('将access_token保存进文件');
        }

        $redisObj = redisObject();
        $redisAccessToken = $redisObj->setex($accessTokenKey, $failureTime, json_encode($accessTokenArray));
        if ('ok' == $redisAccessToken) {
            return true;
        }
        return false;
    } catch (\Exception $exception) {
        // 异常时access_token保存进文件

        $filePath = storage_path() . DIRECTORY_SEPARATOR . 'logs';
        checkDir($filePath);
        $fileName = 'access_token.log';

        $accessTokenData = json_encode($accessTokenArray);

        $file = fopen($filePath . DIRECTORY_SEPARATOR . $fileName, 'w');
        $result = fwrite($file, $accessTokenData);
        fclose($file);
        return $result;
    }
}

/**
 * 处理需要返回给前端的头像地址
 *
 * @param string $avatar
 * @return mixed|string
 * @author fyf
 */
function returnUserAvatar(string $avatar)
{
    if (empty($avatar)) {
        $returnAvatar = config('app.img_url') . config('app.default_avatar');
    } else {
        if (0 === strpos($avatar, 'http')) {
            $returnAvatar = $avatar;
        } else {
            $returnAvatar = config('app.img_url') . $avatar;
        }
    }
    return $returnAvatar;
}

/**
 * 获取用户头像
 *
 * @param array $userInfo
 * @return mixed|string
 * @author fyf
 */
function getUserAvatar(array $userInfo)
{
    if (isset($userInfo['avatar']) && !empty($userInfo['avatar'])) {
        $returnAvatar = $userInfo['avatar'];
    } elseif (isset($userInfo['wechat_avatar']) && !empty($userInfo['wechat_avatar'])) {
        $returnAvatar = $userInfo['wechat_avatar'];
    } else {
        $returnAvatar = config('app.default_avatar');
    }
    return $returnAvatar;
}

/**
 * 生成out_sn
 *
 * @return string
 * @author fyf
 */
function createOutSn()
{
    list($usec, $sec) = explode(".", microtime(true));
    mt_srand((double)microtime() * 1000000);
    $outSn = date('YmdHis', $usec) . str_pad($sec, 4, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    $first = 1;
    $second = 1;
    $outSn = $first . $second . $outSn;
    return $outSn;
}

/**
 * 处理并发
 *
 * @param $key
 * @throws BaseException
 * @author fyf
 */
function processingConcurrency($key)
{
    $redisObject = redisObject();
    RedisOperation::select($redisObject, 1); // 使用1库
    $exist = RedisOperation::get($redisObject, $key);
    if ($exist) {
        throw new BaseException('操作频繁请稍后重试');
    }
    RedisOperation::setex($redisObject, $key, 3, 1);
}

function getValueBit(string $value, array $list)
{
    $valueArray = explode(',', $value);
    $valueBit = 0;
    foreach ($valueArray as $v) {
        if (!in_array($v, array_keys($list))) {
            continue;
        }
        $valueBit += $list[$v];
    }
    return $valueBit;
}

function getValueArray(int $valueBit, array $list)
{
    $valueArray = [];
    foreach ($list as $key => $value) {
        if (($valueBit & $value) != 0) {
            $valueArray[] = $key;
        }
    }
    return $valueArray;
}

/**
 * 汉字转汉语拼音
 *
 * @param string $chinese
 * @return string
 * @author fyf
 */
function chineseForInitials(string $chinese) : string
{
    $initials = '';

    if (empty($chinese)) {
        return $initials;
    }

    // 元音
    $yuanYin = [
        'ā' => 1,
        'á' => 2,
        'ǎ' => 3,
        'à' => 4,
        'ē' => 1,
        'é' => 2,
        'ě' => 3,
        'è' => 4,
        'ī' => 1,
        'í' => 2,
        'ǐ' => 3,
        'ì' => 4,
        'ō' => 1,
        'ó' => 2,
        'ǒ' => 3,
        'ò' => 4,
        'ū' => 1,
        'ú' => 2,
        'ǔ' => 3,
        'ù' => 4,
    ];

    $pinYinObj = new Pinyin();
    $pinyinArray = $pinYinObj->convert($chinese, PINYIN_TONE);
    if ($pinyinArray) {
        foreach ($pinyinArray as &$pinyin) {
            $pinyinBack = $pinyin;
            foreach ($yuanYin as $k => $v) {
                $pinyinNew = str_replace($k, $k . $v, $pinyinBack);
                if ($pinyinNew != $pinyinBack) {
                    $pinyin .= $v;
                }
            }
        }
        $initials = implode('', $pinyinArray);
    }
    return $initials;
}

