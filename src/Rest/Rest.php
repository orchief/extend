<?php
// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Rest
{
    public function index()
    {
        $param = $this->params();
        $data = $this->model()->getDataList($param);
        result($data, $this->model()->getError());
    }

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

    public function save()
    {
        $param = $this->params();
        $this->model()->createData($param);
        result(['msg' => '添加成功!']);
    }

    public function update($id)
    {
        $param = $this->params();
        
        if(isset($param['userId'])){    // 需要权限的情况
            $res = $this->model()->updateUserDataById($param, $id);
        }else{
            $res = $this->model()->updateDataById($param, $id);
        }

        result(['msg' => '更新成功!']);
    }

    /**
     * 删除和批量删除
     *
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        $param = $this->params();
        if(isset($param['userId'])){    // 需要权限的情况
            $res = $this->model()->where('userId', $param['userId'])
            ->where($this->model()->pk, 'in', str2Arr($id))->delete();
        }else{
            $res = $this->model()->where($this->model()->pk, 'in', str2Arr($id))->delete();
        }
        result(['msg' => '删除成功!']);
    }
}
