<?php
// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Save
{
    public function save()
    {
        $param = $this->params();
        $this->model()->validate($param, 'create');
        $this->model()->createData($param);
        result(['msg' => '添加成功!']);
    }
}
