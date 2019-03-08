<?php
namespace command;

/**
 * linux定时结算
 */
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
use app\shop\middle\Settlement;
use think\Config;

class settle extends Command
{
    protected function configure()
    {
        $this->setName('settlement')->setDescription('按照配置文件自动生成所有rest接口');
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
        Settlement::run();
    }
}