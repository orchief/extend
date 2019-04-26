<?php
namespace Utility;

/**
 * 一键生成rest接口
 */
use think\Model as ThinkModel;

class Model extends ThinkModel
{
    use Url2Sql;
    protected $paramConfigs = [
        'sorts'  =>  ['id'],            // 排序字段
        'search' =>  ['name'],          // 模糊搜索
        'filter' =>  ['cateid'],        // 精确过滤
        'ranges' =>  ['time'],          // 范围查询
        'in'     =>  ['cate.status'],   // 多点查询
        'limit'  =>  [5, 20, 50, 100],  // 每页条数
        'offset' =>  0,                 // 跳过条数
        'page'   =>  1                  // 当前默认页码
    ];

    protected $with = [];

    /**
     * 只读字段 禁止update的字段
     *
     * @var array
     */
    protected $readonly = [];
    /**
     * 隐藏字段 无法查询的字段 read / index不显示的字段
     * @var boolean
     */
    protected $hidden = ['userId'];
    /**
     * 显示字段 不设置会返回所有字段 
     * @var boolean
     */
    protected $visible = [];
    /**
     * 主键id
     *
     * @var string
     */
    protected $pk = 'id';

    public function __construct($data = [])
    {
        parent::__construct($data);
        $param = \think\facade\Request::param();
        if(isset($param['fields'])){
            $this->visible = array_diff(explode(',', $param['fields']), $this->hidden); // 设为隐藏的优先
        }
        if(!$this->returnFields){
            $this->returnFields = implode(',', $this->visible);
        }
    }
    
    /**
     * 多条件获取数据列表
     */
    public function getDataList($param)
    {
        // 解析参数
        list($where, $order, $offset, $limit) = $this->buildparams($param, $this->paramConfigs);

        // 创建sql查询
        $list = $this->where($where)->order($order)->limit($offset, $limit)->select();
        $list = $this->filter($list);

        if(!isset($param['page'])){
            return $list;
        }

        $totals = $this->where($where)->count();
        return ['list'  =>  $list, 'dataCount'  => $totals];
    }


    /**
     * 获取一条数据
     *
     * @param [type] $param
     * @return void
     */
    public function getDataById($id)
    {
        return continue_if($this->field($this->returnFields)->with($this->with)->where($this->name . '.' . $this->pk, $id)->find(), ['msg' => '暂无此数据！']);
    }

    /**
     * 自定义 过滤数据库查询结果
     *
     * @return void
     */
    public function filter($data)
    {
        return $data;
    }
    
    /**
     * [createData 新建]
     * @linchuangbin
     * @DateTime  2017-02-10T21:19:06+0800
     * @param     array                    $param [description]
     * @return    [array]                         [description]
     */
    public function createData($param)
    {
        if($this->jsonFields){
            foreach($this->jsonFields as $k => $v){
                foreach ($param as $kk => $vv) {
                    if($kk === $v){
                        $param[$v] = json_encode($param[$v], true);
                    }
                }
            }
        }

        try {
            $this->data($param)->allowField(true)->save();
        } catch (\Exception $e) {
            if(config('app.app_debug')){
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => lang('create failed!')]);
        }
        return $this;
    }


    /**
     * 通过主键id修改用户
     * @param  array   $param  [description]
     */
    public function updateDataById($param, $id, $fields = true, $pk = 'id')
    {
        try {
            $this->allowField($fields)->save($param, [$pk => $id]);
        } catch (\Exception $e) {
            if(config('app.app_debug')){
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => lang('update failed!')]);
        }
        return $this;
    }


    /**
     * 通过主键id修改用户.
     *
     * @param array $param [description]
     */
    public function updateUserDataById($param, $id, $fields = true, $pk = 'id')
    {
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
            $this->allowField($fields)->where(['userId' => $param['userId'], $pk => $id])->update($param);
        } catch (\Exception $e) {
            if (config('app.app_debug')) {
                abort(['msg' => $e->getMessage()]);
            }
            abort(['msg' => '编辑失败!']);
        }
    }
}