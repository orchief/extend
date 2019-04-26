<?php
namespace Utility;

/**
 * 将url根据规定的格式转换成 查询sql
 */
trait Url2Sql{

    /**
     * 获取where查询条件
     */
    public function buildparams($params, $configs = [])
    {
        $search = isset($configs['search']) ? $configs['search'] : false;
        $filter = isset($configs['filter']) ? $configs['filter'] : false;
        $ranges = isset($configs['ranges']) ? $configs['ranges'] : false;
        $in     = isset($configs['in']) ? $configs['in'] : false;
        $sorts  = isset($configs['sorts']) ? $configs['sorts'] : false;
        $where = [];
        $order = [];

        // 字段模糊查询 
        if ($search) {
            foreach ($search as $k => $v) {
                if (array_key_exists($v, $params) && ($params[$v] or $params[$v] === "0" or $params[$v] === 0)) {
                    $where[] = [
                        $v, 'LIKE', '%' . $params[$v] . '%'
                    ];
                }
            }
        }

        // 字段精确查询
        if ($filter) {
            foreach ($filter as $k => $v) {
                if (array_key_exists($v, $params) && ($params[$v] or $params[$v] === "0" or $params[$v] === 0)) {
                    $where[] = [
                        $v, '=', $params[$v]
                    ];
                }
            }
        }

        // 范围查询
        if ($ranges) {
            foreach ($ranges as $k => $v) {
                if (array_key_exists($v, $params) && ($params[$v] or $params[$v] === "0" or $params[$v] === 0)) {
                    $params[$v] = str2Arr($params[$v]);
                    $count = count($params[$v]);
                    if($count == 1 || $count == 2){
                        $sym = 'BETWEEN';
                        if (!isset($params[$v][0])) {
                            $sym = '<=';
                            $params[$v] = $params[$v][1];
                        } else if (!isset($params[$v][0])) {
                            $sym = '>=';
                            $params[$v] = $params[$v][0];
                        }
                        $where[] = [$v, $sym, $params[$v]];
                    }
                }
            }
        }

        if ($in) {
            foreach ($in as $k => $v) {
                if (array_key_exists($v, $params) && is_array($params[$v]) && count($params[$v]) ) {
                    $params[$v] = str2Arr($params[$v]);
                    if(is_array($params[$v]) && count($params[$v])){
                        $where[] = [
                            $v, 'in', $params[$v]
                        ];
                    }
                }
            }
        }

        if ($sorts && isset($params['sorts'])) {
            $params['sorts'] = str2Arr($params['sorts']);
            foreach ($this->sorts as $k => $v) {
                if(in_array($v, $params['sorts'])){
                    $order[$v] = 'ASC';
                }else if(in_array('-' . $v, $params['sorts'])){
                    $order[$v] = 'DESC';
                }
            }
        }

        $offset = isset($param['offset']) && $param['offset'] ? $param['offset'] : 0;
        if(isset($param['page'])){
            $limit  = isset($param['limit'])  && $param['limit'] ? $param['limit'] : 20;
            $offset = ($param['page'] - 1) * $limit;
        } else {
            $limit  = isset($param['limit'])  && $param['limit'] ? $param['limit'] : 20;
            $offset = isset($param['offset']) && $param['offset'] ? $param['offset'] : 0;
        }

        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };

        return [$where, $order, $offset, $limit];
    }
}