<?php
// +----------------------------------------------------------------------
// | Description: 批量删除
// +----------------------------------------------------------------------
// | Author: dongpeng
// +----------------------------------------------------------------------

namespace Rest;

trait Deletes
{
    public function deletes()
    {
        $param = $this->params();
        if(isset($param['ids'])){
            $param['ids'] = str2Arr($param['ids']);
            $res = $this->model()
            ->where($this->model()->getPk(), 'in', $param['ids'])
            ->delete();
        }else{
            abort(['msg' => '删除失败!']);
        }

        result(['msg' => '删除成功!', 'deleteCount' => $res]);
    }
}
