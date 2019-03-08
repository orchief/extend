<?php
//----------------------------------------------------------------
// 通用助手函数
//----------------------------------------------------------------
/**
 * 给树状菜单添加level并去掉没有子菜单的菜单项.
 *
 * @param array  $data  [description]
 * @param int    $root  [description]
 * @param string $child [description]
 * @param string $level [description]
 */
function memuLevelClear($data, $root = 1, $child = 'child', $level = 'level')
{
    if (is_array($data)) {
        foreach ($data as $key => $val) {
            $data[$key]['selected'] = false;
            $data[$key]['level'] = $root;
            if (!empty($val[$child]) && is_array($val[$child])) {
                $data[$key][$child] = memuLevelClear($val[$child], $root + 1);
            } elseif ($root < 3 && $data[$key]['menu_type'] == 1) {
                unset($data[$key]);
            }
            if (empty($data[$key][$child]) && ($data[$key]['level'] == 1) && ($data[$key]['menu_type'] == 1)) {
                unset($data[$key]);
            }
        }

        return array_values($data);
    }

    return array();
}

/**
 * [rulesDeal 给树状规则表处理成 module-controller-action ].
 *
 * @AuthorHTL
 * @DateTime  2017-01-16T16:01:46+0800
 *
 * @param [array] $data [树状规则数组]
 *
 * @return [array] [返回数组]
 */
function rulesDeal($data)
{
    if (is_array($data)) {
        $ret = [];
        foreach ($data as $k1 => $v1) {
            $str1 = $v1['name'];
            if (is_array($v1['child'])) {
                foreach ($v1['child'] as $k2 => $v2) {
                    $str2 = $str1.'-'.$v2['name'];
                    if (is_array($v2['child'])) {
                        foreach ($v2['child'] as $k3 => $v3) {
                            $str3 = $str2.'-'.$v3['name'];
                            $ret[] = $str3;
                        }
                    } else {
                        $ret[] = $str2;
                    }
                }
            } else {
                $ret[] = $str1;
            }
        }

        return $ret;
    }

    return [];
}

/**
 * 用户密码加密方法.
 *
 * @param string $str      加密的字符串
 * @param [type] $auth_key 加密符
 *
 * @return string 加密后长度为32的字符串
 */
function user_md5($str, $salt = '')
{
    return '' === $str ? '' : md5(sha1($str).$salt);
}

/**
 * 终止程序返回json数据.
 *
 * @param array $jsonBody 返回的json
 * @param int   $httpCode http code
 */
function json($data = [], $code = 200, $httpcode = 200, $header = [], $options = [])
{
    $Body = [
        'code' => $code,
        'data' => $data,
    ];

    $response = think\Response::create($Body, 'json', $httpcode, $header, $options);
    throw new think\exception\HttpResponseException($response);
}

/**
 * 获取 / 设置配置信息
 *
 * @param string $name
 * @param string $value
 * @return mixed
 */
function setting($name = null, $value = null)
{
    $value = \app\admin\model\Setting::where(['name' => $name])->value('value');
    $arrV = json_decode($value, true);
    foreach($arrV as $k => $v){
        $res[$v['name']] = $v['value'];
    }
    return $res;
}

/**
 * 终止程序返回json数据.
 *
 * @param array $jsonBody 返回的json
 * @param int   $httpCode http code
 */
function abort($data = [], $code = 400, $httpcode = 200, $header = [], $options = [])
{
    if (!is_array($data)) {
        $data = ['msg' => $data];
    }
    $Body = [
        'code' => $code,
        'data' => $data,
    ];

    $response = think\Response::create($Body, 'json', $httpcode, $header, $options);
    throw new think\exception\HttpResponseException($response);
}

/**
 * Undocumented function.
 */
function continue_if($bool, $data, $code = 400, $httpcode = 200, $header = [], $options = [])
{
    if (!$bool) {
        if (!is_array($data)) {
            $data = ['msg' => $data];
        }
        json($data, $code, $httpcode, $header, $options);
    }

    return $bool;
}

/**
 * 抛出异常或者程序继续执行.
 *
 * @param boolen $boolen 需要检验的结果 为true 则程序继续运行 否则抛出异常
 */
function throw_if($boolen, $msg)
{
    if (!$boolen) {
        throw new \Exception(json_encode($msg, JSON_UNESCAPED_UNICODE));
    }

    return $boolen;
}

function result($res, $data = [], $code = 400, $httpcode = 200, $header = [], $options = [])
{
    if ($res || is_array($res)) {
        json($res, 200, $httpcode, $header, $options);
    } else {
        if (!is_array($data)) {
            $data = ['msg' => $data];
        }
        json($data, $code, $httpcode, $header, $options);
    }
}

/**
 * 验证数据 (数据 默认为请求参数).
 *
 * @param array $rules Rules of inspection
 * @param array $field field 注释
 * @param array $data  data
 */
function validates($rules, $field = [], $data = null, $msg = [])
{
    if (null == $data) {
        $data = \think\facade\Request::instance()->param();
    }

    foreach ($rules as $k => $v) {
        if ([] != $field) {
            $field_keys = array_keys($field);
            if (!in_array($k, $field_keys)) {
                $field[$k] = $k;
            }
        }
    }

    $validator = new \Utility\Validate($rules, $msg, $field);
    continue_if($validator->check($data), $validator->getError());
}

/**
 * 获取当前用户的userId.
 */
function userId()
{
    if (isset($GLOBALS['_userId'])) {
        return $GLOBALS['_userId'];
    }

    continue_if(isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'], '请登录！', 403);

    $userId = continue_if(\Utility\JWT::get('userId'), '没有访问权限！', 403);

    // 存储UserId
    $GLOBALS['_userId'] = decodeUserId(\Utility\JWT::get('userId'));

    return $userId;
}

/**
 * 将字符串类型参数转换为数组.
 *
 * @param [type] $str
 */
function str2Arr($str)
{
    if (is_array($str)) {
        return $str;
    }

    $res1 = json_decode($str, true);

    if (is_array($res1)) {
        return $res1;
    }

    if(strpos($str,',') !== false){
        $res2 = explode(',', $str);
        if (is_array($res2)) {
            return $res2;
        }
    }

    return $str;
}

/**
 * 终止程序返回json数据.
 *
 * @param array $jsonBody 返回的json
 * @param int   $httpCode http code
 */
function toJson($json)
{
    if (!is_string($json)) {
        return json_encode($json);
    }

    $res1 = json_decode($json, true);

    if ($res1 && !is_string($res1)) {
        return $res1;
    }

    $res2 = explode(',', $json);

    if (is_array($res2)) {
        return json_encode($res2);
    }

    return $json;
}

/**
 * 驼峰转下划线
 *
 * @param string $str 需要转换的字符
 */
function humpToLine($str)
{
    $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
        return '_'.strtolower($matches[0]);
    }, $str);

    return $str;
}

/**
 * 下划线转驼峰.
 *
 * @param [type] $str
 */
function convertUnderline($str)
{
    $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
        return strtoupper($matches[2]);
    }, $str);

    return $str;
}

/**
 * 判断一个字符串是否包含另一个字符串.
 */
function in_string($needle, $string)
{
    return strpos($string, $needle) !== false;
}

/**
 * 解密userId.
 */
function decodeUserId($number, $salt = 0)
{
    $optimus = new \Jenssegers\Optimus\Optimus(1580030173, 59260789, 1163945558);
    $original = $optimus->decode($number); // 20
    return $original;
}

/**
 * 加密userId.
 */
function encodeUserId($userId, $salt = 0)
{
    $optimus = new \Jenssegers\Optimus\Optimus(1580030173, 59260789, 1163945558);
    $encoded = $optimus->encode($userId); // 1535832388
    return $encoded;
}


/**
 * 通过银行名称查询银行编码
 *
 * @param [type] $bankName
 * @return void
 */
function getBankleitzahlByBankNo($bankNo)
{
    $curl = new \Curl\Curl();
    $data = [
        '_input_charset'    =>  'utf-8',
        'cardNo'            =>  $bankNo,
        'cardBinCheck'      =>  'true'
    ];
    $curl->get('https://ccdcapi.alipay.com/validateAndCacheCardInfo.json', $data);

    if ($curl->error) {
        return "OTHER";
    } else {
        return $curl->response;
    }
}

/**
 * 获取当前登录 用户信息
 *
 * @param [type] $field
 * @return void
 */
function userInfo($field = null, $value = null)
{
    if(!is_array($field) && $value == null){        // 获取
        $userId = \Utility\JWT::get('userId');
        if(!$userId){
            return null;
        }
        if(null == $field){
            return \app\user\model\Members::get($userId);
        }
        return \app\user\model\Members::where(['userId' => $userId])->value($field);
    }

    if(null != $field && !is_array($field) && $value != null){        // 设置
        if(!userId()){
            return null;
        }
        
        return \app\user\model\Members::where(['userId' => userId()])->update($field, $value);
    }
}

/**
 * 数据库事务函数
 * @param function $func 匿名函数
 * @param object $model 执行事务的主model
 * @return void
 */
function trans($func, $model)
{
    $model->startTrans();
    try{
        call_user_func($func);
        $model->commit();
    }catch(\Exception $th){
        $model->rollback();
        if ($th instanceof \think\exception\HttpResponseException) {
            $res = json_decode($th->getResponse()->getContent(), true);
            result($res['data'], $res['code']);
        } else {
            abort($th->getMessage(), 500);
        }
    }
}

/**
 * 格式化的当前时间.
 */
function now()
{
    // 获取userId
    if (isset($GLOBALS['_now'])) {
        return $GLOBALS['_now'];
    }

    $now = date('Y-m-d H:i:s');

    // 存储UserId
    $GLOBALS['_now'] = $now;

    return $now;
}

/**
 * 获取唯一订单号.
 */
function getOrderId($salt = '')
{
    return $salt.substr(userId(), -5).rand(10000, 99999).time();
}

// /**
//  * cookies加密函数
//  * @param string 加密后字符串
//  */
// function encrypt($data, $key = 'kls8in1e')
// {
//     $prep_code = serialize($data);
//     $block = openssl_cipher_iv_length('AES-256-CBC');
//     if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
//         $prep_code .= str_repeat(chr($pad), $pad);
//     }
//     $encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB);
//     return base64_encode($encrypt);
// }

// /**
//  * cookies 解密密函数
//  * @param array 解密后数组
//  */
// function decrypt($str, $key = 'kls8in1e')
// {
//     $str = base64_decode($str);
//     $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
//     $block = openssl_cipher_iv_length('AES-256-CBC');
//     $pad = ord($str[($len = strlen($str)) - 1]);
//     if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
//         $str = substr($str, 0, strlen($str) - $pad);
//     }
//     return unserialize($str);
// }

/**
 * 字符加密，一次一密,可定时解密有效
 * 
 * @param string $string 原文
 * @param string $key 密钥
 * @param int $expiry 密文有效期,单位s,0 为永久有效
 * @return string 加密后的内容
 */
function encrypt($string,$key = 'kls8in1e', $expiry = 0){
    $string = serialize($string);
    $ckeyLength = 4;
    $keya = md5(substr($key, 0, 16));         //做数据完整性验证  
    $keyb = md5(substr($key, 16, 16));         //用于变化生成的密文 (初始化向量IV)
    $keyc = substr(md5(microtime()), - $ckeyLength);
    $cryptkey = $keya . md5($keya . $keyc);  
    $keyLength = strlen($cryptkey);
    $string = sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string . $keyb), 0, 16) . $string;
    $stringLength = strlen($string);
    $rndkey = array();    
    for($i = 0; $i <= 255; $i++) {    
        $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
    }
    $box = range(0, 255);    
    // 打乱密匙簿，增加随机性
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }    
    // 加解密，从密匙簿得出密匙进行异或，再转成字符
    $result = '';
    for($a = $j = $i = 0; $i < $stringLength; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp; 
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    $result = $keyc . str_replace('=', '', base64_encode($result));
    $result = str_replace(array('+', '/', '='),array('-', '_', '.'), $result);
    return $result;
}


/**
 * 字符解密，一次一密,可定时解密有效
 * 
 * @param string $string 密文
 * @param string $key 解密密钥
 * @return string 解密后的内容
 */
function decrypt($string,$key = 'kls8in1e')
{
    $string = str_replace(array('-', '_', '.'),array('+', '/', '='), $string);
    $ckeyLength = 4;
    $keya = md5(substr($key, 0, 16));         //做数据完整性验证  
    $keyb = md5(substr($key, 16, 16));         //用于变化生成的密文 (初始化向量IV)
    $keyc = substr($string, 0, $ckeyLength);
    $cryptkey = $keya . md5($keya . $keyc);  
    $keyLength = strlen($cryptkey);
    $string = base64_decode(substr($string, $ckeyLength));
    $stringLength = strlen($string);
    $rndkey = array();    
    for($i = 0; $i <= 255; $i++) {    
        $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
    }
    $box = range(0, 255);
    // 打乱密匙簿，增加随机性
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 加解密，从密匙簿得出密匙进行异或，再转成字符
    $result = '';
    for($a = $j = $i = 0; $i < $stringLength; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp; 
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0)
    && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
    ) {
        return unserialize(substr($result, 26));
    } else {
        return '';
    }
}