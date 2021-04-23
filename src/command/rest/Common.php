<?php

namespace command\rest;

use think\Db;
use think\facade\Config;

class Common
{
    protected $config = [];

    protected $database = [];

    protected $like = '';
    protected $is = '';
    protected $ranges = '';
    protected $swaggerParameter = '';
    protected $swaggerItems = '';
    protected $auth = '';
    protected $scene_create = '';
    protected $rule = '';
    protected $field = '';
    protected $scene_update = '';
    protected $postman = '';
    protected $jsonFields = '';
    protected $sorts = '';
    protected $whereIn = '';

    public function __construct($config)
    {
        $this->config = array_replace_recursive($this->config, $config);
        $this->input = $this->config['input'];
        $this->output = $this->config['output'];
        $this->getDbColumnComment();
        $this->getSwaggerParams();
        $this->_init();
    }

    /**
     * 自定义初始化.
     */
    public function _init()
    {
    }

    /**
     * 获取数据库字段注释.
     *
     * @param string $this->table  数据表名称(必须，不含前缀)
     * @param string $field        字段名称(默认获取全部字段,单个字段请输入字段名称)
     * @param string $table_schema 数据库名称(可选)
     *
     * @return string
     */
    protected function getDbColumnComment($field = true, $table_schema = '')
    {
        $database = Config::get()['database'];
        $table_schema = empty($table_schema) ? $database['database'] : $table_schema;

        // 缓存名称
        $fieldName = $field === true ? 'allField' : $field;
        $cacheKeyName = 'db_'.$table_schema.'_'.$this->getTable().'_'.$fieldName;

        // 处理参数
        $param = [
            $this->getTable(),
            $table_schema,
        ];

        // 字段
        $columeName = '';
        if ($field !== true) {
            $param[] = $field;
            $columeName = 'AND COLUMN_NAME = ?';
        }

        // 查询结果
        $result = Db :: query("SELECT *  FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ? AND table_schema = ? $columeName", $param);

        foreach ($result as $k => $v) {
            if ($v['COLUMN_NAME'] == 'userId') {
                unset($result[$k]);
                // 存在userId的表需要进行权限校验
                $this->auth = '    *     security={
                    *         {"authorization": {}}
                    *     },';
            }

            if ($v['COLUMN_NAME'] == 'id') {
                unset($result[$k]);
            }
        }

        $this->tableInfo = $result;
    }

    /**
     * 生成模板 如果是强制覆盖 需要将覆盖之前的文件缓存 等待是否进行 回滚.
     */
    public function create()
    {
        // 缓存之前的文件
        $this->cacheOriginFile();
        $this->build();
    }

    /**
     * 设置替换参数.
     */
    protected function setTplValues($name, $value = null)
    {
        if (is_array($name)) {
            $this->tplValues = array_replace_recursive($this->tplValues, $name);
        } elseif (is_string($name)) {

            $this->tplValues = array_replace_recursive($this->tplValues, [$name => $value]);
        }

        return true;
    }

    /**
     * 生成文件.
     */
    protected function build()
    {
        $tpl = $this->getTpl();

        $tpl = str_replace(array_values($this->tplKeys), array_values($this->tplValues), $tpl);

        $file = $this->getFullPathName();

        $path = $this->getPath();

        is_dir($path) or mkdir($path, 0777, true);

        file_put_contents($file, $tpl);
    }

    /**
     * 回滚刚才生成的模板
     * 情况1: 强制更新的话需要还原更新之前的文件
     * 情况2: 新增的文件则直接删除.
     */
    protected function rollback()
    {
    }

    protected function cacheOriginFile()
    {
    }

    /**
     * 获取对应的模板文件.
     */
    protected function getTpl()
    {
        $getTplFullPath = $this->getTplFullPath();

        return file_get_contents($getTplFullPath);
    }

    /**
     * 获取模板文件的全路径.
     */
    protected function getTplFullPath()
    {
        if ($this->input->getOption('plain')) {
            $fullPath = \think\Facade\Env::get('root_path') . DIRECTORY_SEPARATOR . 'vendor'.DIRECTORY_SEPARATOR.'orchief'.DIRECTORY_SEPARATOR.'utility'.DIRECTORY_SEPARATOR.'src' .DIRECTORY_SEPARATOR . 'command'. DIRECTORY_SEPARATOR .'tpl'.DIRECTORY_SEPARATOR.$this->getTplName($this->typeName).'.plain.tpl';
        } else {
            $fullPath = \think\Facade\Env::get('root_path') . DIRECTORY_SEPARATOR . 'vendor'.DIRECTORY_SEPARATOR.'orchief'.DIRECTORY_SEPARATOR.'utility'.DIRECTORY_SEPARATOR.'src' .DIRECTORY_SEPARATOR . 'command'. DIRECTORY_SEPARATOR .'tpl'.DIRECTORY_SEPARATOR.$this->getTplName($this->typeName).'.tpl';
        }

        return  $fullPath;
    }

    /**
     * get tplName.
     *
     * @return string
     */
    protected function getTplName($name)
    {
        return strtolower($name);
    }

    /**
     * 获取文件存储路径.
     *
     * @param [type] $classname
     * @param [type] $typeName
     * @param [type] $output
     */
    protected function getFullPathName()
    {
        $name = trim($this->input->getArgument('name'));

        $arr = explode('/', $name);

        $temp[0] = $arr[0];
        $temp[1] = $this->typeName;
        $temp[2] = ucfirst(convertUnderline($arr[1]));

        $temp = implode('/', $temp);

        $res = \think\Facade\Env::get('root_path').'application'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $temp).'.php';

        return $res;
    }

    /**
     * 获取文件存储路径.
     *
     * @param [type] $classname
     * @param [type] $typeName
     * @param [type] $output
     */
    protected function getPath()
    {
        $name = trim($this->input->getArgument('name'));

        $arr = explode('/', $name);

        $temp[0] = $arr[0];
        $temp[1] = $this->typeName;

        $temp = implode('/', $temp);

        $res = \think\Facade\Env::get('root_path').'application'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $temp).DIRECTORY_SEPARATOR;

        return $res;
    }

    /**
     * 获取文件保存 名称.
     *
     * @param [type] $classname
     * @param [type] $typeName
     * @param [type] $output
     */
    protected function getFileName($classname, $typeName, $output)
    {
    }

    /**
     * 获取 namespace.
     */
    protected function getNamespace()
    {
        $a = \think\Facade\Env::get('app_namespace');

        return \think\Facade\Env::get('app_namespace').'\\'.implode('\\', array_slice(explode('/', $this->input->getArgument('name')), 0, -1));
    }

    /**
     * get class.
     */
    protected function getClass()
    {
        $name = $this->input->getArgument('name');
        $class = ucfirst(explode('/', $name)[1]);

        return convertUnderline($class);
    }

    /**
     * 获取swagger注释参数.
     */
    protected function getSwaggerParams()
    {
        foreach ($this->tableInfo as $k => $v) {
            if ($this->ifpass($v)) {
                continue;
            }

            $this->swaggerParameter .= '*     @OA\Parameter(
    *         name="'.$v['COLUMN_NAME'].'",
    *         in="query",
    *         description="'.$v['COLUMN_COMMENT'].'",
    *         required=false,
    *         @OA\Schema(
    *             type="'.$this->getFieldType($v).'",
    *             format="string",
    *         )
    *     ),
    ';
            $this->swaggerItems .= '*                   @OA\Property(
    *                       property="'.$v['COLUMN_NAME'].'",
    *                       description="'.$v['COLUMN_COMMENT'].'",
    *                       type="'.$this->getFieldType($v).'"
    *               ),
    ';
        }

        foreach ($this->tableInfo as $k => $v) {
            if ($this->ifpass($v)) {
                continue;
            }

            // 字段类型为 int
            if ($this->getFieldType($v) == 'integer') {
                if ($this->input->hasParameterOption('-a')) {
                    $this->is .= '"userId",';
                }
                $this->is .= '"'.$v['COLUMN_NAME'].'",';
            }

            // 字段类型为 string
            if ($this->getFieldType($v) == 'string') {
                $this->like .= '"'.$v['COLUMN_NAME'].'",';
            }

            // 字段类型为 date
            if ($this->getFieldType($v) == 'time' ) {
                $this->ranges .= '"'.$v['COLUMN_NAME'].'",';
            }

            if($this->getFieldType($v) == 'integer'){
                $this->whereIn .= '"'.$v['COLUMN_NAME'].'",';
            }

            // 字段类型为 json
            if ($this->getFieldType($v) == 'json') {
                $this->jsonFields .= '"'.$v['COLUMN_NAME'].'",';
            }

            // 字段类型为 time
            if ($this->getFieldType($v) == 'time') {
                $this->sorts .= '"-'.$v['COLUMN_NAME'].'",';
            }
        }
    }

    /**
     * 过滤掉隐藏字段.
     */
    protected function ifpass($fields, $scene = null)
    {
        if ($scene == 'create' && is_null($fields['COLUMN_DEFAULT']) && $fields['IS_NULLABLE'] == 'NO') {
            return true;
        }

        return strpos($fields['COLUMN_COMMENT'], 'hidden') !== false;
    }

    /**
     * 数据量字段类型 装换为php字段类型.
     *
     * @param [type] $originType
     */
    protected function getFieldType($v)
    {
        $timetype = [
            'bigint', 'int', 'timestamp', 'datetime', 'date'
        ];
        if(in_string('time', $v['COLUMN_NAME']) && \in_array($v['DATA_TYPE'], $timetype)){
            return 'time';
        }

        $passtype = [
            'varchar', 'char', 'text', 'longtext'
        ];

        if( (in_string('word', $v['COLUMN_NAME']) || in_string('pass', $v['COLUMN_NAME']) ) && \in_array($v['DATA_TYPE'], $passtype)){
            return 'password';
        }

        // return $originType;
        switch ($v['DATA_TYPE']) {
            case 'bigint':
                return 'integer';
            case 'int':
                return 'integer';
            case 'tinyint':
                return 'integer';
                break;
            case 'smallint':
                return 'integer';
                break;
            case 'mediumint':
                return 'integer';
                break;
            case 'float':
                return 'number';
                break;
            case 'double':
                return 'number';
                break;
            case 'decimal':
                return 'number';
                break;
            case 'timestamp':
                return 'date';
                break;
            case 'datetime':
                return 'date';
                break;
            case 'date':
                return 'date';
                break;
            case 'json':
                return 'json';
                break;
        }

        return 'string';
    }

    /**
     * 去除数据库注释中的额外信息.
     */
    protected function trimExtraComment($str)
    {
        $str = preg_replace("/\((.*?)\)/", '', $str);

        return trim($str);
    }

    /**
     * 获取table名称.
     */
    protected function getTable()
    {
        // 初始化
        $table = trim($this->input->getOption('table'));
        $name = trim($this->input->getArgument('name'));
        $arr = explode('/', $name);

        $table2 = $arr[0].'_'.humpToLine(lcfirst($arr[1]));

        $this->table = $table ? $table : $table2;
        // 接收参数
        $database = Config::get()['database'];
        $this->table = $database['prefix'].$this->table;

        return $this->table;
    }
}
