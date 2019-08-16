<?php
// +----------------------------------------------------------------------
// | Description: 权限类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace {$namespace}\common\controller;

use app\JWT;
use app\Controller;

class ApiCommon extends Controller
{
    /**
     * 接口白名单
     *
     * @var array
     */
    protected  $whiteList = [
        'Authorization'     => [                // 手机号 登录
            'save'
        ],
        'Members'           => [                // 手机号 注册
            'save'
        ]
    ];

    public function _initialize()
    {
        parent::_initialize();
        $ac = request()->action();
        $selfClass = get_class($this);
        $action = request()->action();
        continue_if(property_exists($selfClass, $action), ['msg' => '方法不存在!'], 404);

        // 不需要权限验证
        if($this->noAuth()){
            if(isset(userId())){
                unset(userId());
            }
            return;
        }

        // 检验是否为微信登录

        // 权限验证
        userId() = JWT::get('userId');
        continue_if(userId(), [ 'msg' => '权限不足!'], 403);
    }

    /**
     * 小程序微信登录
     *
     * @return void
     */
    private function wechatApp()
    {
        // $app->auth->session(string $code);

    }

    /**
     * 检查白名单
     *
     * @return void
     */
    private function noAuth()
    {
        $c = request()->controller();
        $a = request()->action();
        if (array_key_exists($c, $this->whiteList)) {
            if (in_array($a, $this->whiteList[$c])) {
                return true; // 在白名单中不需要验证
            }
        }
        return false;
    }
}
