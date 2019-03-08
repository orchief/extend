<?php
// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;
use think\Controller;

trait User
{
    // 初始化
    protected function initialize()
    {
        parent::initialize(); 
        userId();
    }
}
