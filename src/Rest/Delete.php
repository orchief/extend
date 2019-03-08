<?php
// +----------------------------------------------------------------------
// | Description: restFul风格 api接口基础类
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Delete
{
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
            $res = $this->model()->delUserDatas(str2Arr($id), $param['userId']);
        }else{
            $res = $this->model()->delDatas(str2Arr($id));
        }
        result(['msg' => '删除成功!']);
    }
}
