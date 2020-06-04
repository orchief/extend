<?php

namespace Utility;

/*
 * 一键生成rest接口
 */

use think\Model as ThinkModel;
use think\facade\Config;

class Model extends ThinkModel
{
    use Url2Sql;
    /**
     * 控制器处理过的前端请求参数.
     *
     * @var array
     */
    protected $param;
    /**
     * 排序字段
     * ['-createTime', 'id']  默认正序 -号倒叙.
     *
     * @var string
     */
    /**
     * 散列查询.
     *
     * @var array
     */
    protected $whereIn = [];
    /**
     * 排序.
     *
     * @var array
     */
    protected $sorts = ['sellerName'];
    /**
     * 允许主表  精确查询的字段 会被转换成 =.
     *
     * @var array
     */
    protected $eqCons = [];
    /**
     * 允许主表 模糊查询的字段 会被转换成 like 并且值两边会被加上 %.
     *
     * @var array
     */
    protected $likeCons = [];
    /**
     * 允许范围查询的字段 支持单段查询
     * 例子: fieldName=1,100  // 查询出 1 到 100 之间的值
     * 例子: fieldName=1,     // 查询出 大于 1 的值
     * 例子: fieldName=,100   // 查询出 小于 100 的值
     *
     * @var array
     */
    protected $ranges = [];
    /**
     * 联合查询 left join的普通封装.
     *
     * @var array
     */
    protected $leftJoin = [
        // [
        //     'tablename',        // left join的表名
        //     'tablename_id',     // left join 的表的对应键
        //     'main_id'           // 主表对应的键
        // ]
    ];
    /**
     * 默认 每页条数.
     *
     * @var int
     */
    protected $limit = 20;
    /**
     * 当前跳过条数 默认不跳过.
     *
     * @var int
     */
    protected $offset = 0;
    /**
     * select 后边的字符串 返回的字段.
     *
     * @var array
     */
    protected $returnFields = '';
    /**
     * 只读字段 禁止update的字段.
     *
     * @var array
     */
    protected $readonly = [];
    /**
     * 是否开启软删除 开启软删除必须在数据库表添加 isDelete字段 类型为int.
     *
     * @var bool
     */
    protected $softDelete = false;
    /**
     * 隐藏字段 无法查询的字段 read / index不显示的字段.
     *
     * @var array
     */
    protected $hidden = ['userId'];
    /**
     * 显示字段 不设置会返回所有字段.
     *
     * @var bool
     */
    protected $visible = [];
    /**
     * 默认软删除字段名称.
     *
     * @var bool
     */
    protected $delField = 'isDelete';

    protected $with;
    protected $jsonFields;
    /**
     * 主键id.
     *
     * @var string
     */
    protected $pk = 'id';

    public function __construct($data = [])
    {
        parent::__construct($data);
        $param = \think\facade\Request::param();
        if (isset($param['fields'])) {
            $this->visible = array_diff(str2Arr($param['fields']), $this->hidden); // 设为隐藏的优先
        }
        if (!$this->returnFields) {
            $this->returnFields = implode(',', $this->visible);
        }
    }

    /**
     * 多条件获取数据列表.
     */
    public function getDataList($param)
    {
        $res = $this->parseUrl($param)->select();
        $total = $this->getTotals($param)->count();
        $res = $this->filter($res);
        $resData['list'] = $res;
        $resData['dataCount'] = $total;
        return $resData;
    }

    /**
     * 获取一条数据.
     *
     * @param [type] $param
     */
    public function getDataById($id)
    {
        $res = $this->parseUrl([])->where($this->name . '.' . $this->pk, $id)->find();

        $res = $this->filter([$res])[0];

        return  $res;
    }

    /**
     * 获取用户的一条数据.
     *
     * @param [type] $param
     */
    public function getUserDataById($id, $userId)
    {
        // 联合查询
        if ($this->leftJoin) {
            foreach ($this->leftJoin as $k => $v) {
                $this->join($v[0], $v[0] . '.' . $v[1] . '=' . $this->name . '.' . $v[2], 'LEFT');
            }
        }
        $res = $this->field($this->returnFields)->where($this->name . '.' . $this->pk, $id)->where(['userId' => $userId])->find();

        $res = $this->filter([$res])[0];

        return  $res;
    }

    public function getDataListCount($param)
    {
        $res = $this->parseUrl($param)->count();

        return  $this->filter($res);
    }

    /**
     * 自定义 过滤数据库查询结果.
     */
    public function filter($data)
    {
        foreach ($data as $k => $v) {
            $this->map($data[$k]);
        }

        return $data;
    }

    /**
     * 自定义 过滤数据库查询结果.
     */
    public function map(&$field)
    {
        if ($field) {
            // json 格式化字段
            if ($this->jsonFields) {
                foreach ($this->jsonFields as $k => $v) {
                    if ($field) {
                        $field[$v] = json_decode($field[$v], true);
                    }
                }
            }
        }
    }

    public function validate($param, $scene)
    {
        $validate = validate($this->name);
        continue_if(!$validate->hasScene($scene) or $validate->scene($scene)->check($param), ['msg' => $validate->getError()]);
    }

    /**
     * [createData 新建].
     *
     * @linchuangbin
     * @DateTime  2017-02-10T21:19:06+0800
     *
     * @param array $param [description]
     *
     * @return [array] [description]
     */
    public function createData($param)
    {
        // json 格式化字段
        if ($this->jsonFields) {
            foreach ($this->jsonFields as $k => $v) {
                foreach ($param as $kk => $vv) {
                    if ($kk === $v) {
                        $param[$v] = toJson($vv);
                    }
                }
            }
        }

        try {
            $res = $this->data($param)->allowField(true)->save();
        } catch (\Exception $e) {
            if (config::get('app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '新建数据失败!']);
        }

        return $res;
    }

    /**
     * 通过主键id修改用户.
     *
     * @param array $param [description]
     */
    public function batch($param, $id, $fields = true, $pk = 'id')
    {
        $new_param = [];
        foreach ($this->batchField as $k => $v) {
            if (isset($param[$v])) {
                $new_param[$v] = $param[$v];
            }
        }
        // 验证
        // $validate = validate($this->name);
        // continue_if($validate->hasScene('update') && $validate->scene('update')->check($new_param), ['msg' => $validate->getError()]);

        // $a = $validate->scene('update')->check($new_param);


        // json 格式化字段
        if ($this->jsonFields) {
            foreach ($this->jsonFields as $k => $v) {
                foreach ($new_param as $kk => $vv) {
                    if ($kk === $v) {
                        $new_param[$v] = toJson($vv);
                    }
                }
            }
        }

        // 模板设置了
        if ($pk != 'id' && $this->pk) {
            $pk = $this->pk;
        }

        try {
            $this->allowField($fields)->where($pk, 'in', $id)->update($new_param);
        } catch (\Exception $e) {
            if (Config::get('app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '编辑失败!']);
        }
    }

    /**
     * 通过主键id修改用户.
     *
     * @param array $param [description]
     */
    public function updateDataById($param, $id, $fields = true, $pk = 'id')
    {
        // 验证
        // $validate = validate($this->name);
        // continue_if(!$validate->hasScene('update') or $validate->scene('update')->check($param), ['msg' => $validate->getError()]);

        // json 格式化字段
        if ($this->jsonFields) {
            foreach ($this->jsonFields as $k => $v) {
                foreach ($param as $kk => $vv) {
                    if ($kk === $v) {
                        $param[$v] = toJson($vv);
                    }
                }
            }
        }

        // 模板设置了
        if ($pk != 'id' && $this->pk) {
            $pk = $this->pk;
        }

        try {
            $this->allowField($fields)->where($pk, 'in', $id)->update($param);
        } catch (\Exception $e) {
            if (Config::get('app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '编辑失败!']);
        }
    }

    /**
     * 通过主键id修改用户.
     *
     * @param array $param [description]
     */
    public function updateUserDataById($param, $id, $fields = true, $pk = 'id')
    {
        // 验证
        // $validate = validate($this->name);
        // continue_if(!$validate->hasScene('update') or $validate->scene('update')->check($param), ['msg' => $validate->getError()]);

        // json 格式化字段
        if ($this->jsonFields) {
            foreach ($this->jsonFields as $k => $v) {
                foreach ($param as $kk => $vv) {
                    if ($kk === $v) {
                        $param[$v] = toJson($vv);
                    }
                }
            }
        }

        try {
            $this->allowField($fields)->where(
                [
                    ['userId', '=', $param['userId']],
                    [$pk, 'in', $id]
                ]
            )->update($param);
        } catch (\Exception $e) {
            if (Config::get('app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '编辑失败!']);
        }
    }

    /**
     * [delDataById 根据id删除数据].
     *
     * @param mixed $ids
     */
    public function del($ids)
    {
        try {
            if (is_array($ids)) {
                continue_if(!empty($ids), ['msg' => '删除失败!']);
                $this->where($this->pk, 'in', $ids)->delete();
            } else {
                $this->where($this->pk, $ids)->delete();
            }
        } catch (\Exception $e) {
            if (Config::get('app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '删除失败!']);
        }

        return true;
    }

    /**
     * [delDataById 根据id删除数据].
     *
     * @param mixed $ids
     */
    public function delDatas($ids)
    {
        if ($this->softDelete == true) {
            return $this->softDelDatas($ids);
        } else {
            try {
                if (is_array($ids)) {
                    continue_if(!empty($ids), ['msg' => '删除失败!']);
                    $this->where($this->pk, 'in', $ids)->delete();
                } else {
                    $this->where($this->pk, $ids)->delete();
                }
            } catch (\Exception $e) {
                if (Config::get('app_debug')) {
                    abort(['msg' => $e->getMessage()]);
                }
                abort(['msg' => '删除失败!']);
            }
        }

        return true;
    }

    /**
     * 多条件删除
     *
     * @return void
     */
    public function deletes($param)
    {
        continue_if($param != [], ['msg' => '禁止无条件删除！']);
        $res = $this->parseUrl($param)->select()->toArray();
        $ids = array_column($res, $this->getPk());
        return $this->delDatas($ids);
    }



    /**
     * [delDataById 根据id删除数据].
     *
     * @param mixed $ids
     */
    public function delUserDatas($ids, $userId)
    {
        if ($this->softDelete == true) {
            return $this->softDelDatas($ids);
        } else {
            try {
                if (is_array($ids)) {
                    continue_if(!empty($ids), ['msg' => '删除失败!']);
                    $this->where(['userId' => $userId])->where($this->pk, 'in', $ids)->delete();
                } else {
                    $this->where(['userId' => $userId])->where($this->pk, $ids)->delete();
                }
            } catch (\Exception $e) {
                if (Config::get('app_debug')) {
                    abort(['msg' => $e->getMessage()]);
                }
                abort(['msg' => '删除失败!']);
            }
        }

        return true;
    }

    /**
     * 软删除数据 通过将isDelete字段设置为删除时候的时间戳来实现软删除 并同时记录删除时间.
     *
     * @param array  $ids
     * @param string $delField
     */
    public function softDelDatas($ids = [])
    {
        try {
            if (is_array($ids)) {
                continue_if(!empty($ids), ['msg' => '删除失败!']);
                $this->where($this->pk, 'in', $ids)->setField($this->delField, time());
            } else {
                $this->where($this->pk, $ids)->setField($this->delField, time());
                $sql = $this->getLastSql();
            }
        } catch (\Exception $e) {
            if (Config::get('app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '删除失败!']);
        }

        return true;
    }

    /**
     * 某个字段在几个选项直接切换 支持批量操作.
     *
     * @AuthorHTL
     * @DateTime  2017-02-11T21:01:58+0800
     *
     * @param string $ids       [主键数组]
     * @param int    $status    [状态1启用0禁用]
     * @param string $fieldName [要修改状态的字段名称 默认status]
     *
     * @return [type] [description]
     */
    public function switchFileds($ids = [], $value = 1, $fieldName = 'status')
    {
        continue_if(!empty($ids), ['msg' => '请传入要修改的条目!']);
        try {
            $this->where($this->pk, 'in', $ids)->setField($fieldName, $value);
        } catch (\Exception $e) {
            if (Config::get('app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '修改失败!']);
        }
    }

    /**
     * [enableDatas 批量启用、禁用].
     *
     * @AuthorHTL
     * @DateTime  2017-02-11T21:01:58+0800
     *
     * @param string $ids       [主键数组]
     * @param int    $status    [状态1启用0禁用]
     * @param string $fieldName [要修改状态的字段名称 默认status]
     *
     * @return [type] [description]
     */
    public function enableDatas($ids = [], $value = 1, $fieldName = 'status')
    {
        if (empty($ids)) {
            $this->error = '请传入要修改的条目';

            return false;
        }

        try {
            $this->where($this->pk, 'in', $ids)->setField($fieldName, $value);

            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }


    /**
     * [enableDatas 批量启用、禁用].
     *
     * @AuthorHTL
     * @DateTime  2017-02-11T21:01:58+0800
     *
     * @param string $ids       [主键数组]
     * @param int    $status    [状态1启用0禁用]
     * @param string $fieldName [要修改状态的字段名称 默认status]
     *
     * @return [type] [description]
     */
    public function enableUserDatas($userId, $ids = [], $value = 1, $fieldName = 'status')
    {
        if (empty($ids)) {
            $this->error = '请传入要修改的条目';

            return false;
        }

        try {
            $this->where(['userId' => $userId])->where($this->pk, 'in', $ids)->setField($fieldName, $value);

            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }


    /**
     * 多条件获取数据列表.
     */
    public function getSum($param, $field)
    {
        $res = [];
        $param['page'] = 1;
        $param['limit'] = 10;
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $res[$value] = $this->getTotals($param)->sum($value);
            }
        }

        return $res;
    }
}
