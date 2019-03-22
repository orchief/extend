<?php

namespace Utility;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\facade\Log;
use think\facade\Request;

class HandleException extends Handle
{
    public function render(Exception $e)
    {
        // 请求异常
        if ($e instanceof HttpException && $e->getStatusCode() == 404) {
            $res = ['code' => 404, 'msg' => $e->getMessage()];
            return response($res, 200, [], 'json');
        }
        $a = config('app.app_debug');
        if (config('app.app_debug')) {
            //  判断是否为静态资源
            $res = ['code' => 500, 'msg' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTrace(), 'previous' => $e->getPrevious(), 'param' => Request::param()];
            //  记录日志
            Log::debug(json_encode($res));

            $trace = $res['trace'];
            unset($res['trace']);
            unset($res['previous']);
            
            $res['trace'] = $trace;

            \Mail\Mail::send(json_encode($res, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            return response($res, 200, [], 'json');
        } else {
            $res = ['code' => 500, 'msg' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTrace(), 'previous' => $e->getPrevious(), 'param' => Request::param()];
            Log::error(json_encode($res));
            $trace = $res['trace'];
            unset($res['trace']);
            unset($res['previous']);

            $res['trace'] = $trace;
            \Mail\Mail::send(json_encode($res, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            return response(['code' => 500, 'msg' => '服务器异常！'], 500, [], 'json');
        }
    }
}
