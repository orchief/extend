<?php

namespace Utility;

/**
 * 将url根据规定的格式转换成 查询sql.
 */
trait Url2Sql
{
    private $page = null;

    protected $where = [];
    protected $order = [];

    /**
     * param参数解析器.
     *
     * @param [type] $param
     */
    protected function parseUrl($param)
    {
        $this->param = $param;
        $this->limit = isset($this->param['limit']) ? $this->param['limit'] : $this->limit;                 // 获取多少条数据
        $this->offset = isset($this->param['offset']) ? $this->param['offset'] : $this->offset;             // 跳过多少条数据
        $this->page = isset($this->param['page']) ? $this->param['page'] : null;                            // 按照页码查询

        // 联合查询
        $model = $this;
        $model = $model->with($this->with);
        if ($this->leftJoin) {
            foreach ($this->leftJoin as $k => $v) {
                if(isset($v[3])){
                    $join = $v[3];
                }else{
                    $join = 'LEFT';
                }
                if(is_array($v[2])){
                    $join_relate = array_values($v[2])[0].'.'.array_keys($v[2])[0];
                    
                }else{
                    $join_relate = $this->name.'.'.$v[2];
                }
                if(is_array($v[0]))
                {
                    $model = $model->join($v[0][0].' '.$v[0][1], $v[0][1].'.'.$v[1].'='.$join_relate, $join);
                }
                else
                {
                    $model = $model->join($v[0], $v[0].'.'.$v[1].'='.$join_relate, $join);
                }
            }
        }

        if ($param) {
            $this->multiSearch();
        }

        if ($this->limit == -1) {
            return $model->field($this->returnFields)->where($this->where)->order($this->order);
        }
        if (null != $this->page) {
            return $model->field($this->returnFields)
            ->where($this->where)->order($this->order)
            ->page($this->page, $this->limit);
        } else {
            return $model->field($this->returnFields)
            ->where($this->where)->order($this->order)
            ->limit($this->offset, $this->limit);
        }
    }

    /**
     * param参数解析器.
     *
     * @param [type] $param
     */
    protected function getTotals($param)
    {
        $this->where = [];
        $this->param = $param;
        $this->limit = isset($this->param['limit']) ? $this->param['limit'] : $this->limit;                 // 获取多少条数据
        $this->offset = isset($this->param['offset']) ? $this->param['offset'] : $this->offset;             // 跳过多少条数据
        $this->page = isset($this->param['page']) ? $this->param['page'] : null;                            // 按照页码查询

        // 联合查询
        $model = $this;
        if ($this->leftJoin) {
            foreach ($this->leftJoin as $k => $v) {
                if(isset($v[3])){
                    $join = $v[3];
                }else{
                    $join = 'LEFT';
                }
                if(is_array($v[2])){
                    $join_relate = array_values($v[2])[0].'.'.array_keys($v[2])[0];
                    
                }else{
                    $join_relate = $this->name.'.'.$v[2];
                }
                if(is_array($v[0]))
                {
                    $model = $model->join($v[0][0].' '.$v[0][1], $v[0][1].'.'.$v[1].'='.$join_relate, $join);
                }
                else
                {
                    $model = $model->join($v[0], $v[0].'.'.$v[1].'='.$join_relate, $join);
                }
            }
        }

        if ($param) {
            $this->multiSearch();
        }
        
        return $model->where($this->where)->order($this->order);
    }

    /**
     * 多条件查询.
     */
    protected function multiSearch()
    {
        // 模糊查询
        if ($this->likeCons) {
            foreach ($this->likeCons as $k => $v) {
                if (!is_numeric($k)) {
                    if (array_key_exists($k, $this->param) && ($this->param[$k] or $this->param[$k] === '0' or $this->param[$k] === 0)) {
                        $this->param[$k] = $this->str2arr($this->param[$k]);
                        if (is_string($this->param[$k]) or is_numeric($this->param[$k])) {
                            $this->where[] = [
                                $v.'.'.$k,  'like', '%'.$this->param[$k].'%',
                            ];
                        }
                    }
                } else {
                    if (array_key_exists($v, $this->param) && ($this->param[$v] or $this->param[$v] === '0' or $this->param[$v] === 0)) {
                        $this->param[$v] = $this->str2arr($this->param[$v]);
                        if (is_string($this->param[$v]) or is_numeric($this->param[$v])) {
                            $this->where[] = [
                                $this->name.'.'.$v, 'like', '%'.$this->param[$v].'%',
                            ];
                        }
                    }
                }
            }
        }

        // 精确查询
        if ($this->eqCons) {
            foreach ($this->eqCons as $k => $v) {
                if (!is_numeric($k)) {
                    if (array_key_exists($k, $this->param) && ($this->param[$k] or $this->param[$k] === '0' or $this->param[$k] === 0)) {
                        $this->param[$k] = $this->str2arr($this->param[$k]);
                        if (is_string($this->param[$k]) or is_numeric($this->param[$k])) {
                            $this->where[] = [
                                $v.'.'.$k, '=', $this->param[$k],
                            ];
                        }
                    }
                } else {
                    if (array_key_exists($v, $this->param) && ($this->param[$v] or $this->param[$v] === '0' or $this->param[$v] === 0)) {
                        $this->param[$v] = $this->str2arr($this->param[$v]);
                        if (is_string($this->param[$v]) or is_numeric($this->param[$v])) {
                            $this->where[] = [
                                $this->name.'.'.$v, '=', $this->param[$v],
                            ];
                        }
                    }
                }
            }
        }

        // 范围
        if ($this->ranges) {
            foreach ($this->ranges as $k => $v) {
                if (!is_numeric($k)) {
                    if (array_key_exists($k, $this->param) && ($this->param[$k] or $this->param[$k] === '0' or $this->param[$k] === 0)) {
                        // 数组模式
                        $this->param[$k] = $this->str2arr($this->param[$k]);
                        if (is_array($this->param[$k])) {
                            // 最小值
                            if (isset($this->param[$k][0])) {
                                $this->where[] = [
                                    $v.'.'.$k, '>=', $this->param[$k][0],
                                ];
                            }
                            // 最大值
                            if (isset($this->param[$k][1])) {
                                $this->where[] = [
                                    $v.'.'.$k, '<=', $this->param[$k][1],
                                ];
                            }
                        }
                    }
                } else {
                    if (array_key_exists($v, $this->param) && ($this->param[$v] or $this->param[$v] === '0' or $this->param[$v] === 0)) {
                        // 数组模式
                        $this->param[$v] = $this->str2arr($this->param[$v]);
                        if (is_array($this->param[$v])) {
                            // 最小值
                            if (isset($this->param[$v][0])) {
                                $this->where[] = [
                                    $this->name.'.'.$v, '>=', $this->param[$v][0],
                                ];
                            }
                            // 最大值
                            if (isset($this->param[$v][1])) {
                                $this->where[] = [
                                    $this->name.'.'.$v, '<=', $this->param[$v][1],
                                ];
                            }
                        }
                    }
                }
            }
        }

        // where in
        if ($this->whereIn) {
            foreach ($this->whereIn as $k => $v) {
                if (!is_numeric($k)) {
                    if (array_key_exists($k, $this->param) && is_array($this->param[$k]) && count($this->param[$k])) {
                        // 数组模式
                        $this->param[$k] = $this->str2arr($this->param[$k]);
                        if (isset($this->param[$k][0])) {
                            if(is_array($this->param[$k])){
                                $this->where[] = [
                                    $v.'.'.$k, 'in', $this->param[$k],
                                ];
                            }else{
                                $this->where[] = [
                                    $v.'.'.$k, '=', $this->param[$k],
                                ];
                            }
                        }
                    }
                } else {
                    if (array_key_exists($v, $this->param) && is_array($this->param[$v]) && count($this->param[$v])) {
                        // 数组模式
                        $this->param[$v] = $this->str2arr($this->param[$v]);
                        if(is_array($this->param[$v])){
                            if (isset($this->param[$v][0])) {
                                $this->where[] = [
                                    $this->name.'.'.$v, 'in', $this->param[$v],
                                ];
                            }
                        }else{
                            if (isset($this->param[$v][0])) {
                                $this->where[] = [
                                    $this->name.'.'.$v, '=', $this->param[$v],
                                ];
                            }
                        }

                    }
                }
            }
        }

        // 排序
        if ($this->sorts && isset($this->param['sorts'])) {
            $sorts = $this->str2arr($this->param['sorts']);
            
            if($sorts && is_array($sorts)){
                foreach ($sorts as $k => $v) {
                    $absv = str_replace('-', '', $v);
                    if (isset($this->sorts[$absv])) {
                        if ($v === $absv) {
                            $this->order[$this->sorts[$absv] . '.' . $absv] = 'asc';
                        } else {
                            $this->order[$this->sorts[$absv] . '.' . $absv] = 'desc';
                        }
                    } else {
                        if ($v === $absv) {
                            $this->order[$this->name.'.' . $absv] = 'asc';
                        } else{
                            $this->order[$this->name.'.' . $absv] = 'desc';
                        }
                    }
                }
            }
        }
    }

    /**
     * 将 逗号隔开 / json 字符格式的数据格式化为数组.
     */
    protected function str2arr($str)
    {
        if (is_array($str)) {
            return $str;
        }
    
        $res1 = json_decode($str, true);
    
        if (is_array($res1)) {
            return $res1;
        }
    
        if(strpos($str,',') !== false){
            $res2 = explode(',', $str);
            if (is_array($res2)) {
                return $res2;
            }
        }
    
        return $str;
    }
}
