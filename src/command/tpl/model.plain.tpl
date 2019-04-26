<?php
// +----------------------------------------------------------------------
// | Description: {$Description}
// +----------------------------------------------------------------------
// | Author: {$Author}
// +----------------------------------------------------------------------
// | Date: {$Date}
// +----------------------------------------------------------------------

namespace {$namespace}\model;

use Utility\{$dbType};

class {$class} extends {$dbType}
{
    protected $name = '{$table}';
    protected $paramConfigs = [
        'sorts'  =>  ['id'],            
        'search' =>  [{$like}],          
        'filter' =>  [{$is}],        
        'ranges' =>  [{$ranges}],          
        'in'     =>  [],
        'limit'  =>  [5, 20, 50, 100],  
        'offset' =>  0,                 
        'page'   =>  1                  
    ];
    /**
     * 只读字段
     *
     * @var array
     */
    protected $readonly = [];
    /**
     * 隐藏字段
     * @var boolean
     */
    protected $hidden = ['userId'];
    /**
     * 显示字段
     * @var boolean
     */
    protected $visible = [];
    /**
     * json字段 设置为json的字段自动转换
     *
     * @var boolean
     */
    protected $jsonFields = [{$jsonFields}];
}
