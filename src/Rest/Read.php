<?php

// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Read
{
    public function read($id)
    {
        $param = $this->params();

        if(isset($param['userId'])){    // 需要权限的情况
            $data = $this->model()->getUserDataById($id, $param['userId']);
        }else{
            $data = $this->model()->getDataById($id);
        }
        
        result($data, '暂无此数据!');
    }
}
