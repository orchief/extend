<?php
// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Batch
{
    public function batch()
    {
        $param = $this->params();

        \validates(
            [
                'ids'   =>  'require',
                'data'   =>  'require'
            ],
            [
                'ids'   =>  'id列表',
                'data'  =>  '需改的数据'
            ], $param
        );

        if(isset($param['userId'])){    // 需要权限的情况
            $res = $this->model()->batch($param['data'], str2Arr($param['ids']));
        }else{
            $res = $this->model()->batch($param['data'], str2Arr($param['ids']));
        }

        result(['msg' => '更新成功!']);
    }
}