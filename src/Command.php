<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2019/3/25
 * Time: 8:57
 */

namespace liupanv\think\tpadmin;


use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\facade\Env;

class Command extends \think\console\Command
{

    protected function configure()
    {
        $this->setName('tpadmin:install')
            ->setDescription('tpadmin install script');
    }

    protected function execute(Input $input, Output $output)
    {
        //检查数据库连接
        Db::query('show tables');
        $appPath = Env::get('app_path');
        $rootPath = Env::get('root_path');
        //复制admin模块
        $this->copydir(__DIR__.DIRECTORY_SEPARATOR.'admin', $appPath.'admin');
        //复制静态资源
        $this->copydir(__DIR__.DIRECTORY_SEPARATOR.'static', $rootPath.'public'.DIRECTORY_SEPARATOR.'static');
        //复制adminlte
        $this->copydir(Env::get('vendor_path').'almasaeed2010'.DIRECTORY_SEPARATOR.'adminlte', $rootPath.'public'.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'adminlte');
        //复制migrate
        $this->copydir(__DIR__.DIRECTORY_SEPARATOR.'database', $rootPath.'database');
        $output->writeln("install success");
        exit;
        //执行migrate
        chdir(Env::get('root_path'));
        exec('php think migrate:run',$results,$ret);
        foreach($results as $row)
        {
            $output->writeln($row);
        }
        $output->writeln("install success");
    }


    protected function copydir($source, $dest)
    {
        if (!file_exists($dest)) mkdir($dest, 0777, true);
        $handle = opendir($source);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') continue;
            $_source = $source . '/' . $item;
            $_dest = $dest . '/' . $item;
            if (is_file($_source)) copy($_source, $_dest);
            if (is_dir($_source)) $this->copydir($_source, $_dest);
        }
        closedir($handle);
    }
}