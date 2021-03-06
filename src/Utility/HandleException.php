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
            if(config('app.mail.bugreport')){
                \Mail\Mail::send(json_encode($res, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            }

            \Sentry\init(['dsn' => 'https://3816b82a71644f5090633e9911553ee6@sentry.io/1510175' ]);
            \Sentry\captureException($e);

            return response($res, 200, [], 'json');
        } else {
            $res = ['code' => 500, 'msg' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTrace(), 'previous' => $e->getPrevious(), 'param' => Request::param()];
            Log::error(json_encode($res));
            $trace = $res['trace'];
            unset($res['trace']);
            unset($res['previous']);

            $res['trace'] = $trace;
            if(config('app.mail.bugreport')){
                \Mail\Mail::send(json_encode($res, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            }
            return response(['code' => 500, 'msg' => '服务器异常！'], 500, [], 'json');
        }
    }
}
