<?php
// +----------------------------------------------------------------------
// | Description: 解决跨域问题
// +----------------------------------------------------------------------
// | Author: orchief
// +----------------------------------------------------------------------

namespace Utility;

use think\Controller as ThinkController;
use think\facade\Request;

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

        try{
            if($this->modelName == $name){
                $this->model = model($name, 'model', false);
                return $this->model;
            }else{
                return model($name, 'model', false);
            }
        }catch(\Exception $e){
            return model($name, 'model', false);
        }
    }

    /** 
     * 获取所有参数
     */
    protected function params($param = [])
    {
        $bodyData = file_get_contents('php://input');
        $bodyData = json_decode($bodyData, true);
        $origin = $this->request->param();
        if(!$bodyData){
            $bodyData = [];
        }

        $propertyName = '_' . Request::action();
        if(property_exists($this, $propertyName) && is_array($this->$propertyName)){
            $prefix = $this->$propertyName;
        }else{
            $prefix = [];
        }

        return array_replace_recursive($origin, $bodyData, $prefix, $param);
    }
}
