<?php
// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace {$namespace}\common\controller;

class Rest extends ApiCommon
{
    public $modelName = null;
    public function index()
    {
        $data = $this->model()->getDataList($this->param);
        result($data, $this->model()->getError());
    }

    public function read()
    {
        $data = $this->model()->getDataById($this->param['id']);
        result($data, ['msg' => '暂无此数据!']);
    }

    public function save()
    {
        $this->model()->createData($this->param);
        result(['msg' => '添加成功!']);
    }

    public function update()
    {
        $this->model()->updateDataById($this->param, $this->param['id']);
        result(['msg' => '更新成功!']);
    }

    public function delete()
    {
        $res = $this->model()->delDatas($this->param['id']);
        result(['msg' => '删除成功!']);
    }

    public function deletes()
    {
        if(!is_array($this->param['ids'])){
            $this->param['ids'] = explode(',', $this->param['ids']);
        }
        $res = $this->model()->delDatas($this->param['ids']);

        result(['msg' => '删除成功!']);
    }

    public function enables()
    {
        if(!is_array($this->param['ids'])){
            $this->param['ids'] = explode(',', $this->param['ids']);
        }

        $res = $this->model()->enableDatas($this->param['ids'], $this->param['status']);
        result(['msg' => '更新成功!']);
    }
}
