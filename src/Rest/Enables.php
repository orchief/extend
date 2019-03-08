<?php
// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Enables
{
    public function enables()
    {
        $param = $this->params();

        if(isset($param['userId'])){    // 需要权限的情况
            $res = $this->model()->enableUserDatas($param['userId'], str2Arr($param['ids']), $param['status']);
        }else{
            $res = $this->model()->enableDatas(str2Arr($param['ids']), $param['status']);
        }
    
        result(['msg' => '更新成功!']);
    }
}