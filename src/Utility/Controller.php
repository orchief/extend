<?php
// +----------------------------------------------------------------------
// | Description: 解决跨域问题
// +----------------------------------------------------------------------
// | Author: orchief
// +----------------------------------------------------------------------

namespace Utility;

use think\Controller as ThinkController;
use think\Request;

class Controller extends ThinkController
{
    // 初始化
    protected function initialize()
    {
        // continue_if(isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'], '未获得授权！', 401);
        $this->param = $this->request->param();
    }

    /**
     * 实例化model 如果是本控制器对应的model则暂存
     */
    protected function model($name = null){
        if(isset($this->model)){
            return $this->model;
        }

        if(null == $name){
            $name = $this->modelName;
        }
        if($this->modelName == $name){
            $this->model = model($name, 'model', false);
            return $this->model;
        }else{
            return model($name, 'model', false);
        }
    }

    /** 
     * 获取所有参数
     */
    protected function params($param = null, $default = null)
    {
        if(is_array($param)){
            $origin = $this->request->param();
            return array_replace_recursive($origin, $param);
        }

        if($param){
            $this->request->param($param, $default);
        }
    }
}
