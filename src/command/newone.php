<?php
namespace command;

/**
 * 创建/更新一个的项目
 */
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
use think\Config;

class newone extends Command
{
    protected function configure()
    {
        $this->setName('newone')->setDescription('更新项目公共文件+生成项目基础文件+生成数据库基础文件');
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
        Config::load(\think\Facade\Env::get('app_path') . DIRECTORY_SEPARATOR .'.config'.DIRECTORY_SEPARATOR.'app.php');
        Config::load(\think\Facade\Env::get('app_path') . '.'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'database.php', 'database');
        // TODO: 生成 / 更新项目公共文件

        // TODO: 生成数据库基础文件 (条件是表不存在)
    }
}