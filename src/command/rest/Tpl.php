<?php
namespace command\rest;

/**
 * 
 */
interface Tpl
{
    /**
     * 生成模板 如果是强制覆盖 需要将覆盖之前的文件缓存 等待是否进行 回滚
     *
     * @return void
     */
    public function create();
}
