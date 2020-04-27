<?php

// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Index
{
    public function index()
    {
        $param = $this->params();
        $data = $this->model()->getDataList($param);
        result($data);
    }
}
