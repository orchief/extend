<?php

namespace command\rest\lib;

use think\Db;
use command\rest\Tpl;
use command\rest\Common;

/**
 * 前端配置文件自动生成.
 */
class Config extends Common implements Tpl
{
    /**
     * 等待替换的.
     *
     * @var array
     */
    protected $tplKeys = [
        'Description' => '{$Description}',
        'baseApi' => '{$baseApi}',
        'fields' => '{$fields}',
        'excelfields' => '{$excelfields}',
        'search' => '{$search}',
        'showfields' => '{$showfields}',
        'addform' => '{$addform}',
        'addrules' => '{$addrules}',
        'editform' => '{$editform}',
        'editrules' => '{$editrules}',
        'filterData' => '{$filterData}',
    ];
    /**
     * 对应tplkeys需要替换成的内容.
     *
     * @var array
     */
    protected $tplValues = [
        'Description' => '',
        'baseApi' => '',
        'fields' => '',
        'excelfields' => '',
        'search' => '',
        'showfields' => '',
        'addform' => '',
        'addrules' => '',
        'editform' => '',
        'editrules' => '',
        'filterData' => '',
    ];

    /**
     * 控制器.
     *
     * @var string
     */
    protected $typeName = 'Config';

    public function _init()
    {
        foreach ($this->tableInfo as $k => $v) {
            // fields
            $temp[] = '
        '.$v['COLUMN_NAME'].": {
            desc: '".$this->trimExtraComment($v['COLUMN_COMMENT'])."'
        }";
            // excelfields
            $temp2[] = '"'.$v['COLUMN_NAME'].'"';

            // search
            $temp3[] = "
        { type: 'input', prop: '".$v['COLUMN_NAME']."' }";

            // addform
            switch ($v['DATA_TYPE']) {
                case 'tinyint':
                    $isMatched = preg_match_all('/(\\d+)\:([^x00-xff]*?)/isU', $v['COLUMN_COMMENT'], $matches);
                    if ($isMatched) {
                        foreach ($matches[1] as $match_k => $match_v) {
                            if (is_numeric($match_v)) {
                                $temp_select[] = "{ label: '".str_replace(')', '', $matches[2][$match_k])."', value: ".$match_v.' }';
                                $temp_exchange[] = "{
                                    origin: $match_v, 
                                    show: '".str_replace(')', '', $matches[2][$match_k])."'
                                }";
                            } else {
                                $temp_exchange[] = "{
                                    origin: '$match_v', 
                                    show: '".str_replace(')', '', $matches[2][$match_k])."'
                                }";
                                $temp_select[] = "{ label: '".str_replace(')', '', $matches[2][$match_k])."', value: '".$match_v."' }";
                            }
                        }
                        $temp_select_str = implode(',', $temp_select);
                        $temp4[] = "
                { prop: '".$v['COLUMN_NAME']."', label: '".$this->trimExtraComment($v['COLUMN_COMMENT'])."', type: 'select' , default: [
                    ".$temp_select_str.'
                ]}';

                        $exchange = implode(',', $temp_exchange);
                        $this->filterData[] = "{
                              originField: '".$v['COLUMN_NAME']."',
                              showField: '".$v['COLUMN_NAME']."Title',
                              exchange: [
                                  ".$exchange.'
                            ]
                          }';
                        $this->filterDataStr = implode(',', $this->filterData);

                        $temp[] = '
        '.$v['COLUMN_NAME']."Title: {
            desc: '".$this->trimExtraComment($v['COLUMN_COMMENT'])."'
        }";
                        $temp2[] = '"'.$v['COLUMN_NAME'].'Title"';
                    } else {
                        $temp4[] = "
            { prop: '".$v['COLUMN_NAME']."', label: '".$this->trimExtraComment($v['COLUMN_COMMENT'])."', type: 'input' }";
                    }

                break;
                case 'timestamp':
                $temp4[] = "
                { prop: '".$v['COLUMN_NAME']."', label: '".$this->trimExtraComment($v['COLUMN_COMMENT'])."', type: 'datetime' }";
                break;
                default:
                $temp4[] = "
            { prop: '".$v['COLUMN_NAME']."', label: '".$this->trimExtraComment($v['COLUMN_COMMENT'])."', type: 'input' }";
                break;
            }
        }
        $this->vueFields = implode(',', $temp);
        $this->excelfields = implode(',', $temp2);
        $this->search = implode(',', $temp3);
        $this->addform = implode(',', $temp4);

        $this->setTplValues([
            'Description' => trim($this->input->getOption('Description')),
            'baseApi' => trim($this->input->getArgument('name')),
            'fields' => $this->vueFields,
            'excelfields' => $this->excelfields,
            'search' => $this->search,
            'showfields' => $this->excelfields,
            'addform' => $this->addform,
            'addrules' => '',
            'editform' => $this->addform,
            'editrules' => '',
            'filterData' => $this->filterDataStr,
        ]);

        // 新增权限
        // 基本信息设置	shopConfig	2	10	1	0
        $desc = trim($this->input->getOption('Description'));

        $vue = trim($this->input->getOption('vue'));
        $name = trim($this->input->getArgument('name'));
        $arrvue = explode('/', $vue);
        $arrname = explode('/', $name);

        $moduleName = $arrvue[0];
        $pathName = $arrvue[1];

        $component = $moduleName.$pathName;

        $ruleData = [
            'title' => $desc,
            'name' => $arrname[1],
            'level' => 2,
            'pid' => 10,
        ];
        $rulePid = Db::table('admin_rule')->insertGetId($ruleData);

        $menus = Db::table('admin_menu')->where(['pid' => 0, 'module' => $moduleName])->find();

        $ruleDataChildren = [
            [
                'title' => '新增',
                'name' => 'save',
                'level' => 3,
                'pid' => $rulePid,
            ], [
                'title' => '修改',
                'name' => 'update',
                'level' => 3,
                'pid' => $rulePid,
            ], [
                'title' => '删除',
                'name' => 'delete',
                'level' => 3,
                'pid' => $rulePid,
            ], [
                'title' => '列表',
                'name' => 'index',
                'level' => 3,
                'pid' => $rulePid,
            ], [
                'title' => '单条',
                'name' => 'read',
                'level' => 3,
                'pid' => $rulePid,
            ], [
                'title' => '批量删除',
                'name' => 'deletes',
                'level' => 3,
                'pid' => $rulePid,
            ], [
                'title' => '批量修改',
                'name' => 'enables',
                'level' => 3,
                'pid' => $rulePid,
            ],
        ];

        $menu_rule_data = [
            'title' => $desc,
            'name' => $desc,
            'level' => 3,
            'pid' => $menus['rule_id'],
        ];

        $menu_rule_id = Db::table('admin_rule')->insert($menu_rule_data);

        Db::table('admin_rule')->insertAll($ruleDataChildren);

        // 查找到父级菜单权限 没有则插入一条
        Db::table('admin_rule')->where(['pid' => 420, 'name' => $moduleName])->value('id');

        // 新增菜单

        // 找到菜单pid
        $pid = Db::table('admin_menu')->where(['pid' => 0, 'module' => $moduleName])->value('id');

        $data = [
            'pid' => $pid,
            'title' => $desc,
            'url' => '/cms/'.trim($this->input->getOption('vue')),
            'icon' => 'people',
            'menu_type' => 2,
            'rule_id' => $menu_rule_id,
            'module' => $moduleName,
        ];
        Db::table('admin_menu')->insert($data);

        // 操作router生成路由
        $path = \think\Facade\Env::get('app_path').'../cms/src/router/routes.js';
        $string = file_get_contents($path);

        $import = "// $desc
import $component from '../views/$moduleName/$pathName'";

        if (in_string($import, $string)) {
            return;
        }

        $ins = "{
                path: '$pathName',
                component: $component,
                name: '$desc',
                meta: {
                    hideLeft: false,
                    module: '$moduleName',
                }
            },";

        $pattern = '/(cms\/'.$moduleName.')(.*?children\:.*?\[)(.*?)(\{)/is';
        $replacement = '${1}${2}'.$ins.'$4';

        // 路由新增
        $string = preg_replace($pattern, $replacement, $string);

        $pattern = '/(const.*userdefineRoutes)/is';
        $replacement = $import.'

${1}';

        // import 新增
        $string = preg_replace($pattern, $replacement, $string);

        file_put_contents($path, $string);
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

        $this->createCommonFile();
    }

    /**
     * 建立公共文件.
     */
    public function createCommonFile()
    {
        // $filename = \think\Facade\Env::get('app_path').'../cms/tpl/components';
        // $dest = $this->getPath().'/components';
        // copydir($filename, $dest);

        // $source = \think\Facade\Env::get('app_path').'../cms/tpl/index.vue';
        // $dest = $this->getPath().'/index.vue';
        // copy($source, $dest);
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
        $res = \think\Facade\Env::get('app_path').'../cms/src/views/'.$this->input->getOption('vue').'/config.js';

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
        $res = \think\Facade\Env::get('app_path').'../cms/src/views/'.$this->input->getOption('vue');

        return $res;
    }
}
