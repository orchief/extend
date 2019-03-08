<?php
namespace command\rest\lib;
use think\Db;
use command\rest\Tpl;
use command\rest\Common;
/**
 * 
 */
class Validate extends Common implements Tpl
{
    /**
     * 等待替换的
     *
     * @var array
     */
    protected $tplKeys = [
        'Description'   =>  '{$Description}',
        'Author'        =>  '{$Author}',
        'Date'          =>  '{$Date}',
        'namespace'     =>  '{$namespace}',
        'class'         =>  '{$class}',
        'table'         =>  '{$table}',
        'rule'          =>  '{$rule}',
        'field'         =>  '{$field}',
        'create'        =>  '{$create}',
        'update'        =>  '{$update}',
        'postman'        =>  '{$postman}',
    ];


    /**
     * 对应tplkeys需要替换成的内容
     *
     * @var array
     */
    protected $tplValues = [
        'Description'   =>  '',
        'Author'        =>  '',
        'Date'          =>  '',
        'namespace'     =>  '',
        'class'         =>  '',
        'table'         =>  '',
        'rule'          =>  '',
        'field'         =>  '',
        'create'        =>  '',
        'update'        =>  '',
        'postman'        =>  '',
    ];

    /**
     * 控制器
     *
     * @var string
     */
    protected $typeName = 'validate';

    public function _init()
    {
        // validates
        foreach($this->tableInfo as $k => $v){
            $sence_create = [];
            $sence_update = [];

            // 字段是否必须
            if(is_null($v['COLUMN_DEFAULT']) && $v['IS_NULLABLE'] == 'NO'){
                $sence_create[] = 'require';
            }

            // 字段是否为唯一
            if($v['COLUMN_KEY'] == 'UNI'){
                $sence_create[] = 'unique:' . $this->getTable() . '';
                $sence_update[] = 'unique:' . $this->getTable() . '';
            }

            // 是否为字符串 长度限制
            if($v['DATA_TYPE'] == 'varchar'){
                $sence_create[] = "length:1," . $v['CHARACTER_MAXIMUM_LENGTH'];
                $sence_update[] = "length:1," . $v['CHARACTER_MAXIMUM_LENGTH'];
            }

            /**
            
            手机->phone
            邮箱->email
            身份证->idCard
            银行卡->bankCard
            密码->password

             */

            $typeList = [
                'phone' => [
                    '手机'
                ],
                'email' => [
                    '邮箱'
                ],
                'idCard' => [
                    '身份证'
                ],
                'bankCard' => [
                    '银行卡'
                ],
                'password' => [
                    '密码'
                ],
            ];

            $COLUMN_COMMENT = $v['COLUMN_COMMENT'];

            foreach($typeList as $kk => $vv){
                foreach($vv as $kkk => $vvv){
                    if(in_string($vvv, $COLUMN_COMMENT)){
                        $sence_create[] = $kk;
                        $sence_update[] = $kk;
                        break;
                    }
                }
            }

            $hasUpCase = preg_match('/[A-Z]/',$COLUMN_COMMENT);

            // 是否为类型
            if($v['DATA_TYPE'] == 'tinyint' || $hasUpCase) {
                $isMatched = preg_match_all('/([a-zA-Z\d]+)\:/isU', $v['COLUMN_COMMENT'], $matches);
                if($isMatched){
                    $selets = implode(',', $matches[1]);
                    $sence_create[] = "in:" . $selets;
                    $sence_update[] = "in:" . $selets;
                }
            }

            if(count($sence_create)){
                $sence_create_str = implode('|', $sence_create);
            
                $this->rule .= "
        '".$v['COLUMN_NAME']."' => '" . $sence_create_str . "',";
            }

            if(count($sence_update)){
                $sence_update_str = implode('|', $sence_update);
            
                $this->scene_update .= "
        '".$v['COLUMN_NAME']."' => '" . $sence_update_str . "',";
            }
        }

        // 字段注释提取
        $count = 1;
        foreach($this->tableInfo as $k => $v){
            if($this->ifpass($v)){
                continue;
            }
            $this->field .= "
        '".$v['COLUMN_NAME']."' => '".$this->trimExtraComment($v['COLUMN_COMMENT'])."',";
            $this->postman .= "
".$count . '. ' . $v['COLUMN_NAME']." : ".$v['COLUMN_COMMENT'];
        $count++;
        }

        // 新增验证
        $tem = [];
        foreach($this->tableInfo as $k => $v){
            $tem[] = "\"" . $v['COLUMN_NAME'] . "\"";
        }
        $this->scene_create .= implode(',', $tem);

        $this->setTplValues([
            'Description'   =>  trim($this->input->getOption('Description')),
            'Author'        =>  trim($this->input->getOption('Author')),
            'Date'          =>  date('Y-m-d H:i:s'),
            'namespace'     =>  $this->getNamespace(),
            'class'         =>  $this->getClass(),
            'rule'          =>  $this->rule,
            'field'         =>  $this->field,
            'create'        =>  $this->scene_create,
            'update'        =>  $this->scene_update,
            'postman'       =>  $this->postman
        ]);
    }

    /**
     * get class
     */
    protected function getClass()
    {
        return ucfirst(convertUnderline($this->getTable()));
    }

    /**
     * 获取文件存储路径
     *
     * @param [type] $classname
     * @param [type] $typeName
     * @param [type] $output
     * @return void
     */
    protected function getFullPathName()
    {
        $name = trim($this->input->getArgument('name'));
        $arr = explode('/', $name);

        $temp[0] = $arr[0];
        $temp[1] = $this->typeName;
        $temp[2] = ucfirst(convertUnderline($this->getTable()));

        $temp = implode('/', $temp);

        $res = \think\Facade\Env::get('root_path') . 'application' . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $temp) . '.php';

        return $res;
    }
}
