<?php

namespace Utility;

use Firebase\JWT\JWT as jwtoken;
use think\facade\Config;

/**
 * jwt.
 */
class JWT
{
    /**
     * 负载部分.
     */
    public static $preload = [];

    /**
     * 签名结果.
     */
    public static $encoded;

    /**
     * 解密结果.
     */
    public static $decoded;

    /**
     * 密钥.
     */
    public static $key;

    /**
     * 设置 jwt.
     *
     * @param [type] $name
     * @param [type] $value
     */
    public static function set($name, $value = null, $key = null)
    {
        static::$preload = self::get();
        static::$preload['expire'] = time();    // 秘钥生成时间
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                static::$preload[$k] = $v;
            }
        } else {
            static::$preload[$name] = $value;
        }

        if($key == null){
            $key = Config::get('app.jwt.key'); // 签名秘钥
        }
        // 生成用户秘钥
        self::$encoded = jwtoken::encode(static::$preload, $key);
        header('authorization: '.self::$encoded);
    }

    /**
     * 获取jwt.
     *
     * @param [type] $name
     */
    public static function get($name = null, $key = null)
    {
        // 检查 static::$decoded
        if ([] == static::$preload) {
            // 生成用户秘钥
            if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION']) {
                $authorization = $_SERVER['HTTP_AUTHORIZATION'];
            } else {
                return [];
            }
            $authorization = str_replace('Bearer ', '', $authorization);
            
            if($key == null){
                $key = Config::get('app.jwt.key'); // 签名秘钥
            }
            try {
                self::$preload = static::object_array(jwtoken::decode($authorization, $key, array('HS256')));
                if (!static::$preload) {
                    static::$preload = [];
                }
            } catch (\Exception $e) {
                return [];
            }
        }

        if (null != $name) {
            if (array_key_exists($name, self::$preload)) {
                return self::$preload[$name];
            } else {
                return null;
            }
        } else {
            return static::$preload;
        }
    }

    /**
     * 对象转换为数组.
     *
     * @param [type] $array
     */
    private static function object_array($array)
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = static::object_array($value);
            }
        }

        return $array;
    }
}
