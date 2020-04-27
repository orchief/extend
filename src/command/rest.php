<?php
namespace command;

/**
 * 一键生成rest接口
 */
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
use think\facade\Db;
use think\facade\Config;

class rest extends Command
{
    private $auth = false;
    /**
     * 后台前端根目录
     *
     * @var string
     */
    private $cmsPath = '';

    protected function configure()
    {
        // 名称
        $this->setName('rest')->setDescription('create restFul API demo：php think rest shop/Members mvc -curd -tshop_members -e会员');     //

        // 参数
        $this->addArgument('name',  Argument::REQUIRED, 'moudle/controller');                                   // 模块名称/控制器名称 
        $this->addArgument('omit',  Argument::OPTIONAL, 'm->model c->controller r->router v->validate', 'mvc');                                   // m->model c->controller r->router v->validate
        
        // 选项
        $this->addOption('Description', 'e', Option::VALUE_REQUIRED, 'explan of this restApi');                 // 控制器 、模型 、验证器的基本注释
        $this->addOption('Author', 'a', Option::VALUE_OPTIONAL, 'author of this restApi', 'orchief');           // 作者
        $this->addOption('plain', 'p', Option::VALUE_OPTIONAL, '是否只包括基础内容', true);                       // 是否只包括基础内容
        $this->addOption('table', 't', Option::VALUE_OPTIONAL, '表名称', null);                                  // 表名称 缺省的情况下 自动按照 模块名_控制器名称(控制器名需要驼峰转换为下划线) 作为表名 
        $this->addOption('force', 'f', Option::VALUE_OPTIONAL, '是否强制覆盖之前的文件(不可撤销！！)', false);      // 是否只包括基础内容
        $this->addOption('vue', 'm', Option::VALUE_OPTIONAL, 'vue管理系统的文件路径 同时对应url路径');          // 是否只包括基础内容

        // 该控制器需要几种对外暴露的方法  使用方法: -curd -cr -c -u -r -d
        $this->addOption('create', 'c', Option::VALUE_OPTIONAL, 'create新建对应post方法', false);               
        $this->addOption('update', 'u', Option::VALUE_OPTIONAL, 'update更新对应put', false);
        $this->addOption('read', 'r', Option::VALUE_OPTIONAL, 'read读一条数据对应get方法', false);
        $this->addOption('delete', 'd', Option::VALUE_OPTIONAL, 'delete删除一条数据对应delete方法', false);
        
        // 使用的数据库驱动
        // $this->addOption('model', 'm', Option::VALUE_OPTIONAL, '使用的数据库驱动', 'Model');
    }

    /**
     * 主体
     *
     * @param Input $input
     * @param Output $output
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $this->Description = trim($input->getOption('Description'));
        $this->Author = trim($input->getOption('Author'));

        // 生成文件
        $this->genarater($input, $output);

        // 返回成功结果
        $output->writeln('<info>'.trim($input->getOption('Description')).' api created successfully.</info>');
    }

    /**
     * 生成对应的文件
     *
     * @return void
     */
    protected function genarater(Input $input, Output $output)
    {
        // 根据参数确定具体生成的模块
        $omit = trim($input->getArgument('omit'));

        $assign = [
            'c' =>  'Controller',
            'm' =>  'Model',
            'v' =>  'Validate',
            'g' =>  'Config',
            's' =>  'Swagger',
        ];

        $configs = [
            'c' =>  [
                'input'   =>  $input,
                'output'  =>  $output,
            ],
            'm' =>  [
                'input'   =>  $input,
                'output'  =>  $output,
            ],
            'v' =>  [
                'input'   =>  $input,
                'output'  =>  $output,
            ],
            'g' =>  [
                'input'   =>  $input,
                'output'  =>  $output,
            ],
            's' =>  [
                'input'   =>  $input,
                'output'  =>  $output,
            ],
        ];

        $len = strlen($omit);
        for($i = 0; $i < $len; $i ++){
            $config = $configs[$omit[$i]];
            $name = $assign[$omit[$i]];
            $application = \command\rest\Factory::make($name, $config);
            $application->create();
        }
    }
}